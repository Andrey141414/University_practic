<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
         'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Passport::routes(function ($router) {
        //     $router->forAccessTokens();
        //     $router->forPersonalAccessTokens();
        //     $router->forTransientTokens();
        // });
        
        //if (! $this->app->routesAreCached()) {
            Passport::routes();
        //}

        //Passport::tokensExpireIn(Carbon::now()->addMinutes(10));
        
        //Passport::refreshTokensExpireIn(Carbon::now()->addDays(10));
    }
}
