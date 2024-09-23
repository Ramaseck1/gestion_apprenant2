<?php

namespace App\Repository;

use App\Models\FirebaseModelInterface;
use App\Models\FirebaseModel;
use App\Models\User;
use App\Models\UserFirebaseModel; // Importation du modèle Firebase
use App\Facade\FirebaseUserFacade;

class FirebaseUserRepository 
{
    

    public function register(array $data)
    {
       FirebaseUserFacade::register($data);


    }

    public function authenticate($email, $password){
        return FirebaseUserFacade::authenticate($email, $password);
    }

    public function listUsers(){
        return FirebaseUserFacade::listUsers();
    }

    public function filterByRole($roleId){
        return FirebaseUserFacade::filterByRole($roleId);
    }

    public function updateUser($firebaseUid, array $data){
        FirebaseUserFacade::updateUser($firebaseUid, $data);
    }
}
