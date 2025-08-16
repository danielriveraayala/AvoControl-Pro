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
        \Log::info('LOGIN PAGE ACCESSED', [
            'url' => request()->fullUrl(),
            'host' => request()->getHost(),
            'user_agent' => request()->userAgent()
        ]);
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
        \Log::info('LOGIN ATTEMPT START', [
            'email' => $request->input('email'),
            'has_password' => !empty($request->input('password')),
            'request_host' => $request->getHost(),
            'request_url' => $request->fullUrl()
        ]);

        try {
            $request->authenticate();
            \Log::info('AUTHENTICATION SUCCESS');
        } catch (\Exception $e) {
            \Log::error('AUTHENTICATION FAILED', [
                'error' => $e->getMessage(),
                'email' => $request->input('email')
            ]);
            throw $e;
        }

        $request->session()->regenerate();
        \Log::info('SESSION REGENERATED');

        // Check if user has a tenant and redirect to tenant subdomain
        $user = Auth::user();
        
        \Log::info('USER AUTHENTICATED', [
            'user_id' => $user->id,
            'email' => $user->email,
            'current_tenant_id' => $user->current_tenant_id
        ]);
        
        // Super admin goes to developer panel
        if ($user->hasRole('super_admin')) {
            \Log::info('REDIRECTING SUPER ADMIN', ['url' => 'https://avocontrol.pro/developer']);
            return redirect()->to('https://avocontrol.pro/developer');
        }
        
        // Get user's tenants
        $userTenants = $user->tenants()->where('tenants.status', 'active')->get();
        
        \Log::info('USER TENANTS FOUND', [
            'count' => $userTenants->count(),
            'tenant_slugs' => $userTenants->pluck('slug')->toArray()
        ]);
        
        // If user has multiple tenants, let them choose
        if ($userTenants->count() > 1) {
            \Log::info('MULTIPLE TENANTS - REDIRECTING TO SELECTION');
            return redirect()->route('tenant.select');
        }
        
        // If user has exactly one tenant, redirect there
        if ($userTenants->count() == 1) {
            $tenant = $userTenants->first();
            $user->update(['current_tenant_id' => $tenant->id]);
            
            $tenantUrl = 'https://' . $tenant->slug . '.avocontrol.pro/dashboard';
            \Log::info('SINGLE TENANT - REDIRECTING', ['url' => $tenantUrl]);
            return redirect()->away($tenantUrl);
        }
        
        // Default redirect if no tenant
        \Log::info('NO TENANTS - DEFAULT REDIRECT', ['url' => RouteServiceProvider::HOME]);
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
        // Only logout if user is authenticated
        if (Auth::check()) {
            // Clear user's current tenant
            Auth::user()->update(['current_tenant_id' => null]);
            
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        
        // Create response with explicit cookie clearing
        $response = redirect('/')->with('message', 'Sesión cerrada correctamente');
        
        // Clear the session cookie for the entire domain
        $cookieName = config('session.cookie');
        $response->withCookie(cookie()->forget($cookieName, '/', '.avocontrol.pro'));
        
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