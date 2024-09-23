<?php

namespace App\Services;

interface UserServiceInterface
{
    public function create(array $data);
    public function find($id);
    public function authenticate($email, $password);
    // Ajoutez d'autres méthodes nécessaires
}
