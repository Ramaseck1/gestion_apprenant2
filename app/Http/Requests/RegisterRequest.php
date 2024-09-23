<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;  // Autoriser la requête
    }

    public function rules()
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'login' => [
                'sometimes',
                'required',
                'string',
                'email:rfc,dns', // Vérifie que c'est une adresse email valide
                'max:255',
                function ($attribute, $value, $fail) {
                    // Vérifie que l'email se termine par @gmail.com
                    if (!preg_match('/@gmail\.com$/', $value)) {
                        $fail('The ' . $attribute . ' must end with @gmail.com.');
                    }
                }
            ],  'password' => ['required', 'string', 'min:5',
                'regex:/[a-z]/',  // au moins une lettre minuscule
                'regex:/[A-Z]/',  // au moins une lettre majuscule
                'regex:/[0-9]/',  // au moins un chiffre
                'regex:/[@$!%*#?&]/'  // au moins un caractère spécial
            ],
            'role_id' => ['required', 'integer'],
            'photo' => 'nullable|image|max:4096',  // Limite à 4 MB
        ];
    }

    public function messages()
    {
        return [
            'nom.required' => 'Le nom est requis.',
            'prenom.required' => 'Le prénom est requis.',
            'login.unique' => 'Ce login est déjà utilisé.',
            'password.regex' => 'Le mot de passe doit comporter des majuscules, des minuscules, des chiffres, et des caractères spéciaux.',
        ];
    }
// Gérer les erreurs de validation
        protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
        {
            throw new HttpResponseException(response()->json([
                'status' => 422,
                'message' => 'Échec de validation',
                'errors' => $validator->errors(),
            ], 422));
        }
}