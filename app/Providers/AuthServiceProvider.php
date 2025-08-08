<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Definir gates basados en roles
        Gate::define('manageSuppliers', [App\Policies\UserPolicy::class, 'manageSuppliers']);
        Gate::define('manageCustomers', [App\Policies\UserPolicy::class, 'manageCustomers']);
        Gate::define('manageLots', [App\Policies\UserPolicy::class, 'manageLots']);
        Gate::define('manageSales', [App\Policies\UserPolicy::class, 'manageSales']);
        Gate::define('managePayments', [App\Policies\UserPolicy::class, 'managePayments']);
        Gate::define('manageUsers', [App\Policies\UserPolicy::class, 'manageUsers']);
    }
}
