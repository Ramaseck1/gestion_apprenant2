<?php

namespace App\Services;

use App\Repository\FirebaseUserRepository;

class FirebaseUserServices
{
    protected $firebaseUserRepository;

    public function __construct(FirebaseUserRepository $firebaseUserRepository)
    {
        $this->firebaseUserRepository = $firebaseUserRepository;
    }

    public function register(array $data)
    {
       
        return $this->firebaseUserRepository->register($data);
    }

    public function authenticate($email, $password){
        return $this->firebaseUserRepository->authenticate($email, $password);
    }
    public function listUsers(){
        return $this->firebaseUserRepository->listUsers();
    }
    public function filterByRole($roleId){
        return $this->firebaseUserRepository->filterByRole($roleId);
    }
    public function updateUser($firebaseUid, array $data){
        return $this->firebaseUserRepository->updateUser($firebaseUid, $data);
    }
}
