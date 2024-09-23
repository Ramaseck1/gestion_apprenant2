<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\FirebaseUserService;
use Closure;
use App\Services\FirebaseAuthService;
use Illuminate\Http\Request;
use Log;

class FirebaseAuthMiddleware
{
    protected $firebaseAuthService;

    public function __construct(FirebaseUserService $firebaseAuthService)
    {
        $this->firebaseAuthService = $firebaseAuthService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Récupère le token de l'en-tête Authorization
        $token = $request->bearerToken();

        if (!$token) {
            Log::warning('Token is missing');

            return response()->json(['message' => 'Token is required'], 401);
        }

        try {
            // Vérifie le token via le service FirebaseAuthService
            $uid = $this->firebaseAuthService->verifyToken($token);
            Log::info('Authenticated UID:', ['uid' => $uid]);


            if ($uid) {
                $user = User::where('firebase_uid', $uid)->first();
                if ($user) {
                    // Associez l'utilisateur à la requête
                    $request->attributes->set('user', $user);
                    // Vous pouvez également stocker l'utilisateur dans le système d'authentification de Laravel
                    auth()->login($user);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Token verification error:', ['error' => $e->getMessage()]);

            return response()->json(['message' => $e->getMessage()], 401);
        }
        return $next($request); // Assurez-vous de retourner le résultat de la prochaine étape

    }
}
