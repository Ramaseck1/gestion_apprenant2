<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette demande.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Modifier en fonction des besoins d'autorisation
    }

    /**
     * Obtenez les règles de validation pour la demande.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'required|string|unique:users,telephone',
            'email' => 'required|email|unique:users,email',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role_id' => 'required|exists:roles,id',
            'statut' => 'required|in:Bloquer,Actif',
            'password' => 'required|string|min:8',
        ];
    }
}
