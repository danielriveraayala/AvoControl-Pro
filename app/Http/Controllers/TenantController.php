<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Http\Middleware\TenantResolver;
use App\Providers\TenantServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TenantController extends Controller
{
    /**
     * Show tenant selection page
     */
    public function select()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $tenants = $user->tenants()->where('tenants.status', 'active')->get();
        
        return view('tenant.select', compact('tenants'));
    }

    /**
     * Switch to a specific tenant
     */
    public function switch(Request $request, $tenantSlug)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Find tenant by slug
        $tenant = Tenant::where('slug', $tenantSlug)
                       ->where('status', 'active')
                       ->first();

        if (!$tenant) {
            return redirect()->back()->with('error', 'Tenant no encontrado o inactivo.');
        }

        // Check if user has access to this tenant
        $tenantUser = TenantUser::where('tenant_id', $tenant->id)
                               ->where('user_id', $user->id)
                               ->where('status', 'active')
                               ->first();

        if (!$tenantUser) {
            return redirect()->back()->with('error', 'No tienes acceso a este tenant.');
        }

        // Switch user to this tenant
        try {
            $user->switchToTenant($tenant->id);
            
            Log::info('User switched tenant', [
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'tenant_slug' => $tenant->slug
            ]);

            return redirect()->route('dashboard')->with('success', "Has cambiado al tenant: {$tenant->name}");
        } catch (\Exception $e) {
            Log::error('Error switching tenant', [
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al cambiar de tenant. Intenta nuevamente.');
        }
    }

    /**
     * Get current tenant information (API)
     */
    public function current()
    {
        $tenant = TenantResolver::getCurrentTenant();
        
        if (!$tenant) {
            return response()->json([
                'error' => 'No tenant found'
            ], 404);
        }

        return response()->json([
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'domain' => $tenant->domain,
            'plan' => $tenant->plan,
            'status' => $tenant->status,
            'features' => $tenant->features,
            'settings' => $tenant->settings
        ]);
    }

    /**
     * Get available tenants for current user (API)
     */
    public function available()
    {
        if (!auth()->check()) {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }

        $tenants = TenantServiceProvider::getAvailableTenantsForUser();
        
        return response()->json([
            'tenants' => $tenants->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'domain' => $tenant->domain,
                    'plan' => $tenant->plan,
                    'status' => $tenant->status,
                    'is_current' => $tenant->id == auth()->user()->current_tenant_id
                ];
            })
        ]);
    }

    /**
     * Create a new tenant (Super Admin only)
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('super_admin')) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug',
            'email' => 'required|email|unique:tenants,email',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'plan' => 'required|in:basic,premium,enterprise,custom,trial',
            'max_users' => 'required|integer|min:1',
            'max_storage_mb' => 'required|integer|min:100',
            'features' => 'nullable|array',
            'settings' => 'nullable|array'
        ]);

        try {
            $tenant = Tenant::create($validatedData);

            Log::info('Tenant created', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Tenant creado exitosamente',
                'tenant' => $tenant
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating tenant', [
                'error' => $e->getMessage(),
                'data' => $validatedData
            ]);

            return response()->json([
                'error' => 'Error al crear tenant',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update tenant (Super Admin or Tenant Admin)
     */
    public function update(Request $request, Tenant $tenant)
    {
        $user = auth()->user();
        
        // Check permissions
        if (!$user->hasRole('super_admin') && !$user->isAdminInTenant($tenant->id)) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:tenants,email,' . $tenant->id,
            'domain' => 'sometimes|nullable|string|max:255|unique:tenants,domain,' . $tenant->id,
            'plan' => 'sometimes|in:basic,premium,enterprise,custom,trial',
            'status' => 'sometimes|in:active,inactive,suspended,pending',
            'max_users' => 'sometimes|integer|min:1',
            'max_storage_mb' => 'sometimes|integer|min:100',
            'features' => 'sometimes|nullable|array',
            'settings' => 'sometimes|nullable|array'
        ]);

        try {
            $tenant->update($validatedData);

            Log::info('Tenant updated', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'updated_by' => $user->id,
                'changes' => $validatedData
            ]);

            return response()->json([
                'message' => 'Tenant actualizado exitosamente',
                'tenant' => $tenant->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating tenant', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
                'data' => $validatedData
            ]);

            return response()->json([
                'error' => 'Error al actualizar tenant',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tenant usage statistics
     */
    public function stats(Tenant $tenant = null)
    {
        $tenant = $tenant ?? TenantResolver::getCurrentTenant();
        
        if (!$tenant) {
            return response()->json([
                'error' => 'No tenant found'
            ], 404);
        }

        $user = auth()->user();
        
        // Check permissions
        if (!$user->hasRole('super_admin') && !$user->isAdminInTenant($tenant->id)) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $stats = [
            'tenant_info' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'plan' => $tenant->plan,
                'status' => $tenant->status,
                'created_at' => $tenant->created_at
            ],
            'usage' => [
                'users' => [
                    'current' => $tenant->tenantUsers()->where('status', 'active')->count(),
                    'limit' => $tenant->max_users,
                    'percentage' => round(($tenant->tenantUsers()->where('status', 'active')->count() / $tenant->max_users) * 100, 2)
                ],
                'storage' => [
                    'current' => 0, // Would need implementation
                    'limit' => $tenant->max_storage_mb,
                    'percentage' => 0
                ]
            ],
            'business_data' => [
                'suppliers' => $tenant->suppliers()->count(),
                'customers' => $tenant->customers()->count(),
                'lots' => $tenant->lots()->count(),
                'sales' => $tenant->sales()->count(),
                'payments' => $tenant->payments()->count()
            ],
            'subscription' => [
                'plan' => $tenant->plan,
                'expires_at' => $tenant->subscription_expires_at,
                'trial_ends_at' => $tenant->trial_ends_at,
                'is_trial' => $tenant->isOnTrial(),
                'is_expired' => $tenant->hasExpired()
            ]
        ];

        return response()->json($stats);
    }
}