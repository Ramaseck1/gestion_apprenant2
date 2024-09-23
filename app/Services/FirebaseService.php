<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirebaseService
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

    public function getDatabase()
    {
        return $this->database;
    }
}
