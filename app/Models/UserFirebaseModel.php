<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFirebaseModel extends FirebaseModel
{
    use HasFactory;
    
        protected $table = 'firebase_users'; // Si vous souhaitez stocker des informations Firebase dans une table spécifique
    
        protected $fillable = ['firebase_uid','nom', 'prenom', 'adresse', 'telephone', 'email', 'photo', 'statut', 'role_id', 'password',
    ];
    
        // Vous pouvez ajouter des relations ou des fonctions supplémentaires ici si nécessaire

        protected $auth;

        // Un getter pour accéder à auth
        public function getAuth()
        {
            return $this->auth;
        }
        public function register(array $data)
        {
            return $this->create($data); // Appel de la méthode `create()` du FirebaseModel
        }
    
        public function authenticate($email, $password)
        {
            return $this->authenticate($email, $password); // Appel de la méthode `authenticate()` du FirebaseModel
        }

        public function listUsers(){
            return $this->listUsers(); // Appel de la méthode `listUsers()` du FirebaseModel
        }
        public function filterByRole($roleId){
            return $this->filterByRole($roleId); // Appel de la méthode `filterByRole()` du FirebaseModel
        }

        public function updateUser($firebaseUid, array $data){
            return $this->updateUser($firebaseUid, $data); // Appel de la méthode `updateUser()` du FirebaseModel
        }
}
