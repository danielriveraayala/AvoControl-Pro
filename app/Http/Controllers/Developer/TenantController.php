<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $plan = $request->get('plan');

        $tenants = Tenant::with(['users', 'subscriptions'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('domain', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($plan, function ($query, $plan) {
                return $query->where('plan', $plan);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        
        $stats = [
            'total' => Tenant::count(),
            'active' => Tenant::where('status', 'active')->count(),
            'trial' => Tenant::where('plan', 'trial')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'expired' => Tenant::where('status', 'inactive')->count(), // Renamed for view compatibility
        ];

        // Map Tenant data to match TenantSubscription structure expected by view
        $subscriptions = $tenants->getCollection()->map(function ($tenant) {
            // Create a mock object with the properties the view expects
            $mock = new \stdClass();
            $mock->id = $tenant->id;
            $mock->tenant_name = $tenant->name;
            $mock->tenant_domain = $tenant->slug;
            $mock->contact_name = $tenant->name; // Fallback to tenant name
            $mock->contact_email = $tenant->email;
            
            // Status mapping
            $mock->status_display = $this->getStatusDisplay($tenant->status);
            $mock->status_color = $this->getStatusColor($tenant->status);
            
            // Plan mapping - use tenant's plan field directly since subscriptions relationship is empty
            if ($tenant->plan) {
                // Create a mock plan object with the properties the view expects
                $planMock = new \stdClass();
                $planMock->display_name = ucfirst($tenant->plan);
                $planMock->price = $this->getPlanPrice($tenant->plan);
                $planMock->name = $tenant->plan;
                $mock->plan = $planMock;
            } else {
                // Fallback plan object
                $planMock = new \stdClass();
                $planMock->display_name = 'Sin plan';
                $planMock->price = 0;
                $planMock->name = 'none';
                $mock->plan = $planMock;
            }
            
            // Dates - ensure these are properly formatted for the view
            $mock->created_at = $tenant->created_at;
            $mock->created_at_formatted = $tenant->created_at->format('d/m/Y');
            $mock->created_at_human = $tenant->created_at->diffForHumans();
            
            // Methods the view expects - convert closures to boolean values
            $mock->isTrial = $tenant->plan === 'trial';
            $mock->trial_days_remaining = $this->getTrialDaysRemaining($tenant);
            $mock->current_users = $tenant->users()->count();
            $mock->lots_this_month = $tenant->lots()->whereMonth('created_at', now()->month)->count();
            $mock->isOverLimits = false; // Simplified for now
            $mock->isSuspended = $tenant->status === 'suspended';
            $mock->isActive = $tenant->status === 'active';
            
            return $mock;
        });
        
        // Set the mapped collection back to the paginator
        $tenants->setCollection($subscriptions);
        $subscriptions = $tenants;
        
        return view('developer.tenants.index', compact('subscriptions', 'plans', 'stats', 'search', 'status', 'plan'));
    }

    public function create()
    {
        $plans = ['trial', 'basic', 'premium', 'enterprise', 'corporate'];
        return view('developer.tenants.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'required|string|max:100|unique:tenants,slug|alpha_dash',
            'email' => 'required|email|max:150|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'plan' => 'required|in:trial,basic,premium,enterprise,corporate',
            'status' => 'required|in:active,inactive,suspended',
            'address' => 'nullable|string',
        ]);

        try {
            // Create new Tenant
            $tenant = new Tenant();
            $tenant->name = $request->name;
            $tenant->slug = strtolower($request->slug);
            $tenant->email = strtolower($request->email);
            $tenant->phone = $request->phone;
            $tenant->plan = $request->plan;
            $tenant->status = $request->status;
            $tenant->address = $request->address;

            // Set dates based on status
            if ($request->status === 'trial' || $tenant->plan === 'trial') {
                $tenant->trial_ends_at = Carbon::now()->addDays(14);
            }

            $tenant->save();

            Log::info('New tenant created', [
                'tenant_name' => $tenant->name,
                'tenant_slug' => $tenant->slug,
                'plan' => $tenant->plan,
                'status' => $tenant->status,
                'created_by' => auth()->user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tenant creado exitosamente',
                'redirect' => route('developer.tenants.show', $tenant)
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating tenant subscription', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['users', 'subscriptions']);
        $usageSummary = []; // TODO: Implement usage summary for Tenant model
        $limitViolations = []; // TODO: Implement limit violations for Tenant model
        
        return view('developer.tenants.show', compact('tenant', 'usageSummary', 'limitViolations'));
    }

    public function edit(Tenant $tenant)
    {
        $plans = ['trial', 'basic', 'premium', 'enterprise', 'corporate'];
        return view('developer.tenants.edit', compact('tenant', 'plans'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'required|string|max:100|alpha_dash|unique:tenants,slug,' . $tenant->id,
            'email' => 'required|email|max:150|unique:tenants,email,' . $tenant->id,
            'phone' => 'nullable|string|max:20',
            'plan' => 'required|in:trial,basic,premium,enterprise,corporate',
            'status' => 'required|in:active,inactive,suspended',
            'address' => 'nullable|string',
        ]);

        try {
            $oldPlan = $tenant->plan;
            $tenant->update($request->only([
                'name', 'slug', 'email', 'phone', 
                'plan', 'status', 'address'
            ]));

            // Log plan change
            if ($oldPlan != $request->plan) {
                Log::info('Tenant plan changed', [
                    'tenant_name' => $tenant->name,
                    'old_plan' => $oldPlan,
                    'new_plan' => $request->plan,
                    'changed_by' => auth()->user()->name
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Suscripción actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating tenant subscription', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    public function suspend(Request $request, Tenant $tenant)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            // Update tenant status to suspended
            $tenant->status = 'suspended';
            $tenant->save();

            Log::info('Tenant suspended', [
                'tenant_name' => $tenant->name,
                'reason' => $request->reason,
                'suspended_by' => auth()->user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Suscripción suspendida exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al suspender la suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activate(Tenant $tenant)
    {
        try {
            // Update tenant status to active
            $tenant->status = 'active';
            $tenant->save();

            Log::info('Tenant activated', [
                'tenant_name' => $tenant->name,
                'activated_by' => auth()->user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Suscripción activada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al activar la suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    public function extendTrial(Request $request, Tenant $tenant)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:90'
        ]);

        try {
            if ($tenant->plan === 'trial' && $tenant->trial_ends_at) {
                $tenant->trial_ends_at = $tenant->trial_ends_at->addDays($request->days);
                $tenant->save();
                
                Log::info('Trial extended', [
                    'tenant_name' => $tenant->name,
                    'extended_days' => $request->days,
                    'new_trial_end' => $tenant->trial_ends_at->format('Y-m-d H:i:s'),
                    'extended_by' => auth()->user()->name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Período de prueba extendido por {$request->days} días"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se puede extender el período de prueba para tenants en plan trial'
                ], 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al extender el período de prueba: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Tenant $tenant)
    {
        try {
            $tenantName = $tenant->name;
            
            // Solo permitir eliminación si está cancelled o expired
            if (!in_array($tenant->status, ['cancelled', 'expired'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar suscripciones canceladas o expiradas'
                ], 422);
            }

            $tenant->delete();

            Log::info('Tenant subscription deleted', [
                'tenant_name' => $tenantName,
                'deleted_by' => auth()->user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Suscripción eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    public function refreshUsage(Tenant $tenant)
    {
        try {
            // Update last activity timestamp
            $tenant->last_activity_at = Carbon::now();
            $tenant->save();

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas de uso actualizadas',
                'last_update' => $tenant->last_activity_at ? $tenant->last_activity_at->format('d/m/Y H:i') : 'N/A'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper methods for view compatibility
     */
    private function getStatusDisplay($status)
    {
        $statuses = [
            'active' => 'Activo',
            'suspended' => 'Suspendido',
            'inactive' => 'Inactivo',
            'trial' => 'Trial',
            'cancelled' => 'Cancelado'
        ];
        
        return $statuses[$status] ?? ucfirst($status);
    }
    
    private function getStatusColor($status)
    {
        $colors = [
            'active' => 'green',
            'suspended' => 'red',
            'inactive' => 'gray',
            'trial' => 'yellow',
            'cancelled' => 'gray'
        ];
        
        return $colors[$status] ?? 'gray';
    }
    
    private function getTrialDaysRemaining($tenant)
    {
        if ($tenant->plan !== 'trial' || !$tenant->trial_ends_at) {
            return 0;
        }
        
        return max(0, $tenant->trial_ends_at->diffInDays(now(), false));
    }
    
    /**
     * Get plan price based on plan name
     */
    private function getPlanPrice($planName)
    {
        $planPrices = [
            'trial' => 0,
            'basic' => 29,
            'premium' => 79,
            'enterprise' => 199,
            'corporate' => 999
        ];
        
        return $planPrices[strtolower($planName)] ?? 0;
    }
}