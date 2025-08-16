<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TenantResolver
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip tenant resolution for developer routes
        if ($this->shouldSkipTenantResolution($request)) {
            return $next($request);
        }

        // Check if user is accessing from main domain and should be redirected to tenant subdomain
        if ($this->shouldRedirectToTenantSubdomain($request)) {
            return $this->redirectToUserTenant($request);
        }

        // Try to resolve tenant
        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            // Set tenant in application context
            app()->instance('current_tenant', $tenant);
            
            // Store tenant ID in session
            session(['current_tenant_id' => $tenant->id]);
            
            // Set tenant for authenticated user if not already set
            if (auth()->check() && !auth()->user()->current_tenant_id) {
                auth()->user()->update(['current_tenant_id' => $tenant->id]);
            }
            
            // Log tenant resolution for debugging
            Log::info('Tenant resolved', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'tenant_slug' => $tenant->slug,
                'domain' => $request->getHost(),
                'user_id' => auth()->id()
            ]);
        } else {
            // Handle tenant not found
            return $this->handleTenantNotFound($request);
        }

        return $next($request);
    }

    /**
     * Resolve tenant from request
     */
    protected function resolveTenant(Request $request): ?Tenant
    {
        $host = $request->getHost();
        
        // Try different resolution strategies
        $tenant = $this->resolveByDomain($host) 
                ?? $this->resolveBySubdomain($host)
                ?? $this->resolveBySession($request)
                ?? $this->resolveByUser($request)
                ?? $this->resolveDefault();

        return $tenant;
    }

    /**
     * Resolve tenant by full domain
     */
    protected function resolveByDomain(string $host): ?Tenant
    {
        return Cache::remember("tenant_domain_{$host}", 3600, function () use ($host) {
            return Tenant::where('domain', $host)
                        ->where('status', 'active')
                        ->first();
        });
    }

    /**
     * Resolve tenant by subdomain
     */
    protected function resolveBySubdomain(string $host): ?Tenant
    {
        $parts = explode('.', $host);
        
        // If it's a subdomain (more than 2 parts), extract subdomain
        if (count($parts) > 2) {
            $subdomain = $parts[0];
            
            return Cache::remember("tenant_subdomain_{$subdomain}", 3600, function () use ($subdomain) {
                return Tenant::where('slug', $subdomain)
                            ->where('status', 'active')
                            ->first();
            });
        }

        return null;
    }

    /**
     * Resolve tenant from session
     */
    protected function resolveBySession(Request $request): ?Tenant
    {
        $tenantId = session('current_tenant_id');
        
        if ($tenantId) {
            return Cache::remember("tenant_id_{$tenantId}", 3600, function () use ($tenantId) {
                return Tenant::where('id', $tenantId)
                            ->where('status', 'active')
                            ->first();
            });
        }

        return null;
    }

    /**
     * Resolve tenant from authenticated user
     */
    protected function resolveByUser(Request $request): ?Tenant
    {
        if (auth()->check() && auth()->user()->current_tenant_id) {
            $tenantId = auth()->user()->current_tenant_id;
            
            return Cache::remember("tenant_id_{$tenantId}", 3600, function () use ($tenantId) {
                return Tenant::where('id', $tenantId)
                            ->where('status', 'active')
                            ->first();
            });
        }

        return null;
    }

    /**
     * Resolve default tenant (for development or fallback)
     */
    protected function resolveDefault(): ?Tenant
    {
        // In development, use a default tenant
        if (app()->environment('local')) {
            return Cache::remember('default_tenant', 3600, function () {
                return Tenant::where('status', 'active')
                            ->where('slug', 'default')
                            ->orWhere('plan', 'basic')
                            ->first();
            });
        }

        return null;
    }

    /**
     * Check if tenant resolution should be skipped
     */
    protected function shouldSkipTenantResolution(Request $request): bool
    {
        $skipRoutes = [
            'developer/*',
            'api/health',
            'telescope/*',
            'horizon/*',
            '_debugbar/*',
            '/',
            'pricing',
            'features',
            'contact',
            'login',
            'register',
            'password/*',
            'subscription/register',
            'subscription/register/*',
            'subscription/success',
            'subscription/cancelled',
            'paypal/webhook'
        ];

        foreach ($skipRoutes as $route) {
            if ($request->is($route)) {
                return true;
            }
        }

        // Skip for super admin users in developer panel
        if (auth()->check() && auth()->user()->hasRole('super_admin') && $request->is('developer/*')) {
            return true;
        }

        return false;
    }

    /**
     * Handle tenant not found scenario
     */
    protected function handleTenantNotFound(Request $request)
    {
        $host = $request->getHost();
        
        Log::warning('Tenant not found', [
            'host' => $host,
            'path' => $request->path(),
            'user_id' => auth()->id()
        ]);

        // For API requests, return JSON error
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Tenant not found',
                'message' => 'No active tenant found for this domain.',
                'domain' => $host
            ], 404);
        }

        // For web requests, redirect to tenant selection or show error page
        if (auth()->check()) {
            // User is authenticated, redirect to tenant selection
            return redirect()->route('tenant.select')->with('error', 'Please select a tenant to continue.');
        }

        // Guest user, show tenant not found page
        return response()->view('errors.tenant-not-found', [
            'domain' => $host,
            'message' => 'No active tenant found for this domain.'
        ], 404);
    }

    /**
     * Get current tenant from application context
     */
    public static function getCurrentTenant(): ?Tenant
    {
        return app('current_tenant');
    }

    /**
     * Check if tenant is currently resolved
     */
    public static function hasTenant(): bool
    {
        return app()->bound('current_tenant') && app('current_tenant') !== null;
    }

    /**
     * Get current tenant ID
     */
    public static function getCurrentTenantId(): ?int
    {
        $tenant = self::getCurrentTenant();
        return $tenant ? $tenant->id : null;
    }

    /**
     * Check if user should be redirected from main domain to tenant subdomain
     */
    protected function shouldRedirectToTenantSubdomain(Request $request): bool
    {
        // Only redirect authenticated users
        if (!auth()->check()) {
            return false;
        }

        $host = $request->getHost();
        
        // Only redirect if accessing from main domain (avocontrol.pro)
        if ($host !== 'avocontrol.pro') {
            return false;
        }

        // Skip redirect for certain routes that should remain on main domain
        $skipRedirectRoutes = [
            'login', 'logout', 'register', 'password/*',
            'subscription/*', 'paypal/*', 'tenant/select', 'developer/*'
        ];

        foreach ($skipRedirectRoutes as $route) {
            if ($request->is($route)) {
                return false;
            }
        }

        // Skip for super admin (they should access developer panel from main domain)
        if (auth()->user()->hasRole('super_admin')) {
            return false;
        }

        return true;
    }

    /**
     * Redirect user to their tenant subdomain
     */
    protected function redirectToUserTenant(Request $request)
    {
        $user = auth()->user();
        
        // Get user's tenants
        $userTenants = $user->tenants()->where('tenants.status', 'active')->get();
        
        if ($userTenants->count() === 1) {
            // Single tenant - redirect directly
            $tenant = $userTenants->first();
            $user->update(['current_tenant_id' => $tenant->id]);
            
            $path = $request->getPathInfo();
            $query = $request->getQueryString();
            $tenantUrl = 'https://' . $tenant->slug . '.avocontrol.pro' . $path;
            
            if ($query) {
                $tenantUrl .= '?' . $query;
            }
            
            return redirect()->away($tenantUrl);
        } elseif ($userTenants->count() > 1) {
            // Multiple tenants - redirect to tenant selection
            return redirect()->route('tenant.select');
        } else {
            // No tenants - redirect to tenant selection
            return redirect()->route('tenant.select')->with('warning', 'No tienes acceso a ninguna empresa.');
        }
    }
}