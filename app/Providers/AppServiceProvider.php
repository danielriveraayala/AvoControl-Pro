<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register custom Blade directives for RBAC
        $this->registerRbacDirectives();
    }

    /**
     * Register RBAC Blade directives.
     */
    private function registerRbacDirectives()
    {
        // @canRole('admin,manager')
        Blade::directive('canRole', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasAnyRole(explode(',', {$expression}))): ?>";
        });

        // @canPermission('users.create,users.edit')
        Blade::directive('canPermission', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasAnyPermission(explode(',', {$expression}))): ?>";
        });

        // @canManage(User $user) or @canManage($role)
        Blade::directive('canManage', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->canManageUser({$expression})): ?>";
        });

        // @canManageRole($role)
        Blade::directive('canManageRole', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->canManageRole({$expression})): ?>";
        });

        // @canAssignRole($role)
        Blade::directive('canAssignRole', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->canAssignRole({$expression})): ?>";
        });

        // @isSuperAdmin
        Blade::directive('isSuperAdmin', function () {
            return "<?php if(auth()->check() && auth()->user()->isSuperAdmin()): ?>";
        });

        // @hasHierarchy(50)
        Blade::directive('hasHierarchy', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->getHighestHierarchyLevel() >= {$expression}): ?>";
        });

        // Closing directives
        Blade::directive('endcanRole', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('endcanPermission', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('endcanManage', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('endcanManageRole', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('endcanAssignRole', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('endisSuperAdmin', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('endhasHierarchy', function () {
            return '<?php endif; ?>';
        });
    }
}
