<?php

namespace App\Services;

use App\Models\User;
use Hash;

class PostgreSQLUserService implements UserServiceInterface
{
    public function createuser(array $data)
    {
        return User::create($data);
    }

    public function find($id)
    {
        return User::find($id);
    }
    public function authenticate($email, $password)
    {
        $user = User::where('email', $email)->first();
     
    
        if ($user && Hash::check($password, $user->password)) {
            return $user; // Retourne l'utilisateur si authentification réussie
        }
    
        return null; // Retourne null si les identifiants sont incorrects
    }
    
    
    

}
