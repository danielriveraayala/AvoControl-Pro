<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\TenantPlan;
use App\Models\TenantSubscription;
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

        $subscriptions = TenantSubscription::with(['plan', 'creator'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('tenant_name', 'like', "%{$search}%")
                      ->orWhere('tenant_domain', 'like', "%{$search}%")
                      ->orWhere('contact_name', 'like', "%{$search}%")
                      ->orWhere('contact_email', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($plan, function ($query, $plan) {
                return $query->where('tenant_plan_id', $plan);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $plans = TenantPlan::active()->ordered()->get();
        
        $stats = [
            'total' => TenantSubscription::count(),
            'active' => TenantSubscription::active()->count(),
            'trial' => TenantSubscription::trial()->count(),
            'suspended' => TenantSubscription::suspended()->count(),
            'expired' => TenantSubscription::expired()->count(),
        ];

        return view('developer.tenants.index', compact('subscriptions', 'plans', 'stats', 'search', 'status', 'plan'));
    }

    public function create()
    {
        $plans = TenantPlan::active()->ordered()->get();
        return view('developer.tenants.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenant_name' => 'required|string|max:150',
            'tenant_domain' => 'required|string|max:100|unique:tenant_subscriptions,tenant_domain|alpha_dash',
            'contact_name' => 'required|string|max:150',
            'contact_email' => 'required|email|max:150|unique:tenant_subscriptions,contact_email',
            'contact_phone' => 'nullable|string|max:20',
            'tenant_plan_id' => 'required|exists:tenant_plans,id',
            'status' => 'required|in:trial,active',
            'billing_name' => 'nullable|string|max:150',
            'billing_address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        try {
            $plan = TenantPlan::findOrFail($request->tenant_plan_id);
            
            $subscription = new TenantSubscription();
            $subscription->tenant_name = $request->tenant_name;
            $subscription->tenant_domain = strtolower($request->tenant_domain);
            $subscription->contact_name = $request->contact_name;
            $subscription->contact_email = strtolower($request->contact_email);
            $subscription->contact_phone = $request->contact_phone;
            $subscription->tenant_plan_id = $request->tenant_plan_id;
            $subscription->status = $request->status;
            $subscription->billing_name = $request->billing_name;
            $subscription->billing_address = $request->billing_address;
            $subscription->tax_id = $request->tax_id;
            $subscription->notes = $request->notes;
            $subscription->created_by = auth()->id();

            // Set dates based on status
            if ($request->status === 'trial') {
                $subscription->trial_ends_at = Carbon::now()->addDays(14);
                $subscription->current_period_start = Carbon::now();
                $subscription->current_period_end = Carbon::now()->addDays(14);
            } else {
                $subscription->current_period_start = Carbon::now();
                $subscription->current_period_end = $plan->billing_cycle === 'yearly' 
                    ? Carbon::now()->addYear() 
                    : Carbon::now()->addMonth();
            }

            $subscription->save();

            Log::info('New tenant subscription created', [
                'tenant_name' => $subscription->tenant_name,
                'tenant_domain' => $subscription->tenant_domain,
                'plan' => $plan->name,
                'status' => $subscription->status,
                'created_by' => auth()->user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Suscripción de tenant creada exitosamente',
                'redirect' => route('developer.tenants.show', $subscription)
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

    public function show(TenantSubscription $tenant)
    {
        $tenant->load(['plan', 'creator']);
        $usageSummary = $tenant->getUsageSummary();
        $limitViolations = $tenant->getLimitViolations();
        
        return view('developer.tenants.show', compact('tenant', 'usageSummary', 'limitViolations'));
    }

    public function edit(TenantSubscription $tenant)
    {
        $plans = TenantPlan::active()->ordered()->get();
        return view('developer.tenants.edit', compact('tenant', 'plans'));
    }

    public function update(Request $request, TenantSubscription $tenant)
    {
        $request->validate([
            'tenant_name' => 'required|string|max:150',
            'tenant_domain' => 'required|string|max:100|alpha_dash|unique:tenant_subscriptions,tenant_domain,' . $tenant->id,
            'contact_name' => 'required|string|max:150',
            'contact_email' => 'required|email|max:150|unique:tenant_subscriptions,contact_email,' . $tenant->id,
            'contact_phone' => 'nullable|string|max:20',
            'tenant_plan_id' => 'required|exists:tenant_plans,id',
            'status' => 'required|in:trial,active,suspended,cancelled,expired',
            'billing_name' => 'nullable|string|max:150',
            'billing_address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        try {
            $oldPlan = $tenant->tenant_plan_id;
            $tenant->update($request->only([
                'tenant_name', 'tenant_domain', 'contact_name', 'contact_email', 
                'contact_phone', 'tenant_plan_id', 'status', 'billing_name', 
                'billing_address', 'tax_id', 'notes'
            ]));

            // Log plan change
            if ($oldPlan != $request->tenant_plan_id) {
                $oldPlanName = TenantPlan::find($oldPlan)->name ?? 'unknown';
                $newPlanName = TenantPlan::find($request->tenant_plan_id)->name;
                
                Log::info('Tenant plan changed', [
                    'tenant_name' => $tenant->tenant_name,
                    'old_plan' => $oldPlanName,
                    'new_plan' => $newPlanName,
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

    public function suspend(Request $request, TenantSubscription $tenant)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $tenant->suspend($request->reason, auth()->user());

            Log::info('Tenant subscription suspended', [
                'tenant_name' => $tenant->tenant_name,
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

    public function activate(TenantSubscription $tenant)
    {
        try {
            $tenant->activate();

            Log::info('Tenant subscription activated', [
                'tenant_name' => $tenant->tenant_name,
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

    public function extendTrial(Request $request, TenantSubscription $tenant)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:90'
        ]);

        try {
            if ($tenant->extendTrial($request->days)) {
                Log::info('Trial extended', [
                    'tenant_name' => $tenant->tenant_name,
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
                    'message' => 'Solo se puede extender el período de prueba para suscripciones en estado trial'
                ], 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al extender el período de prueba: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(TenantSubscription $tenant)
    {
        try {
            $tenantName = $tenant->tenant_name;
            
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

    public function refreshUsage(TenantSubscription $tenant)
    {
        try {
            // Here you would implement the actual usage calculation
            // For now, we'll just update the timestamp
            $tenant->updateUsageStats();

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas de uso actualizadas',
                'last_update' => $tenant->last_usage_update->format('d/m/Y H:i')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}