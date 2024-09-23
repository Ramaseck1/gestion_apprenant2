<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Repository\FirebaseUserRepository;
use App\Services\Auth\AuthServiceFilebase;
use App\Services\Auth\AuthServiceInterface;
use App\Services\Auth\AuthServicePassport;
use App\Services\FirebaseUserService;
use App\Services\FirebaseUserServices;
use App\Services\PostgreSQLUserService;
use App\Services\RegisterAdminServiceInterface;
use App\Services\UserServiceInterface;
use Auth;
use Gate;
use Hash;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade as PDF;

class AuthController extends Controller
{

    // App\Policies\UserPolicy.php

    
    protected $registerService;
    protected $firebase;
    protected $userService;
/*     protected $authServicePassport;
 */


    public function __construct( RegisterAdminServiceInterface $registerService,PostgreSQLUserService $userService,FirebaseUserService $firebase)
    {
     
        $this->registerService = $registerService;
        $this->firebase = $firebase; 
        $this->userService = $userService;
/*         $this->authServicePassport = $authServicePassport;
 */
    }

 //export excel 

public function exportUsers()
{
    
    Excel::store(new UsersExport, 'users.xlsx','public');
    return Excel::download(new UsersExport, 'users.xlsx');
}


//export pdf



/* public function exportUsersPDF()
{
    $users = User::all();
    $pdf = PDF::loadView('users.pdf', ['users' => $users]);

    return $pdf->download('users.pdf');
} */
  public function register(StoreUserRequest $request)
{
    $data = $request->validated();
    
    // Initialiser la variable pour éviter l'erreur
    $firebaseUid = null;
    
    if ($request->hasFile('photo')) {
        $firebaseUid = $this->firebase->create($data);
    }
    
    // Vérifier si $firebaseUid a bien été défini
    if ($firebaseUid) {
        return response()->json(['status' => 'Success', 'data' => $firebaseUid]);
    } else {
        return response()->json(['status' => 'Error', 'message' => 'Failed to create Firebase user']);
    }
}

    
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        try {
            $token = $this->firebase->authenticate($credentials['email'], $credentials['password']);
            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }



    public function verify(Request $request)
    {
        // Récupérer le token depuis l'en-tête Authorization
        $token = $request->bearerToken();
       

        if (!$token) {
            return response()->json(['message' => 'Token is required'], 401);
        }

        try {
            // Vérification du token via le service FirebaseAuthService
            $uid = $this->firebase->verifyToken($token);

            return response()->json(['message' => 'Authenticated', 'uid' => $uid], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
    
        // Récupérer le token depuis l'en-tête Authorization (Bearer token)
        $token = $request->bearerToken();
    
        try {
            // Vérifier et obtenir l'UID à partir du token Firebase
            $verifiedToken = $this->firebase->verifyToken($token);
            $uid = $verifiedToken;
    
            // Obtenir l'utilisateur depuis Firebase
            $currentUser = $this->firebase->getUserByUid($uid);
            $currentUser = $this->firebase->getUserByUid($uid);
if (!isset($currentUser['role_id'])) {
    return response()->json(['status' => 'error', 'message' => 'Role ID not found.'], 403);
}

            
            // Autorisation
            if (Gate::allows('create', [$currentUser, $data['role_id']])) {
                // L'utilisateur peut créer
                  $userId = $this->firebase->create($data);
            } else {
                // L'utilisateur ne peut pas créer
            }
            
            // Créer l'utilisateur dans Firebase
          
    
            return response()->json([
                'status' => 'success',
                'data' => $userId,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    

public function canCreateUser($userRole, $newUserRole)
{
    // Définition des permissions pour chaque rôle
    $rolePermissions = [
        'ADMIN' => ['ADMIN', 'COATCH', 'Manager', 'APPRENANT', 'CM'],
        'MANAGER' => ['COATCH', 'Manager', 'APPRENANT'],
        'CM' => ['APPRENANT'],
    ];

    // Vérification si le rôle que l'utilisateur essaie de créer est autorisé
    return in_array($newUserRole, $rolePermissions[$userRole] ?? []);
}

 
public function index(){
    $users = $this->firebase->listUsers();
    return response()->json(['status' =>'success', 'data' => $users]);
}

public function update(Request $request, $firebaseUid){
    $data = $request->all();
    $this->firebase->updateUser($firebaseUid, $data);
    return response()->json(['status' => 'Success', 'data' => $firebaseUid]);

}


public function filterByRole($roleId){
    $users = $this->firebase->filterByRole($roleId);
    return response()->json(['status' =>'success', 'data' => $users]);
}




    

}
