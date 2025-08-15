<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Super Admin Gate - has access to everything
        Gate::define('super-admin', function (User $user) {
            return $user->hasRole('super_admin');
        });

        // Developer Panel Access
        Gate::define('access-developer-panel', function (User $user) {
            return $user->hasRole('super_admin');
        });

        // Administrative Gates
        Gate::define('manage-users', function (User $user) {
            return $user->hasRole(['super_admin', 'admin']);
        });

        Gate::define('manage-roles', function (User $user) {
            return $user->hasRole('super_admin');
        });

        Gate::define('manage-system-config', function (User $user) {
            return $user->hasRole('super_admin');
        });

        // Business Operation Gates
        Gate::define('manage-lots', function (User $user) {
            return $user->hasPermission('manage_lots') || 
                   $user->hasRole(['admin', 'gerente', 'comprador']);
        });

        Gate::define('manage-sales', function (User $user) {
            return $user->hasPermission('manage_sales') || 
                   $user->hasRole(['admin', 'gerente', 'vendedor']);
        });

        Gate::define('manage-payments', function (User $user) {
            return $user->hasPermission('manage_payments') || 
                   $user->hasRole(['admin', 'gerente', 'contador']);
        });

        Gate::define('manage-suppliers', function (User $user) {
            return $user->hasPermission('manage_suppliers') || 
                   $user->hasRole(['admin', 'gerente', 'comprador']);
        });

        Gate::define('manage-customers', function (User $user) {
            return $user->hasPermission('manage_customers') || 
                   $user->hasRole(['admin', 'gerente', 'vendedor']);
        });

        // Financial Gates
        Gate::define('view-financial-reports', function (User $user) {
            return $user->hasPermission('view_financial_reports') || 
                   $user->hasRole(['admin', 'gerente', 'contador']);
        });

        Gate::define('manage-financial-data', function (User $user) {
            return $user->hasPermission('manage_financial_data') || 
                   $user->hasRole(['admin', 'contador']);
        });

        // Reporting Gates
        Gate::define('view-reports', function (User $user) {
            return $user->hasPermission('view_reports') || 
                   !$user->hasRole('operario'); // All except operario can view reports
        });

        Gate::define('export-data', function (User $user) {
            return $user->hasPermission('export_data') || 
                   $user->hasRole(['admin', 'gerente', 'contador']);
        });

        // Configuration Gates
        Gate::define('manage-company-config', function (User $user) {
            return $user->hasPermission('manage_company_config') || 
                   $user->hasRole(['admin', 'gerente']);
        });

        // Notification Gates
        Gate::define('manage-notifications', function (User $user) {
            return $user->hasPermission('manage_notifications') || 
                   $user->hasRole(['super_admin', 'admin']);
        });

        // Advanced permission checking with hierarchy
        Gate::define('can-modify-user', function (User $currentUser, User $targetUser) {
            // Super admin can modify anyone
            if ($currentUser->hasRole('super_admin')) {
                return true;
            }

            // Admin can modify users except super_admin
            if ($currentUser->hasRole('admin')) {
                return !$targetUser->hasRole('super_admin');
            }

            // Gerente can modify lower level users
            if ($currentUser->hasRole('gerente')) {
                return !$targetUser->hasRole(['super_admin', 'admin', 'gerente']);
            }

            return false;
        });

        // Permission-based gates for granular control
        Gate::define('create-lots', function (User $user) {
            return $user->hasPermission('create_lots');
        });

        Gate::define('edit-lots', function (User $user) {
            return $user->hasPermission('edit_lots');
        });

        Gate::define('delete-lots', function (User $user) {
            return $user->hasPermission('delete_lots');
        });

        Gate::define('create-sales', function (User $user) {
            return $user->hasPermission('create_sales');
        });

        Gate::define('edit-sales', function (User $user) {
            return $user->hasPermission('edit_sales');
        });

        Gate::define('delete-sales', function (User $user) {
            return $user->hasPermission('delete_sales');
        });

        Gate::define('create-payments', function (User $user) {
            return $user->hasPermission('create_payments');
        });

        Gate::define('edit-payments', function (User $user) {
            return $user->hasPermission('edit_payments');
        });

        Gate::define('delete-payments', function (User $user) {
            return $user->hasPermission('delete_payments');
        });

        // Backup and system operations
        Gate::define('manage-backups', function (User $user) {
            return $user->hasRole('super_admin');
        });

        Gate::define('manage-maintenance', function (User $user) {
            return $user->hasRole('super_admin');
        });

        Gate::define('view-logs', function (User $user) {
            return $user->hasRole('super_admin');
        });

        // Multi-tenant preparation
        Gate::define('manage-tenants', function (User $user) {
            return $user->hasRole('super_admin');
        });

        Gate::define('manage-subscriptions', function (User $user) {
            return $user->hasRole('super_admin');
        });
    }
}
