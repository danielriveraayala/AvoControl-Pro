<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

class TenantContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get current tenant
        $tenant = TenantResolver::getCurrentTenant();

        if ($tenant) {
            // Set tenant-specific configurations
            $this->setTenantConfigurations($tenant);
            
            // Share tenant data with views
            $this->shareTenantDataWithViews($tenant);
            
            // Apply tenant-specific settings
            $this->applyTenantSettings($tenant);
        }

        return $next($request);
    }

    /**
     * Set tenant-specific configurations
     */
    protected function setTenantConfigurations(Tenant $tenant): void
    {
        // Set application name to tenant name
        Config::set('app.name', $tenant->name);

        // Set tenant-specific mail settings if configured
        if ($tenant->getSetting('mail.from.address')) {
            Config::set('mail.from.address', $tenant->getSetting('mail.from.address'));
            Config::set('mail.from.name', $tenant->getSetting('mail.from.name', $tenant->name));
        }

        // Set tenant-specific database connection if using separate DBs
        if ($tenant->getSetting('database.connection')) {
            Config::set('database.default', $tenant->getSetting('database.connection'));
        }

        // Set tenant-specific filesystem disk
        if ($tenant->getSetting('filesystem.disk')) {
            Config::set('filesystems.default', $tenant->getSetting('filesystem.disk'));
        }

        // Set tenant-specific cache prefix
        Config::set('cache.prefix', 'tenant_' . $tenant->id . '_');

        // Set tenant-specific session name
        Config::set('session.cookie', 'avocontrol_session_tenant_' . $tenant->id);
    }

    /**
     * Share tenant data with views
     */
    protected function shareTenantDataWithViews(Tenant $tenant): void
    {
        // Share basic tenant information
        View::share('currentTenant', $tenant);
        View::share('tenantName', $tenant->name);
        View::share('tenantSlug', $tenant->slug);
        View::share('tenantLogo', $tenant->logo_path);
        
        // Share tenant settings
        $tenantSettings = $this->getTenantSettings($tenant);
        View::share('tenantSettings', $tenantSettings);

        // Share tenant features
        View::share('tenantFeatures', $tenant->features ?: []);

        // Share tenant plan information
        View::share('tenantPlan', [
            'name' => $tenant->plan,
            'max_users' => $tenant->max_users,
            'max_storage_mb' => $tenant->max_storage_mb,
            'is_trial' => $tenant->isOnTrial(),
            'expires_at' => $tenant->subscription_expires_at
        ]);
    }

    /**
     * Apply tenant-specific settings
     */
    protected function applyTenantSettings(Tenant $tenant): void
    {
        // Apply timezone setting
        $timezone = $tenant->getSetting('app.timezone', 'America/Mexico_City');
        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);

        // Apply locale setting
        $locale = $tenant->getSetting('app.locale', 'es');
        app()->setLocale($locale);

        // Apply currency setting
        if ($tenant->getSetting('app.currency')) {
            Config::set('app.currency', $tenant->getSetting('app.currency', 'MXN'));
        }

        // Apply notification settings
        $this->applyNotificationSettings($tenant);

        // Apply security settings
        $this->applySecuritySettings($tenant);
    }

    /**
     * Apply notification settings
     */
    protected function applyNotificationSettings(Tenant $tenant): void
    {
        // Email notifications
        if ($tenant->getSetting('notifications.email.enabled', true)) {
            Config::set('notifications.email.enabled', true);
            
            // Custom SMTP settings for tenant
            if ($tenant->getSetting('mail.smtp.host')) {
                Config::set('mail.mailers.smtp', [
                    'transport' => 'smtp',
                    'host' => $tenant->getSetting('mail.smtp.host'),
                    'port' => $tenant->getSetting('mail.smtp.port', 587),
                    'encryption' => $tenant->getSetting('mail.smtp.encryption', 'tls'),
                    'username' => $tenant->getSetting('mail.smtp.username'),
                    'password' => $tenant->getSetting('mail.smtp.password'),
                ]);
            }
        }

        // Push notifications
        if ($tenant->getSetting('notifications.push.enabled', true)) {
            Config::set('notifications.push.enabled', true);
            
            // Custom VAPID keys for tenant
            if ($tenant->getSetting('push.vapid.public_key')) {
                Config::set('webpush.VAPID.public_key', $tenant->getSetting('push.vapid.public_key'));
                Config::set('webpush.VAPID.private_key', $tenant->getSetting('push.vapid.private_key'));
                Config::set('webpush.VAPID.subject', $tenant->getSetting('push.vapid.subject', $tenant->name));
            }
        }
    }

    /**
     * Apply security settings
     */
    protected function applySecuritySettings(Tenant $tenant): void
    {
        // Session security
        $sessionLifetime = $tenant->getSetting('security.session_lifetime', 120);
        Config::set('session.lifetime', $sessionLifetime);

        // Password requirements
        if ($tenant->getSetting('security.password.min_length')) {
            Config::set('auth.password.min_length', $tenant->getSetting('security.password.min_length', 8));
        }

        // Rate limiting
        if ($tenant->getSetting('security.rate_limit.api')) {
            Config::set('throttle.api', $tenant->getSetting('security.rate_limit.api', '60,1'));
        }

        // Two-factor authentication
        if ($tenant->getSetting('security.2fa.required')) {
            Config::set('auth.2fa.required', true);
        }
    }

    /**
     * Get tenant settings organized by category
     */
    protected function getTenantSettings(Tenant $tenant): array
    {
        return Cache::remember("tenant_{$tenant->id}_settings", 1800, function () use ($tenant) {
            $settings = $tenant->tenantSettings()->get();
            $organized = [];

            foreach ($settings as $setting) {
                $organized[$setting->category][$setting->key] = [
                    'value' => $setting->typed_value,
                    'type' => $setting->type,
                    'is_public' => $setting->is_public,
                    'description' => $setting->description
                ];
            }

            return $organized;
        });
    }

    /**
     * Get tenant feature flags
     */
    public static function getTenantFeatures(): array
    {
        $tenant = TenantResolver::getCurrentTenant();
        return $tenant ? ($tenant->features ?: []) : [];
    }

    /**
     * Check if tenant has specific feature enabled
     */
    public static function hasFeature(string $feature): bool
    {
        $tenant = TenantResolver::getCurrentTenant();
        return $tenant ? $tenant->hasFeature($feature) : false;
    }

    /**
     * Get tenant setting value
     */
    public static function getSetting(string $key, $default = null)
    {
        $tenant = TenantResolver::getCurrentTenant();
        return $tenant ? $tenant->getSetting($key, $default) : $default;
    }

    /**
     * Check if tenant is within usage limits
     */
    public static function isWithinUsageLimits(string $resource = null): bool
    {
        $tenant = TenantResolver::getCurrentTenant();
        
        if (!$tenant) {
            return false;
        }

        switch ($resource) {
            case 'users':
                $currentUsers = $tenant->tenantUsers()->where('status', 'active')->count();
                return $currentUsers < $tenant->max_users;
                
            case 'storage':
                // This would need to be implemented based on actual storage usage
                return true;
                
            default:
                return true;
        }
    }

    /**
     * Get tenant usage statistics
     */
    public static function getUsageStats(): array
    {
        $tenant = TenantResolver::getCurrentTenant();
        
        if (!$tenant) {
            return [];
        }

        return [
            'users' => [
                'current' => $tenant->tenantUsers()->where('status', 'active')->count(),
                'limit' => $tenant->max_users,
                'percentage' => round(($tenant->tenantUsers()->where('status', 'active')->count() / $tenant->max_users) * 100, 2)
            ],
            'storage' => [
                'current' => 0, // Would need actual implementation
                'limit' => $tenant->max_storage_mb,
                'percentage' => 0
            ],
            'plan' => $tenant->plan,
            'expires_at' => $tenant->subscription_expires_at,
            'is_trial' => $tenant->isOnTrial()
        ];
    }
}