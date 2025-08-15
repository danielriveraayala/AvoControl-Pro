<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlanManagementController extends Controller
{
    private PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    /**
     * Display a listing of the plans
     */
    public function index()
    {
        $plans = SubscriptionPlan::ordered()->get();
        
        return view('developer.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new plan
     */
    public function create()
    {
        $availableFeatures = SubscriptionPlan::getAvailableFeatures();
        
        return view('developer.plans.create', compact('availableFeatures'));
    }

    /**
     * Store a newly created plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:50|unique:subscription_plans,key',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'annual_price' => 'nullable|numeric|min:0',
            'annual_discount_percentage' => 'nullable|integer|min:0|max:100',
            'currency' => 'required|string|in:USD',
            'billing_cycle' => 'required|in:monthly,yearly',
            'trial_days' => 'required|integer|min:0',
            'max_users' => 'nullable|integer|min:-1',
            'max_lots_per_month' => 'nullable|integer|min:-1',
            'max_storage_gb' => 'nullable|integer|min:-1',
            'max_locations' => 'required|integer|min:-1',
            'features' => 'array',
            'features.*' => 'string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_custom' => 'boolean',
            'show_on_landing' => 'boolean',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
            'sort_order' => 'required|integer|min:0',
            'button_text' => 'nullable|string|max:100',
            'popular_badge' => 'nullable|string|max:50',
        ]);

        try {
            // Clean and format key
            $validated['key'] = Str::slug($validated['key'], '_');
            
            // Handle null values for limits (-1 means unlimited)
            if (empty($validated['max_users'])) $validated['max_users'] = null;
            if (empty($validated['max_lots_per_month'])) $validated['max_lots_per_month'] = null;
            if (empty($validated['max_storage_gb'])) $validated['max_storage_gb'] = null;
            
            // Handle annual pricing fields
            if (empty($validated['annual_price'])) $validated['annual_price'] = null;
            if (empty($validated['annual_discount_percentage'])) $validated['annual_discount_percentage'] = null;
            if (empty($validated['button_text'])) $validated['button_text'] = null;
            if (empty($validated['popular_badge'])) $validated['popular_badge'] = null;
            
            // Handle boolean fields (checkboxes that aren't sent when unchecked)
            $validated['is_active'] = $request->has('is_active');
            $validated['is_featured'] = $request->has('is_featured');
            $validated['is_custom'] = $request->has('is_custom');
            $validated['show_on_landing'] = $request->has('show_on_landing');
            
            // Create the plan
            $plan = SubscriptionPlan::create($validated);

            Log::info('Subscription plan created', [
                'plan_id' => $plan->id,
                'plan_key' => $plan->key,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('developer.plans.index')
                ->with('success', 'Plan creado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error creating subscription plan', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el plan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified plan
     */
    public function show(SubscriptionPlan $plan)
    {
        $plan->load('subscriptions');
        $availableFeatures = SubscriptionPlan::getAvailableFeatures();
        
        return view('developer.plans.show', compact('plan', 'availableFeatures'));
    }

    /**
     * Show the form for editing the plan
     */
    public function edit(SubscriptionPlan $plan)
    {
        $availableFeatures = SubscriptionPlan::getAvailableFeatures();
        
        return view('developer.plans.edit', compact('plan', 'availableFeatures'));
    }

    /**
     * Update the specified plan
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:50|unique:subscription_plans,key,' . $plan->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'annual_price' => 'nullable|numeric|min:0',
            'annual_discount_percentage' => 'nullable|integer|min:0|max:100',
            'currency' => 'required|string|in:USD',
            'billing_cycle' => 'required|in:monthly,yearly',
            'trial_days' => 'required|integer|min:0',
            'max_users' => 'nullable|integer|min:-1',
            'max_lots_per_month' => 'nullable|integer|min:-1',
            'max_storage_gb' => 'nullable|integer|min:-1',
            'max_locations' => 'required|integer|min:-1',
            'features' => 'array',
            'features.*' => 'string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_custom' => 'boolean',
            'show_on_landing' => 'boolean',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:50',
            'sort_order' => 'required|integer|min:0',
            'button_text' => 'nullable|string|max:100',
            'popular_badge' => 'nullable|string|max:50',
        ]);

        try {
            // Clean and format key
            $validated['key'] = Str::slug($validated['key'], '_');
            
            // Handle null values for limits
            if (empty($validated['max_users'])) $validated['max_users'] = null;
            if (empty($validated['max_lots_per_month'])) $validated['max_lots_per_month'] = null;
            if (empty($validated['max_storage_gb'])) $validated['max_storage_gb'] = null;
            
            // Handle annual pricing fields
            if (empty($validated['annual_price'])) $validated['annual_price'] = null;
            if (empty($validated['annual_discount_percentage'])) $validated['annual_discount_percentage'] = null;
            if (empty($validated['button_text'])) $validated['button_text'] = null;
            if (empty($validated['popular_badge'])) $validated['popular_badge'] = null;
            
            // Handle boolean fields (checkboxes that aren't sent when unchecked)
            $validated['is_active'] = $request->has('is_active');
            $validated['is_featured'] = $request->has('is_featured');
            $validated['is_custom'] = $request->has('is_custom');
            $validated['show_on_landing'] = $request->has('show_on_landing');
            
            // Update the plan
            $plan->update($validated);

            Log::info('Subscription plan updated', [
                'plan_id' => $plan->id,
                'plan_key' => $plan->key,
                'updated_by' => auth()->id()
            ]);

            return redirect()->route('developer.plans.index')
                ->with('success', 'Plan actualizado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error updating subscription plan', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el plan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified plan
     */
    public function destroy(SubscriptionPlan $plan)
    {
        try {
            if (!$plan->canBeDeleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este plan no puede ser eliminado porque tiene suscripciones activas o es un plan del sistema.'
                ], 422);
            }

            $planName = $plan->name;
            $plan->delete();

            Log::info('Subscription plan deleted', [
                'plan_id' => $plan->id,
                'plan_key' => $plan->key,
                'plan_name' => $planName,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Plan '{$planName}' eliminado exitosamente."
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting subscription plan', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync plan with PayPal (double sync for annual pricing)
     */
    public function syncWithPayPal(SubscriptionPlan $plan)
    {
        try {
            if ($plan->price == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los planes gratuitos no se sincronizan con PayPal.'
                ]);
            }

            $updateData = [];
            $messages = [];
            $errors = [];

            // 1. Sync Monthly Plan
            $monthlyResult = $this->paypalService->createSubscriptionPlan($plan, 'monthly');
            
            if ($monthlyResult['success']) {
                $updateData['paypal_plan_id'] = $monthlyResult['plan_id'];
                $messages[] = 'Plan mensual sincronizado exitosamente.';
                
                Log::info('Monthly PayPal plan synchronized', [
                    'plan_id' => $plan->id,
                    'paypal_plan_id' => $monthlyResult['plan_id']
                ]);
            } else {
                $errors[] = 'Error en plan mensual: ' . $monthlyResult['error'];
                Log::error('Failed to sync monthly PayPal plan', [
                    'plan_id' => $plan->id,
                    'error' => $monthlyResult['error']
                ]);
            }

            // 2. Sync Annual Plan (if annual pricing exists)
            if ($plan->annual_price && $plan->annual_price > 0) {
                $annualResult = $this->paypalService->createSubscriptionPlan($plan, 'yearly');
                
                if ($annualResult['success']) {
                    $updateData['paypal_annual_plan_id'] = $annualResult['plan_id'];
                    $messages[] = 'Plan anual sincronizado exitosamente.';
                    
                    Log::info('Annual PayPal plan synchronized', [
                        'plan_id' => $plan->id,
                        'paypal_annual_plan_id' => $annualResult['plan_id']
                    ]);
                } else {
                    $errors[] = 'Error en plan anual: ' . $annualResult['error'];
                    Log::error('Failed to sync annual PayPal plan', [
                        'plan_id' => $plan->id,
                        'error' => $annualResult['error']
                    ]);
                }
            }

            // Update plan with successful PayPal IDs
            if (!empty($updateData)) {
                $plan->update($updateData);
            }

            // Determine response
            if (!empty($messages) && empty($errors)) {
                // All successful
                return response()->json([
                    'success' => true,
                    'message' => implode(' ', $messages),
                    'paypal_plan_id' => $updateData['paypal_plan_id'] ?? null,
                    'paypal_annual_plan_id' => $updateData['paypal_annual_plan_id'] ?? null
                ]);
            } elseif (!empty($messages) && !empty($errors)) {
                // Partial success
                return response()->json([
                    'success' => true,
                    'message' => implode(' ', $messages) . ' Advertencias: ' . implode(' ', $errors),
                    'paypal_plan_id' => $updateData['paypal_plan_id'] ?? null,
                    'paypal_annual_plan_id' => $updateData['paypal_annual_plan_id'] ?? null,
                    'warnings' => $errors
                ]);
            } else {
                // All failed
                return response()->json([
                    'success' => false,
                    'message' => 'Error al sincronizar con PayPal: ' . implode(' ', $errors)
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Exception in syncWithPayPal', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar con PayPal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle plan status
     */
    public function toggleStatus(SubscriptionPlan $plan)
    {
        try {
            $plan->update(['is_active' => !$plan->is_active]);
            
            $status = $plan->is_active ? 'activado' : 'desactivado';
            
            return response()->json([
                'success' => true,
                'message' => "Plan {$status} exitosamente.",
                'is_active' => $plan->is_active
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsync plan from PayPal
     */
    public function unsyncFromPayPal(SubscriptionPlan $plan)
    {
        try {
            if (!$plan->paypal_plan_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este plan no está sincronizado con PayPal.'
                ]);
            }

            // Optional: Try to deactivate the plan in PayPal
            try {
                $this->paypalService->deactivateSubscriptionPlan($plan->paypal_plan_id);
            } catch (\Exception $e) {
                Log::warning('Could not deactivate PayPal plan during unsync', [
                    'plan_id' => $plan->id,
                    'paypal_plan_id' => $plan->paypal_plan_id,
                    'error' => $e->getMessage()
                ]);
            }

            // Remove PayPal ID from our database
            $plan->update(['paypal_plan_id' => null]);
            
            return response()->json([
                'success' => true,
                'message' => 'Plan desincronizado de PayPal exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desincronizar de PayPal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a plan
     */
    public function duplicate(SubscriptionPlan $plan)
    {
        try {
            $newPlan = $plan->replicate();
            $newPlan->key = $plan->key . '_copy_' . time();
            $newPlan->name = $plan->name . ' (Copia)';
            $newPlan->paypal_plan_id = null;
            $newPlan->stripe_plan_id = null;
            $newPlan->is_active = false;
            $newPlan->is_featured = false;
            $newPlan->sort_order = $plan->sort_order + 1;
            $newPlan->save();

            return response()->json([
                'success' => true,
                'message' => 'Plan duplicado exitosamente.',
                'redirect' => route('developer.plans.edit', $newPlan)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar el plan: ' . $e->getMessage()
            ], 500);
        }
    }
}
