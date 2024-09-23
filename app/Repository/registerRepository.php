<?php
namespace App\Repository;

use App\Models\User;
use App\Repository\registerRepositoryInterface;

class registerRepository implements registerRepositoryInterface
{
    public function create(array $data)
    {
        // Création d'un nouvel utilisateur
        return User::create($data);
    }
}
