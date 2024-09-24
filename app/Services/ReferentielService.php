<?php

namespace App\Services;

use App\Models\Role;
use Kreait\Firebase\Factory;
use Log;

class ReferentielService
{
    protected $database;

    public function __construct()
    {
        $serviceAccountPath = base_path('config/firebase_credentials.json');

        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath)
            ->withDatabaseUri('https://gestionapprenant-c42e2-default-rtdb.firebaseio.com');

        $this->database = $factory->createDatabase();
    }

    public function createReferentiel(array $data)
    {
        $this->authorize(['Admin', 'Manager']);

        // Validation des données (à faire selon vos règles)
        $this->validateReferentielData($data);

        // Création du référentiel
        $referentielRef = $this->database->getReference('referentiels')->push();
        $referentielKey = $referentielRef->getKey();
        
        // Structure des données à enregistrer
        $referentielData = [
            'code' => $data['code'],
            'libelle' => $data['libelle'],
            'description' => $data['description'],
            'photo' => $data['photo'] ?? '',
            'statut' => 'Actif', // Par défaut, le statut est actif
            'competences' => []
        ];

        // Ajout des compétences et modules
        if (isset($data['competences'])) {
            foreach ($data['competences'] as $competence) {
                $competenceData = [
                    'nom' => $competence['nom'],
                    'description' => $competence['description'],
                    'duree_acquisition' => $competence['duree_acquisition'],
                    'modules' => []
                ];

                if (isset($competence['modules'])) {
                    foreach ($competence['modules'] as $module) {
                        $competenceData['modules'][] = [
                            'nom' => $module['nom'],
                            'description' => $module['description'],
                            'duree_acquisition' => $module['duree_acquisition'],
                        ];
                    }
                }

                $referentielData['competences'][$competenceData['nom']] = $competenceData;
            }
        }

        // Enregistrement du référentiel dans la base de données
        $referentielRef->set($referentielData);

        return $referentielKey; // Retourne la clé du référentiel créé
    }

    private function validateReferentielData(array $data)
{
    $this->authorize(['Admin', 'Manager']);

    // Vérifiez que le code et le libellé ne sont pas vides
    if (empty($data['code']) || empty($data['libelle'])) {
        throw new \Exception('Code et Libellé sont obligatoires.');
    }

    // Vérification de l'unicité du code
    if (!$this->isUnique('code', $data['code'])) {
        throw new \Exception('Le code doit être unique.');
    }

    // Vérification de l'unicité du libellé
    if (!$this->isUnique('libelle', $data['libelle'])) {
        throw new \Exception('Le libellé doit être unique.');
    }

    // Autres validations peuvent être ajoutées ici
}

    private function isUnique($field, $value)
{
    $referentielsRef = $this->database->getReference('referentiels');
    $referentiels = $referentielsRef->getValue();

    foreach ($referentiels as $referentiel) {
        if ($referentiel[$field] === $value) {
            return false; // Le champ n'est pas unique
        }
    }

    return true; // Le champ est unique
}

public function getReferentiels($statut = null)
{
    $this->authorize(['Admin', 'Manager']);

    $reference = $this->database->getReference('referentiels');
    $snapshot = $reference->getSnapshot();
    $referentiels = $snapshot->getValue();

    // Si un statut est fourni, filtrer les référentiels par statut
    if ($statut) {
        $referentiels = array_filter($referentiels, function($referentiel) use ($statut) {
            return $referentiel['statut'] === $statut;
        });
    }

    return $referentiels;
}

    public function getReferentielById($id)
    {
        $this->authorize(['Admin', 'Manager']);

        $reference = $this->database->getReference('referentiels/' . $id);
        $snapshot = $reference->getSnapshot();
        
        if (!$snapshot->exists()) {
            throw new \Exception('Referentiel not found.');
        }

        return $snapshot->getValue();
    }

    public function updateReferentiel($id, array $data)
    {
        $this->authorize(['Admin', 'Manager']);

        $reference = $this->database->getReference('referentiels/' . $id);
        $snapshot = $reference->getSnapshot();
        
        if (!$snapshot->exists()) {
            throw new \Exception('Referentiel not found.');
        }

        $reference->update($data);
    }

    public function deleteReferentiel($id)
    {
        $this->authorize(['Admin', 'Manager']);

        $reference = $this->database->getReference('referentiels/' . $id);
        $snapshot = $reference->getSnapshot();
        
        if (!$snapshot->exists()) {
            throw new \Exception('Referentiel not found.');
        }

        $reference->remove(); // Supprime le référentiel
    }

    public function listArchivedReferentiels()
    {
        $this->authorize(['Admin', 'Manager' ]);

        // Implémentation pour lister les référentiels archivés
        $reference = $this->database->getReference('archived_referentiels');
        $snapshot = $reference->getSnapshot();
        return $snapshot->getValue();
    }


protected function authorize($roleRequired , $data = [])
{
    $user = request()->attributes->get('user'); // Récupérer l'utilisateur de la requête

    Log::info('Authorization check:', ['user' => $user]);
    Log::info('User role check:', ['role_id' => $user->role_id, 'required_roles' => $roleRequired]);

    if (!$user || !$user->role_id) {
        throw new \Exception('Unauthorized action.');
    }

    $role = Role::find($user->role_id);
    if (!$role || !in_array($role->name, (array)$roleRequired)) {
        throw new \Exception('Unauthorized action.');
    }

    // Vérification des règles selon le rôle
    if ($role->name === 'Admin') {
        // Logique spécifique aux admins
    } elseif ($role->name === 'Manager') {
        // Logique spécifique aux managers
    } elseif ($role->name === 'CM') {
        // Logique spécifique aux CM
    }
}

}
