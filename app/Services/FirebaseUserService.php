<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Log;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Auth\UserRecord;
use Kreait\Firebase\Auth\SignInResult;
class FirebaseUserService implements UserServiceInterface
{
    protected $database;
    protected $auth;
    protected $storage;

    public function __construct()
    {
        $serviceAccountPath = base_path('config/firebase_credentials.json');

        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath)
            ->withDatabaseUri('https://gestionapprenant-c42e2-default-rtdb.firebaseio.com');

        $this->database = $factory->createDatabase();
        $this->auth = $factory->createAuth();
        $this->storage = $factory->createStorage(); // Ajout de Firebase Storage

    }

    public function checkUnique($field, $value)
    {
        $reference = $this->database->getReference('users');
        $snapshot = $reference->getSnapshot();
        $users = $snapshot->getValue();

        if ($users) {
            foreach ($users as $user) {
                if (isset($user[$field]) && $user[$field] === $value) {
                    return false; // Le champ existe déjà
                }
            }
        }

        return true; // Le champ est unique
    }

    public function register(array $data)
    {
      
        // Vérifier l'unicité de l'email et du téléphone
        if (!$this->checkUnique('email', $data['email'])) {
            throw new \Exception('Email already exists.');
        }
    
        if (!$this->checkUnique('telephone', $data['telephone'])) {
            throw new \Exception('Phone number already exists.');
        }
    
        // Hacher le mot de passe avant de le stocker
        if (isset($data['password'])) {
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            throw new \Exception('Password is required.');
        }
        
        // Créer un utilisateur dans Firebase Authentication
        $firebaseUser = $this->auth->createUser([
            'email' => $data['email'],
            'password' => $data['password'], // Plain password for Firebase Authentication
        ]);
        
        // Obtenir l'UID généré par Firebase
        $firebaseUid = $firebaseUser->uid;
            // Téléverser la photo dans Firebase Storage (si présente)
    if (isset($data['photo'])) {
        $file = $data['photo']; // Le fichier à téléverser
        $filePath = 'users/' . $firebaseUid . '/' . $file->getClientOriginalName();

        // Téléverser le fichier dans Firebase Storage
        $bucket = $this->storage->getBucket();
        $uploadedFile = $bucket->upload(
            fopen($file->getPathname(), 'r'),
            ['name' => $filePath]
        );

        // Obtenir l'URL publique du fichier téléversé
        $photoUrl = $uploadedFile->signedUrl(new \DateTime('9999-12-31'));
    } else {
        $photoUrl = ''; // Aucun fichier téléversé
    }


    
        // Créer un tableau utilisateur avec les données hachées pour Realtime Database
        $userArray = [
            'email' => $data['email'],
            'telephone' => $data['telephone'],
            'password' => $hashedPassword, // Stocker le mot de passe haché dans la Realtime Database
            'photoUrl' => $photoUrl, // URL de la photo téléversée
            'role_id' => $data['role_id'], // URL de la photo téléversée

        ];
    
        Log::info('Création de l\'utilisateur', ['firebase_uid' => $firebaseUid]);

        // Ajouter l'utilisateur dans Firebase Realtime Database
        $reference = $this->database->getReference('users/' . $firebaseUid);
        $reference->set($userArray);
    
        // Ajouter l'utilisateur dans la base de données locale
        $user = new User();
        $user->firebase_uid = $firebaseUid; // Enregistrer l'UID Firebase
        $user->email = $data['email'];
        $user->nom = $data['nom'] ?? 'NomParDefaut';
        $user->prenom = $data['prenom'] ?? 'PrenomParDefaut';
        $user->adresse = $data['adresse'] ?? 'AdresseParDefaut';
        $user->telephone = $data['telephone'];
        $user->password = $hashedPassword; // Mot de passe haché
        $user->role_id = $data['role_id']; // Utiliser le role_id passé dans les données
        $user->photo = $data['photo'] ?? '';
        $user->statut = $data['statut'] ?? 'Inactif';
        $user->save();  
        try {
            $user->save();
            Log::info('Utilisateur enregistré avec succès', ['user' => $user]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'utilisateur', ['error' => $e->getMessage()]);
        }
        
        return $firebaseUid; // Retourne l'identifiant Firebase (UID)
    }

    public function createuser(array $data)
    {

        $this->authorize(['Admin', 'Manager', 'CM'], $data);

      
        // Vérifier l'unicité de l'email et du téléphone
        if (!$this->checkUnique('email', $data['email'])) {
            throw new \Exception('Email already exists.');
        }
    
        if (!$this->checkUnique('telephone', $data['telephone'])) {
            throw new \Exception('Phone number already exists.');
        }
    
        // Hacher le mot de passe avant de le stocker
        if (isset($data['password'])) {
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            throw new \Exception('Password is required.');
        }
        
        // Créer un utilisateur dans Firebase Authentication
        $firebaseUser = $this->auth->createUser([
            'email' => $data['email'],
            'password' => $data['password'], // Plain password for Firebase Authentication
        ]);
        
        // Obtenir l'UID généré par Firebase
        $firebaseUid = $firebaseUser->uid;
            // Téléverser la photo dans Firebase Storage (si présente)
    if (isset($data['photo'])) {
        $file = $data['photo']; // Le fichier à téléverser
        $filePath = 'users/' . $firebaseUid . '/' . $file->getClientOriginalName();

        // Téléverser le fichier dans Firebase Storage
        $bucket = $this->storage->getBucket();
        $uploadedFile = $bucket->upload(
            fopen($file->getPathname(), 'r'),
            ['name' => $filePath]
        );

        // Obtenir l'URL publique du fichier téléversé
        $photoUrl = $uploadedFile->signedUrl(new \DateTime('9999-12-31'));
    } else {
        $photoUrl = ''; // Aucun fichier téléversé
    }


    
        // Créer un tableau utilisateur avec les données hachées pour Realtime Database
        $userArray = [
            'email' => $data['email'],
            'telephone' => $data['telephone'],
            'password' => $hashedPassword, // Stocker le mot de passe haché dans la Realtime Database
            'photoUrl' => $photoUrl, // URL de la photo téléversée
            'role_id' => $data['role_id'], // URL de la photo téléversée

        ];
    
        Log::info('Création de l\'utilisateur', ['firebase_uid' => $firebaseUid]);

        // Ajouter l'utilisateur dans Firebase Realtime Database
        $reference = $this->database->getReference('users/' . $firebaseUid);
        $reference->set($userArray);
    
        // Ajouter l'utilisateur dans la base de données locale
        $user = new User();
        $user->firebase_uid = $firebaseUid; // Enregistrer l'UID Firebase
        $user->email = $data['email'];
        $user->nom = $data['nom'] ?? 'NomParDefaut';
        $user->prenom = $data['prenom'] ?? 'PrenomParDefaut';
        $user->adresse = $data['adresse'] ?? 'AdresseParDefaut';
        $user->telephone = $data['telephone'];
        $user->password = $hashedPassword; // Mot de passe haché
        $user->role_id = $data['role_id']; // Utiliser le role_id passé dans les données
        $user->photo = $data['photo'] ?? '';
        $user->statut = $data['statut'] ?? 'Inactif';
        $user->save();  
        try {
            $user->save();
            Log::info('Utilisateur enregistré avec succès', ['user' => $user]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'utilisateur', ['error' => $e->getMessage()]);
        }
        
        return $firebaseUid; // Retourne l'identifiant Firebase (UID)
    }
    public function getUserByUid($uid)
{
    $reference = $this->database->getReference('users/' . $uid);
    $snapshot = $reference->getSnapshot();
    
    if ($snapshot->exists()) {
        return $snapshot->getValue();
    }

    return null; // Aucun utilisateur trouvé
}


    public function find($id)
    {

        $reference = $this->database->getReference('users/' . $id);
        return $reference->getValue();
    }
    public function authenticate($email, $password)
    {
        try {
            // Authentifier l'utilisateur avec Firebase
            $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);
            
            // Récupérer le token d'ID de Firebase
            $idToken = $signInResult->idToken();


           
    
            return $idToken; // Retourne le token d'ID
        } catch (AuthException $e) {
            throw new \Exception('Authentication failed: ' . $e->getMessage());
        }
    }


    public function verifyToken($token)
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($token);
            return $verifiedIdToken->claims()->get('sub');
        } catch (AuthException $e) {
            throw new \Exception('Token non valide : ' . $e->getMessage());
        } catch (FirebaseException $e) {
            throw new \Exception('Erreur Firebase : ' . $e->getMessage());
        }
    }

    public function listUsers()
    {
        $this->authorize(['Admin', 'Manager', 'CM']);

        $reference = $this->database->getReference('users');
        $snapshot = $reference->getSnapshot();
        return $snapshot->getValue();
    }
    

    public function filterByRole($roleId)
{

    $this->authorize(['Admin', 'Manager', 'CM']);

    $reference = $this->database->getReference('users');
    $snapshot = $reference->getSnapshot();
    $users = $snapshot->getValue();
    $filteredUsers = [];

    if ($users) {
        foreach ($users as $user) {
            if (isset($user['role_id']) && $user['role_id'] === $roleId) {
                $filteredUsers[] = $user;
            }
        }
    }

    return $filteredUsers;
}
 //modifier utilisateur
public function updateUser($firebaseUid, array $data)
{
    // Vérifie si l'utilisateur existe
    $this->authorize(['CM', 'Manager',"Admin"]);

    $reference = $this->database->getReference('users/' . $firebaseUid);
    $snapshot = $reference->getSnapshot();
    
    if (!$snapshot->exists()) {
        throw new \Exception('User not found.');
    }

    // Met à jour les données de l'utilisateur
    $reference->update($data);
}


protected function authorize($roleRequired , $data = [])
{
    $user = request()->attributes->get('user'); // Récupérer l'utilisateur de la requête

    Log::info('Authorization check:', ['user' => $user]);
    Log::info('User role check:', ['role_id' => $user->role_id, 'required_roles' => $roleRequired]);

    if (!$user || !$user->role_id) {
        throw new \Exception('Unauthorized action.');
    }

    $role = Role::find($user->role_id);
    if (!$role || !in_array($role->name, (array)$roleRequired)) {
        throw new \Exception('Unauthorized action.');
    }

    // Vérification des règles selon le rôle
    if ($role->name === 'Admin') {
        // Logique spécifique aux admins
    } elseif ($role->name === 'Manager') {
        // Logique spécifique aux managers
    } elseif ($role->name === 'CM') {
        // Logique spécifique aux CM
    }
}









     // Connexion via login et mot de passe (existant dans votre code)
   




    // Connexion avec un token OAuth de Google, Facebook, ou GitHub// Valider le token Firebase côté serveur
 




      // Exemple pour Google, mais vous pouvez faire de même pour Facebook et GitHub
}
