<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use App\Http\Middleware\TenantResolver;
use App\Http\Middleware\TenantContext;
use App\Models\Tenant;

class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register tenant resolver as singleton
        $this->app->singleton('tenant.resolver', function ($app) {
            return new TenantResolver();
        });

        // Register tenant context as singleton
        $this->app->singleton('tenant.context', function ($app) {
            return new TenantContext();
        });

        // Register current tenant singleton
        $this->app->singleton('current_tenant', function ($app) {
            return null; // Will be set by TenantResolver middleware
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Blade directives for tenant functionality
        $this->registerBladeDirectives();

        // Register view composers
        $this->registerViewComposers();

        // Register macros
        $this->registerMacros();
    }

    /**
     * Register custom Blade directives
     */
    protected function registerBladeDirectives(): void
    {
        // @tenant - Check if current tenant exists
        Blade::if('tenant', function () {
            return TenantResolver::hasTenant();
        });

        // @tenantFeature - Check if tenant has specific feature
        Blade::if('tenantFeature', function ($feature) {
            return TenantContext::hasFeature($feature);
        });

        // @tenantPlan - Check if tenant has specific plan
        Blade::if('tenantPlan', function ($plan) {
            $tenant = TenantResolver::getCurrentTenant();
            return $tenant && $tenant->plan === $plan;
        });

        // @tenantSetting - Get tenant setting value
        Blade::directive('tenantSetting', function ($expression) {
            return "<?php echo TenantContext::getSetting($expression); ?>";
        });

        // @tenantName - Display tenant name
        Blade::directive('tenantName', function () {
            return "<?php echo TenantResolver::getCurrentTenant()?->name; ?>";
        });

        // @tenantLogo - Display tenant logo
        Blade::directive('tenantLogo', function ($attributes = '""') {
            return "<?php 
                \$tenant = TenantResolver::getCurrentTenant();
                if (\$tenant && \$tenant->logo_path) {
                    echo '<img src=\"' . asset(\$tenant->logo_path) . '\" ' . $attributes . '>';
                }
            ?>";
        });

        // @withinUsageLimit - Check if tenant is within usage limits
        Blade::if('withinUsageLimit', function ($resource = null) {
            return TenantContext::isWithinUsageLimits($resource);
        });

        // @tenantActive - Check if tenant is active
        Blade::if('tenantActive', function () {
            $tenant = TenantResolver::getCurrentTenant();
            return $tenant && $tenant->isActive();
        });

        // @tenantExpired - Check if tenant subscription has expired
        Blade::if('tenantExpired', function () {
            $tenant = TenantResolver::getCurrentTenant();
            return $tenant && $tenant->hasExpired();
        });

        // @tenantTrial - Check if tenant is on trial
        Blade::if('tenantTrial', function () {
            $tenant = TenantResolver::getCurrentTenant();
            return $tenant && $tenant->isOnTrial();
        });
    }

    /**
     * Register view composers
     */
    protected function registerViewComposers(): void
    {
        // Share tenant data with navigation views
        View::composer(['layouts.admin', 'layouts.app'], function ($view) {
            $tenant = TenantResolver::getCurrentTenant();
            
            if ($tenant) {
                $view->with([
                    'currentTenant' => $tenant,
                    'tenantUsageStats' => TenantContext::getUsageStats(),
                    'tenantFeatures' => TenantContext::getTenantFeatures()
                ]);
            }
        });

        // Share tenant settings with settings views
        View::composer('developer.config.*', function ($view) {
            $tenant = TenantResolver::getCurrentTenant();
            
            if ($tenant) {
                $view->with('tenantSettings', $tenant->tenantSettings()->get()->groupBy('category'));
            }
        });
    }

    /**
     * Register helpful macros
     */
    protected function registerMacros(): void
    {
        // Add macro to Request for getting current tenant
        \Illuminate\Http\Request::macro('tenant', function () {
            return TenantResolver::getCurrentTenant();
        });

        // Add macro to Collection for filtering by tenant
        \Illuminate\Support\Collection::macro('forCurrentTenant', function () {
            $tenantId = TenantResolver::getCurrentTenantId();
            
            if (!$tenantId) {
                return $this;
            }

            return $this->filter(function ($item) use ($tenantId) {
                return isset($item->tenant_id) && $item->tenant_id == $tenantId;
            });
        });

        // Add macro to Builder for current tenant
        \Illuminate\Database\Eloquent\Builder::macro('forCurrentTenant', function () {
            $tenantId = TenantResolver::getCurrentTenantId();
            
            if ($tenantId) {
                return $this->where('tenant_id', $tenantId);
            }

            return $this;
        });
    }

    /**
     * Get tenant-aware cache key
     */
    public static function getCacheKey(string $key): string
    {
        $tenantId = TenantResolver::getCurrentTenantId();
        return $tenantId ? "tenant_{$tenantId}_{$key}" : $key;
    }

    /**
     * Get tenant-aware storage path
     */
    public static function getStoragePath(string $path = ''): string
    {
        $tenant = TenantResolver::getCurrentTenant();
        
        if ($tenant) {
            $tenantPath = 'tenants/' . $tenant->slug;
            return $path ? $tenantPath . '/' . $path : $tenantPath;
        }

        return $path;
    }

    /**
     * Get tenant-aware URL
     */
    public static function getTenantUrl(string $path = '', array $parameters = []): string
    {
        $tenant = TenantResolver::getCurrentTenant();
        
        if ($tenant && $tenant->domain) {
            $scheme = request()->isSecure() ? 'https' : 'http';
            $url = $scheme . '://' . $tenant->domain;
            
            if ($path) {
                $url .= '/' . ltrim($path, '/');
            }
            
            if ($parameters) {
                $url .= '?' . http_build_query($parameters);
            }
            
            return $url;
        }

        return url($path, $parameters);
    }

    /**
     * Check if user belongs to current tenant
     */
    public static function userBelongsToCurrentTenant($userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        $tenantId = TenantResolver::getCurrentTenantId();
        
        if (!$userId || !$tenantId) {
            return false;
        }

        return \App\Models\TenantUser::where('user_id', $userId)
                                    ->where('tenant_id', $tenantId)
                                    ->where('status', 'active')
                                    ->exists();
    }

    /**
     * Get available tenants for current user
     */
    public static function getAvailableTenantsForUser($userId = null): \Illuminate\Support\Collection
    {
        $userId = $userId ?? auth()->id();
        
        if (!$userId) {
            return collect([]);
        }

        return Tenant::whereHas('tenantUsers', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('status', 'active');
        })->where('status', 'active')->get();
    }
}