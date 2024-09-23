<?php

namespace App\Models;

use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Factory;

abstract class FirebaseModel 
{
    protected $database;
    protected $auth;

    public function __construct()
    {
        $serviceAccountPath = base_path('config/firebase_credentials.json');

        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath)
            ->withDatabaseUri('https://gestionapprenant-c42e2-default-rtdb.firebaseio.com');

        $this->database = $factory->createDatabase();
        $this->auth = $factory->createAuth();
    }

    public function checkUnique($field, $value)
    {
        $reference = $this->database->getReference('users');
        $snapshot = $reference->getSnapshot();
        $users = $snapshot->getValue();

        if ($users) {
            foreach ($users as $user) {
                if (isset($user[$field]) && $user[$field] === $value) {
                    return false; // Le champ existe déjà
                }
            }
        }

        return true; // Le champ est unique
    }

    // Méthode abstraite pour la création d'un utilisateur
    public function create(array $data)
        {
            // Vérifier l'unicité de l'email et du téléphone
            if (!$this->checkUnique('email', $data['email'])) {
                throw new \Exception('Email already exists.');
            }
        
            if (!$this->checkUnique('telephone', $data['telephone'])) {
                throw new \Exception('Phone number already exists.');
            }
        
            // Hacher le mot de passe avant de le stocker
            if (isset($data['password'])) {
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                throw new \Exception('Password is required.');
            }
        
            // Créer un utilisateur dans Firebase Authentication
            $firebaseUser = $this->auth->createUser([
                'email' => $data['email'],
                'password' => $data['password'], // Plain password for Firebase Authentication
            ]);
        
            // Obtenir l'UID généré par Firebase
            $firebaseUid = $firebaseUser->uid;
        
            // Créer un tableau utilisateur avec les données hachées pour Realtime Database
          
        
            // Ajouter l'utilisateur dans Firebase Realtime Database
            $reference = $this->database->getReference('users/' . $firebaseUid);
            $reference->set($data);
        
            // Ajouter l'utilisateur dans la base de données locale
             $user = new User();
                $user->firebase_uid = $firebaseUid; // Enregistrer l'UID Firebase dans la base de données locale
            $user->email = $data['email'];
            $user->nom = $data['nom'] ?? 'NomParDefaut';
            $user->prenom = $data['prenom'] ?? 'PrenomParDefaut';
            $user->adresse = $data['adresse'] ?? 'AdresseParDefaut';
            $user->telephone = $data['telephone'];
            $user->password = $hashedPassword; // Mot de passe haché
            $user->role_id = $data['role_id']; // Assurez-vous que role_id est défini
            $user->photo = $data['photo'] ?? '';
            $user->statut = $data['statut'] ?? 'Inactif';
            $user->save(); 
        
            return $firebaseUid; // Retourne l'identifiant Firebase (UID)
        }

        public function authenticate($email, $password)
    {
        try {
            // Authentifier l'utilisateur avec Firebase
            $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);
            
            // Récupérer le token d'ID de Firebase
            $idToken = $signInResult->idToken();

            dd($idToken);
    
            return $idToken; // Retourne le token d'ID
        } catch (AuthException $e) {
            throw new \Exception('Authentication failed: ' . $e->getMessage());
        }
    }
    public function listUsers()
    {
        $reference = $this->database->getReference('users');
        $snapshot = $reference->getSnapshot();
        return $snapshot->getValue();
    }
    

    public function filterByRole($roleId)
{
    $reference = $this->database->getReference('users');
    $snapshot = $reference->getSnapshot();
    $users = $snapshot->getValue();
    $filteredUsers = [];

    if ($users) {
        foreach ($users as $user) {
            if (isset($user['role_id']) && $user['role_id'] === $roleId) {
                $filteredUsers[] = $user;
            }
        }
    }

    return $filteredUsers;
}
 //modifier utilisateur
public function updateUser($firebaseUid, array $data)
{
    // Vérifie si l'utilisateur existe
    $reference = $this->database->getReference('users/' . $firebaseUid);
    $snapshot = $reference->getSnapshot();
    
    if (!$snapshot->exists()) {
        throw new \Exception('User not found.');
    }

    // Met à jour les données de l'utilisateur
    $reference->update($data);
}


}
