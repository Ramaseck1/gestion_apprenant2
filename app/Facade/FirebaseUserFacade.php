<?php

namespace App\Facade;

use Illuminate\Support\Facades\Facade;

class FirebaseUserFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'firebaseuser';
    }
}
