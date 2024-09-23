<?php

namespace App\Providers;

use App\Models\ConcreteFirebaseModel;
use App\Models\FirebaseModel;
use App\Models\FirebaseModelInterface;
use App\Repository\registerRepository;
use App\Services\Auth\AuthServicePassport;
use App\Services\FieldValidationService;
use App\Services\FirebaseUserService;
use App\Services\PostgreSQLUserService;
use App\Services\registerAdminService;
use App\Services\RegisterAdminServiceInterface;
use App\Services\UserServiceInterface;
use Illuminate\Support\ServiceProvider;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Repository\registerRepositoryInterface::class, registerRepository::class);
        $this->app->bind(RegisterAdminServiceInterface::class, concrete: registerAdminService::class);
        $this->app->bind(FirebaseUserService::class, function ($app) {
            return new FirebaseUserService();
        });
      

        $this->app->bind(UserServiceInterface::class, function ($app) {
            // Vérifie si Firebase est activé par défaut et sinon vérifie pour PostgreSQL
            if (config('firebase.enable', false)) { // Firebase est activé par défaut
                return new FirebaseUserService();
           } elseif (config('database.default') === 'pgsql') {
                return new PostgreSQLUserService();
            }

            throw new \Exception('No valid user service found');
        });




    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
