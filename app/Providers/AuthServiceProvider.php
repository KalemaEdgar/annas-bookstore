<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // To be able to issue or revoke access tokens, clients, and personal access tokens, we need to add the Passport::routes method in the boot method of the  AuthServiceProvider
        Passport::routes();

        // Enable Implicit grant code authorization
        // Passport::enableImplicitGrant();

        // Adding Gates for authorization.
        // We can use in all the controller methods where we only want the admin to have access
        // We give it a closure to use whenever the admin-only gate needs evaluation (user should have a role of admin to pass)
        Gate::define('admin-only', function ($user) {
            return $user->role === 'admin';
        });
    }
}
