<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user)
    {
        return $user->role_id === 1; // Seul l'Admin peut voir tous les utilisateurs
    }

    public function view(User $user, User $model)
    {
        return $user->id === $model->id || $user->role_id === 1; // Seul l'Admin ou l'utilisateur lui-même peut voir son propre profil
    }

    public function create(User $user, $newUserRoleId) {
        
        if ($user->role_id === 1) {
            return in_array($newUserRoleId, [1, 2, 3, 4,5]); // Admin, Coach, Manager, CM
        }
    
        if ($user->role_id === 2) { // Manager
            return in_array($newUserRoleId, [ 3, 4]);
        }
    
        // CM peut inscrire des apprenants
        if ($user->role_id === 3) { // CM
            return true; // ou une logique spécifique
        }
    
        return false; // Pour tous les autres rôles
    }
    

    public function enroll(User $user)
    {
        // Règles pour inscrire un nouvel apprenant ou une liste d'apprenants
        if (in_array($user->role_id, [1, 2, 3])) {
            return true; // Admin, Manager ou CM peuvent inscrire des apprenants
        }

        return false; // Les autres rôles ne peuvent pas inscrire
    }

    public function update(User $user, User $model)
    {
        // Seul l'utilisateur lui-même ou un Admin peut modifier un profil
        return $user->id === $model->id || $user->role_id === 1;
    }

    public function delete(User $user, User $model)
    {
        // Seul un Admin peut supprimer un utilisateur
        return $user->role_id === 1;
    }
}
