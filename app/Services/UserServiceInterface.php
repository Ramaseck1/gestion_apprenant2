<?php

namespace App\Services;

interface UserServiceInterface
{
    public function createuser(array $data);
    public function find($id);
    public function authenticate($email, $password);
    // Ajoutez d'autres méthodes nécessaires
}
