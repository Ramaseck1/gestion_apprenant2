<?php
namespace App\Services;

use App\Jobs\SendGradeReports;
use App\Models\Role;
use Kreait\Firebase\Factory;
use Log;

class PromotionService
{
    protected $database;

    public function __construct()
    {
        $serviceAccountPath = base_path('config/firebase_credentials.json');

        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath)
            ->withDatabaseUri('https://gestionapprenant-c42e2-default-rtdb.firebaseio.com');

        $this->database = $factory->createDatabase();
        $this->auth = $factory->createAuth();
/*         $this->storage = $factory->createStorage(); // Ajout de Firebase Storage
 */
    }

    public function createPromotion(array $data)
    {
        // Validation des données
        $this->validatePromotionData($data);

        // Création de la promotion
        $promotionRef = $this->database->getReference('promotions')->push();
        $promotionKey = $promotionRef->getKey();
        $promotionData = [
            'libelle' => $data['libelle'],
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'] ?? null,
            'duree' => $data['duree'] ?? null,
            'etat' => 'Inactif',
            'referentiels' => $this->formatReferentiels($data['referentiels'] ?? []),
            'apprenants' => $this->formatApprenants($data['apprenants'] ?? []),
            'photo' => $data['photo'] ?? '',
        ];


        $promotionRef->set($promotionData);
     
        return $promotionKey;
    }

    private function formatReferentiels(array $referentiels)
    {
        return array_map(function($referentiel) {
            return [
                'infos' => $referentiel['infos'] ?? '',
                'apprenants' => $this->formatApprenants($referentiel['apprenants'] ?? []),
            ];
        }, $referentiels);
    }

    private function formatApprenants(array $apprenants)
    {
        return array_map(function($apprenant) {
            return [
                'nom' => $apprenant['nom'] ?? '',
                'prenom' => $apprenant['prenom'] ?? '',
                'adresse' => $apprenant['adresse'] ?? '',
                'email' => $apprenant['email'] ?? '',
                'password' => $apprenant['password'] ?? '',
                'telephone' => $apprenant['telephone'] ?? '',
                'role' => $apprenant['role'] ?? '',
                'statut' => $apprenant['statut'] ?? '',
                'photo' => $apprenant['photo'] ?? '',
            ];
        }, $apprenants);
    }

    private function validatePromotionData(array $data)
    {
        // Implémentez vos règles de validation ici
        if (empty($data['libelle'])) {
            throw new \Exception('Libelle is required.');
        }

        if (empty($data['date_debut'])) {
            throw new \Exception('Date de début is required.');
        }
        
        // Autres validations peuvent être ajoutées ici
    }

    public function updatePromotion($id, array $data)
    {
        $reference = $this->database->getReference('promotions/' . $id);
        $snapshot = $reference->getSnapshot();

        if (!$snapshot->exists()) {
            throw new \Exception('Promotion not found.');
        }

        $reference->update($data);
    }

    public function listPromotions()
    {
        $reference = $this->database->getReference('promotions');
        $snapshot = $reference->getSnapshot();
        return $snapshot->getValue();
    }

    public function getPromotionById($id)
    {
        $reference = $this->database->getReference('promotions/' . $id);
        $snapshot = $reference->getSnapshot();

        if (!$snapshot->exists()) {
            throw new \Exception('Promotion not found.');
        }

        return $snapshot->getValue();
    }

    public function updateReferentiel($promotionId, $referentielId, $action)
    {
        $this->authorize(['CM', 'Manager']); // Vérifiez si l'utilisateur a le bon rôle

        $reference = $this->database->getReference('promotions/' . $promotionId . '/referentiels/' . $referentielId);
        $snapshot = $reference->getSnapshot();

        if ($action === 'remove') {
            if (!$snapshot->exists() || empty($snapshot->getValue()['apprenants'])) {
                $reference->remove(); // Soft delete
            } else {
                throw new \Exception('Referentiel is not empty and cannot be removed.');
            }
        } elseif ($action === 'add') {
            // Logique pour ajouter un référentiel si besoin
        }
    }

    public function changePromotionEtat($id)
    {
        $reference = $this->database->getReference('promotions/' . $id);
        $snapshot = $reference->getSnapshot();

        if (!$snapshot->exists()) {
            throw new \Exception('Promotion not found.');
        }

        // Logique pour changer l'état de la promotion
        // Vérifiez qu'il n'y a qu'une seule promotion en cours
    }

    public function getPromotionEncours()
    {
        $reference = $this->database->getReference('promotions');
        $snapshot = $reference->getSnapshot();
        $promotions = $snapshot->getValue();

        foreach ($promotions as $promotion) {
            if ($promotion['etat'] === 'En cours') {
                return $promotion;
            }
        }

        throw new \Exception('No ongoing promotion found.');
    }

    public function cloturerPromotion($id)
    {
        $reference = $this->database->getReference('promotions/' . $id);
        $snapshot = $reference->getSnapshot();
    
        if (!$snapshot->exists() || strtotime($snapshot->getValue()['date_fin']) > time()) {
            throw new \Exception('Promotion cannot be closed yet.');
        }
    
        $reference->update(['etat' => 'Clôturé']);
    
        // Envoi des relevés de notes après clôture
        $promotionData = $snapshot->getValue(); // Récupérer les données de la promotion
        $promotion = new Promotion($promotionData); // Créer une instance de promotion
        SendGradeReports::dispatch($promotion);
    }
    

    public function listReferentiels($id)
    {
        $reference = $this->database->getReference('promotions/' . $id . '/referentiels');
        $snapshot = $reference->getSnapshot();

        return $snapshot->getValue();
    }

    public function getPromotionStats($id)
    {
        $reference = $this->database->getReference('promotions/' . $id);
        $snapshot = $reference->getSnapshot();

        if (!$snapshot->exists()) {
            throw new \Exception('Promotion not found.');
        }

        $promotionData = $snapshot->getValue();
        // Logique pour récupérer les stats des apprenants, etc.
        return [
            'promotion' => $promotionData,
            'nombre_apprenants' => count($promotionData['apprenants']),
            // Ajoutez d'autres stats nécessaires ici
        ];
    }
//autorisation



protected function authorize($roleRequired)
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
}


}
