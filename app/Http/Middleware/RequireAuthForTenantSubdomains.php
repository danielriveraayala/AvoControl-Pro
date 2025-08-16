<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

class RequireAuthForTenantSubdomains
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ensures that tenant subdomains are only accessible
     * to authenticated users who have access to that specific tenant.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        
        // Debug log
        \Log::info('RequireAuthForTenantSubdomains middleware', [
            'host' => $host,
            'url' => $request->url(),
            'authenticated' => \Auth::check()
        ]);
        
        // Check if we're on a subdomain
        if ($this->isSubdomain($host)) {
            // Extract subdomain
            $subdomain = $this->extractSubdomain($host);
            
            // Skip if it's 'dev' or 'www' subdomain
            if (in_array($subdomain, ['dev', 'www', 'api'])) {
                return $next($request);
            }
            
            // Check if subdomain corresponds to a tenant
            $tenant = Tenant::where('slug', $subdomain)->first();
            
            \Log::info('Subdomain check', [
                'subdomain' => $subdomain,
                'tenant_found' => $tenant ? true : false,
                'tenant_id' => $tenant ? $tenant->id : null
            ]);
            
            if ($tenant) {
                // This is a tenant subdomain - require authentication
                if (!Auth::check()) {
                    // Store the full URL as intended for redirect after login
                    $fullUrl = $request->fullUrl();
                    
                    \Log::info('Storing intended URL for unauthenticated user', [
                        'intended_url' => $fullUrl,
                        'subdomain' => $subdomain
                    ]);
                    
                    // Store in session AND use Laravel's intended mechanism
                    session(['url.intended' => $fullUrl]);
                    session()->put('url.intended', $fullUrl);
                    
                    // Also use Laravel's redirect()->guest() which sets intended automatically
                    return redirect()->guest('//avocontrol.pro/login')
                        ->with('error', 'Debes iniciar sesión para acceder a esta empresa.');
                }
                
                // User is authenticated, check if they have access to this tenant
                $user = Auth::user();
                
                // Super admins can access any tenant
                if ($user->hasRole('super_admin')) {
                    return $next($request);
                }
                
                // Check if user has access to this specific tenant
                $hasTenantAccess = $user->tenants()
                    ->where('tenants.id', $tenant->id)
                    ->where('tenants.status', 'active')
                    ->exists();
                
                if (!$hasTenantAccess) {
                    // User doesn't have access to this tenant
                    return redirect()->route('tenant.select')
                        ->with('error', 'No tienes acceso a esta empresa.');
                }
                
                // Set current tenant for the user
                if ($user->current_tenant_id !== $tenant->id) {
                    $user->update(['current_tenant_id' => $tenant->id]);
                }
            }
        }
        
        return $next($request);
    }
    
    /**
     * Check if the current request is on a subdomain
     */
    protected function isSubdomain(string $host): bool
    {
        // Count dots in hostname
        $parts = explode('.', $host);
        
        // For avocontrol.pro, we expect at least 3 parts for a subdomain
        // e.g., tenant.avocontrol.pro
        return count($parts) >= 3;
    }
    
    /**
     * Extract subdomain from host
     */
    protected function extractSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);
        
        if (count($parts) >= 3) {
            // Return first part as subdomain
            return $parts[0];
        }
        
        return null;
    }
}