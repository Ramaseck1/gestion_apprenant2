<?php

namespace App\Providers;

use App\Models\UserFirebaseModel;
use Illuminate\Support\ServiceProvider;

class FirebaseUserProviver extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('firebaseuser'::class , function($app){
            return new UserFirebaseModel();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
