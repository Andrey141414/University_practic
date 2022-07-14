<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use DateInterval;

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

        // Passport::tokensExpireIn(Carbon::now()->addDays(10));
        // Passport::personalAccessTokensExpireIn( Carbon::now()->addDays(10));
        // Passport::refreshTokensExpireIn(now()->addMinutes(2));
        //Passport::tokensExpireIn(new DateInterval('P1D'));
        //Passport::refreshTokensExpireIn('P1D');
        $personalAccessTokensExpireIn = new DateInterval('P1D');
        
        //Passport::refreshTokensExpireIn(now()->addMinute(2));
    }
}
