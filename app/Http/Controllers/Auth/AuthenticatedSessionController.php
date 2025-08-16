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
        
        // Check for intended URL from session (set by our middleware)
        $intendedUrl = session('url.intended');
        
        // Log for debugging
        \Log::info('Login redirect debug', [
            'user_email' => $user->email,
            'intended_url' => $intendedUrl,
            'session_intended' => session('url.intended'),
            'has_intended_url' => !empty($intendedUrl)
        ]);
        
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
                        
                        // Redirect to tenant dashboard (not the original URL which might be /login)
                        $tenantDashboardUrl = 'https://' . $subdomain . '.avocontrol.pro/dashboard';
                        return redirect($tenantDashboardUrl);
                    }
                }
            }
        }
        
        // Super admin goes to developer panel if no intended URL
        if ($user->hasRole('super_admin')) {
            return redirect()->to('https://avocontrol.pro/developer');
        }
        
        // Check from which domain the login is happening
        $currentHost = $request->getHost();
        $isMainDomain = ($currentHost === 'avocontrol.pro');
        
        // Get user's tenants
        $userTenants = $user->tenants()->where('tenants.status', 'active')->get();
        
        // If user has multiple tenants, let them choose
        if ($userTenants->count() > 1) {
            return redirect()->route('tenant.select');
        }
        
        // If user has exactly one tenant and is NOT logging in from main domain
        if ($userTenants->count() == 1 && !$isMainDomain) {
            $tenant = $userTenants->first();
            $user->update(['current_tenant_id' => $tenant->id]);
            
            $tenantUrl = 'https://' . $tenant->slug . '.avocontrol.pro/dashboard';
            return redirect()->away($tenantUrl);
        }
        
        // For main domain login or no tenants, go to main dashboard
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Handle GET logout (from tenant subdomains)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getLogout(Request $request)
    {
        \Log::info('GET Logout attempt', [
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'user_email' => Auth::user() ? Auth::user()->email : null,
            'host' => $request->getHost(),
            'session_id' => $request->session()->getId(),
            'url' => $request->fullUrl(),
            'user_agent' => $request->userAgent()
        ]);

        // Only logout if user is authenticated
        if (Auth::check()) {
            $userId = Auth::id();
            $userEmail = Auth::user()->email;
            
            // Clear user's current tenant
            Auth::user()->update(['current_tenant_id' => null]);
            
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            \Log::info('Logout completed successfully', [
                'user_id' => $userId,
                'user_email' => $userEmail,
                'new_session_id' => $request->session()->getId(),
                'auth_check_after' => Auth::check()
            ]);
        } else {
            \Log::info('No user to logout');
        }
        
        // Create response with explicit cookie clearing
        $response = redirect('/')->with('message', 'Sesión cerrada correctamente');
        
        // Clear the session cookie for the entire domain
        $cookieName = config('session.cookie');
        $response->withCookie(cookie()->forget($cookieName, '/', '.avocontrol.pro'));
        
        \Log::info('Logout redirect created', [
            'redirect_url' => '/',
            'cookie_cleared' => $cookieName
        ]);
        
        return $response;
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
