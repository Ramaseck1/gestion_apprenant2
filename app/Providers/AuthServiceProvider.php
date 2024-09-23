<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Services\Auth\AuthServiceInterface;
use App\Services\Auth\AuthServicePassport;
use App\Services\Auth\AuthServiceSanctum;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];


    public function register()
    {
        // Choisissez entre Sanctum et Passport en décommentant la ligne appropriée
/*         $this->app->bind(AuthServiceInterface::class, AuthServiceSanctum::class);
 */       
//  $this->app->bind(AuthServiceInterface::class, AuthServicePassport::class);
    } 

    public function boot()
    {
        $this->registerPolicies();

    }
}
