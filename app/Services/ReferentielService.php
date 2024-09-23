<?php

namespace App\Services;

use Kreait\Firebase\Factory;

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
        // Implémentez ici vos règles de validation (par exemple, vérifier l'unicité du code et du libellé)
        if (empty($data['code']) || empty($data['libelle'])) {
            throw new \Exception('Code et Libellé sont obligatoires.');
        }

        // Autres validations peuvent être ajoutées ici
    }
    public function getReferentiels()
    {
        $reference = $this->database->getReference('referentiels');
        $snapshot = $reference->getSnapshot();
        return $snapshot->getValue();
    }

    public function getReferentielById($id)
    {
        $reference = $this->database->getReference('referentiels/' . $id);
        $snapshot = $reference->getSnapshot();
        
        if (!$snapshot->exists()) {
            throw new \Exception('Referentiel not found.');
        }

        return $snapshot->getValue();
    }

    public function updateReferentiel($id, array $data)
    {
        $reference = $this->database->getReference('referentiels/' . $id);
        $snapshot = $reference->getSnapshot();
        
        if (!$snapshot->exists()) {
            throw new \Exception('Referentiel not found.');
        }

        $reference->update($data);
    }

    public function deleteReferentiel($id)
    {
        $reference = $this->database->getReference('referentiels/' . $id);
        $snapshot = $reference->getSnapshot();
        
        if (!$snapshot->exists()) {
            throw new \Exception('Referentiel not found.');
        }

        $reference->remove(); // Supprime le référentiel
    }

    public function listArchivedReferentiels()
    {
        // Implémentation pour lister les référentiels archivés
        $reference = $this->database->getReference('archived_referentiels');
        $snapshot = $reference->getSnapshot();
        return $snapshot->getValue();
    }
}
