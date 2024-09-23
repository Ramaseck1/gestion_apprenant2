<?php
namespace App\Services;
use Kreait\Firebase\Database;
use Illuminate\Validation\ValidationException;

class FieldValidationService
{
    protected static $database;

    public static function setDatabase(Database $database)
    {
        self::$database = $database;
    }

    public static function validate(array $data)
    {
        $errors = [];

        // Validation des champs requis
        if (empty($data['nom']) || !is_string($data['nom']) || strlen($data['nom']) > 255) {
            $errors['nom'] = 'Le champ nom est requis et doit être une chaîne de caractères de moins de 255 caractères.';
        }

        if (empty($data['prenom']) || !is_string($data['prenom']) || strlen($data['prenom']) > 255) {
            $errors['prenom'] = 'Le champ prénom est requis et doit être une chaîne de caractères de moins de 255 caractères.';
        }

        if (empty($data['telephone']) || !is_string($data['telephone']) || strlen($data['telephone']) > 255) {
            $errors['telephone'] = 'Le champ téléphone est requis et doit être une chaîne de caractères de moins de 255 caractères.';
        } else {
            // Vérifier l'unicité du téléphone
            if (self::isFieldUnique('telephone', $data['telephone'])) {
                $errors['telephone'] = 'Le numéro de téléphone est déjà utilisé.';
            }
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 255) {
            $errors['email'] = 'Le champ email est requis, doit être une adresse email valide et de moins de 255 caractères.';
        } else {
            // Vérifier l'unicité de l'email
            if (self::isFieldUnique('email', $data['email'])) {
                $errors['email'] = 'L\'adresse email est déjà utilisée.';
            }
        }

/*         if (!empty($errors)) {
            throw new ValidationException($errors);
        } */
    }

    protected static function isFieldUnique($field, $value)
    {
        if (!self::$database) {
            throw new \RuntimeException('Database not set for FieldValidationService.');
        }

        // Remplacez 'users' par le chemin approprié dans Firebase
        $reference = self::$database->getReference('users');
        $snapshot = $reference->orderByChild($field)->equalTo($value)->getSnapshot();

        return $snapshot->numChildren() > 0;
    }
}
