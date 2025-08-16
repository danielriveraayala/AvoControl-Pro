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
        
        // Super admin goes to developer panel
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
            
            $tenantUrl = 'http://' . $tenant->slug . '.avocontrol.com/dashboard';
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
