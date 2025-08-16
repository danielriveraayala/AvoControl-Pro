<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Check if user has a tenant and redirect to tenant subdomain
        $user = Auth::user();
        
        // Check if there's an intended URL (e.g., from trying to access a tenant subdomain)
        $intendedUrl = session('url.intended');
        
        // If there's an intended URL and it's a tenant subdomain
        if ($intendedUrl) {
            // Parse the URL to extract the subdomain
            $host = parse_url($intendedUrl, PHP_URL_HOST);
            if ($host) {
                $parts = explode('.', $host);
                
                // Check if it's a tenant subdomain (3+ parts)
                if (count($parts) >= 3 && $parts[1] === 'avocontrol' && $parts[2] === 'pro') {
                    $subdomain = $parts[0];
                    
                    // Verify user has access to this tenant
                    $tenant = $user->tenants()
                        ->where('tenants.slug', $subdomain)
                        ->where('tenants.status', 'active')
                        ->first();
                    
                    if ($tenant || $user->hasRole('super_admin')) {
                        // User has access to this tenant or is super admin
                        if ($tenant) {
                            $user->update(['current_tenant_id' => $tenant->id]);
                        } else if ($user->hasRole('super_admin')) {
                            // Super admin accessing a tenant - find the tenant by slug
                            $tenantForAdmin = \App\Models\Tenant::where('slug', $subdomain)
                                ->where('status', 'active')
                                ->first();
                            if ($tenantForAdmin) {
                                $user->update(['current_tenant_id' => $tenantForAdmin->id]);
                            }
                        }
                        
                        // Clear the intended URL from session
                        session()->forget('url.intended');
                        
                        // Redirect to the intended URL
                        return redirect($intendedUrl);
                    }
                }
            }
        }
        
        // Super admin goes to developer panel if no intended URL
        if ($user->hasRole('super_admin')) {
            return redirect()->to('https://dev.avocontrol.pro/developer');
        }
        
        // Get user's tenants
        $userTenants = $user->tenants()->where('tenants.status', 'active')->get();
        
        // If user has multiple tenants, let them choose
        if ($userTenants->count() > 1) {
            return redirect()->route('tenant.select');
        }
        
        // If user has exactly one tenant, redirect there
        if ($userTenants->count() == 1) {
            $tenant = $userTenants->first();
            $user->update(['current_tenant_id' => $tenant->id]);
            
            $tenantUrl = 'http://' . $tenant->slug . '.avocontrol.pro/dashboard';
            return redirect()->away($tenantUrl);
        }
        
        // Default redirect if no tenant
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
