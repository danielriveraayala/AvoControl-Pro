<?php

namespace App\Http\Controllers;

use App\Models\QualityGrade;
use App\Models\Setting;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $qualityGrades = QualityGrade::ordered()->get();
            
            if ($request->ajax()) {
                $html = view('configuration.partials.quality_table', compact('qualityGrades'))->render();
                return response()->json([
                    'html' => $html,
                    'count' => $qualityGrades->count(),
                    'success' => true
                ]);
            }
            
            return view('configuration.index', compact('qualityGrades'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar las calidades: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error al cargar la configuración');
        }
    }

    public function storeQuality(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight_min' => 'nullable|integer|min:1',
            'weight_max' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/i',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Set default sort_order if not provided
        if (!isset($validated['sort_order']) || $validated['sort_order'] === null || $validated['sort_order'] === '') {
            $validated['sort_order'] = QualityGrade::max('sort_order') + 1;
        }
        
        // Normalize color to lowercase
        if (isset($validated['color'])) {
            $validated['color'] = strtolower($validated['color']);
        }

        QualityGrade::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Calidad creada exitosamente']);
        }

        return redirect()->back()->with('success', 'Calidad creada exitosamente');
    }

    public function updateQuality(Request $request, QualityGrade $qualityGrade)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'weight_min' => 'nullable|integer|min:1',
            'weight_max' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:500',
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/i',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'boolean'
        ]);

        // Set default sort_order if not provided
        if (!isset($validated['sort_order']) || $validated['sort_order'] === null || $validated['sort_order'] === '') {
            $validated['sort_order'] = $qualityGrade->sort_order ?? 0;
        }
        
        // Normalize color to lowercase
        if (isset($validated['color'])) {
            $validated['color'] = strtolower($validated['color']);
        }

        $qualityGrade->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Calidad actualizada exitosamente']);
        }

        return redirect()->back()->with('success', 'Calidad actualizada exitosamente');
    }

    public function destroyQuality(Request $request, QualityGrade $qualityGrade)
    {
        try {
            // Check if quality grade is being used in lots
            $lotsCount = $qualityGrade->lots()->count();
            
            if ($lotsCount > 0) {
                $message = "No se puede eliminar la calidad '{$qualityGrade->name}' porque está siendo utilizada en {$lotsCount} lote(s). Primero debes reasignar o eliminar los lotes que usan esta calidad.";
                
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => $message,
                        'lots_count' => $lotsCount
                    ], 400);
                }

                return redirect()->back()->with('error', $message);
            }

            // Check if quality grade is being used in sale items
            $saleItemsCount = \DB::table('sale_items')
                ->where('quality_grade', $qualityGrade->name)
                ->count();
                
            if ($saleItemsCount > 0) {
                $message = "No se puede eliminar la calidad '{$qualityGrade->name}' porque está siendo utilizada en {$saleItemsCount} venta(s). Esta calidad tiene historial de transacciones.";
                
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => $message,
                        'sale_items_count' => $saleItemsCount
                    ], 400);
                }

                return redirect()->back()->with('error', $message);
            }

            // Safe to delete
            $qualityGrade->delete();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => "Calidad '{$qualityGrade->name}' eliminada exitosamente"
                ]);
            }

            return redirect()->back()->with('success', "Calidad '{$qualityGrade->name}' eliminada exitosamente");

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle foreign key constraint errors
            if ($e->getCode() === '23000') {
                $message = "No se puede eliminar la calidad '{$qualityGrade->name}' porque está siendo utilizada en otros registros del sistema. Verifica que no esté asignada a lotes o ventas.";
                
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => $message,
                        'error_code' => 'FOREIGN_KEY_CONSTRAINT'
                    ], 400);
                }

                return redirect()->back()->with('error', $message);
            }

            // Other database errors
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Error al eliminar la calidad. Intenta nuevamente.',
                    'error' => config('app.debug') ? $e->getMessage() : 'Database error'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al eliminar la calidad. Intenta nuevamente.');
        }
    }

    public function showQuality(Request $request, QualityGrade $qualityGrade)
    {
        if ($request->wantsJson()) {
            return response()->json($qualityGrade);
        }

        return view('configuration.quality.show', compact('qualityGrade'));
    }

    public function getQualitiesTable(Request $request)
    {
        if ($request->ajax() && $request->has('ajax')) {
            // Handle DataTables request
            $query = QualityGrade::query();
            
            // Search functionality for DataTables
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            }

            // Ordering for DataTables
            if ($request->has('order')) {
                $columns = ['sort_order', 'name', 'color', 'weight_min', 'active'];
                $orderColumn = $columns[$request->order[0]['column']] ?? 'sort_order';
                $orderDirection = $request->order[0]['dir'] ?? 'asc';
                $query->orderBy($orderColumn, $orderDirection);
            } else {
                $query->orderBy('sort_order', 'asc');
            }

            // Pagination for DataTables
            $totalRecords = $query->count();
            $qualities = $query->skip($request->start ?? 0)
                             ->take($request->length ?? 10)
                             ->get();

            // Format data for DataTables
            $data = $qualities->map(function($quality) {
                return [
                    'sort_order' => '<span class="badge badge-secondary">'.$quality->sort_order.'</span>',
                    'name' => '<strong>'.$quality->name.'</strong>',
                    'color' => '<div class="d-flex align-items-center">
                                    <div class="color-preview me-2" style="width: 20px; height: 20px; background-color: '.$quality->color.'; border-radius: 4px; border: 1px solid #ccc;"></div>
                                    <small class="text-muted">'.$quality->color.'</small>
                                </div>',
                    'weight' => $quality->weight_min && $quality->weight_max 
                               ? $quality->weight_min . 'g - ' . $quality->weight_max . 'g'
                               : ($quality->weight_min ? $quality->weight_min . 'g+' : ($quality->weight_max ? $quality->weight_max . 'g-' : 'Sin especificar')),
                    'status' => '<span class="badge badge-'.($quality->active ? 'success' : 'secondary').'">'.($quality->active ? 'Activo' : 'Inactivo').'</span>',
                    'actions' => '<div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info" onclick="editQuality('.$quality->id.')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteQuality('.$quality->id.', \''.addslashes($quality->name).'\')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                  </div>'
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => QualityGrade::count(),
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);
        }
        
        try {
            $qualityGrades = QualityGrade::ordered()->get();
            $html = view('configuration.partials.quality_table', compact('qualityGrades'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => $qualityGrades->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las calidades: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCompanyConfig(Request $request)
    {
        try {
            // Get company configuration from settings table
            $config = [
                'company_name' => Setting::get('company_name', 'AvoControl-Pro', 'company'),
                'company_rfc' => Setting::get('company_rfc', '', 'company'),
                'company_address' => Setting::get('company_address', '', 'company'),
                'company_city' => Setting::get('company_city', 'Uruapan', 'company'),
                'company_state' => Setting::get('company_state', 'Michoacán', 'company'),
                'company_postal_code' => Setting::get('company_postal_code', '', 'company'),
                'company_phone' => Setting::get('company_phone', '', 'company'),
                'company_email' => Setting::get('company_email', '', 'company'),
                'company_website' => Setting::get('company_website', '', 'company'),
                'company_logo' => Setting::get('company_logo', '', 'company'),
                'company_description' => Setting::get('company_description', '', 'company'),
            ];

            return response()->json([
                'success' => true,
                'config' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar la configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeCompanyConfig(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_rfc' => 'nullable|string|max:13',
            'company_address' => 'nullable|string|max:500',
            'company_city' => 'nullable|string|max:255',
            'company_state' => 'nullable|string|max:255',
            'company_postal_code' => 'nullable|string|max:10',
            'company_phone' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'company_logo' => 'nullable|url|max:255',
            'company_description' => 'nullable|string|max:1000',
        ]);

        try {
            // Save each setting to the database
            foreach ($validated as $key => $value) {
                Setting::set($key, $value, 'company', $this->getCompanyFieldDescription($key));
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuración de empresa guardada correctamente'
                ]);
            }

            return redirect()->back()->with('success', 'Configuración de empresa guardada correctamente');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar la configuración: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al guardar la configuración');
        }
    }

    /**
     * Get description for company configuration fields
     */
    private function getCompanyFieldDescription($field)
    {
        $descriptions = [
            'company_name' => 'Nombre de la empresa',
            'company_rfc' => 'RFC de la empresa',
            'company_address' => 'Dirección de la empresa',
            'company_city' => 'Ciudad donde se ubica la empresa',
            'company_state' => 'Estado donde se ubica la empresa',
            'company_postal_code' => 'Código postal de la empresa',
            'company_phone' => 'Teléfono de contacto de la empresa',
            'company_email' => 'Email de contacto de la empresa',
            'company_website' => 'Sitio web de la empresa',
            'company_logo' => 'URL del logo de la empresa',
            'company_description' => 'Descripción de la empresa para reportes'
        ];

        return $descriptions[$field] ?? '';
    }

    /**
     * Get current subscription information for billing tab
     */
    public function getCurrentSubscription()
    {
        try {
            $user = auth()->user();
            
            \Log::info('Getting subscription for user', [
                'user_id' => $user ? $user->id : null,
                'current_tenant_id' => $user ? $user->current_tenant_id : null
            ]);
            
            if (!$user->current_tenant_id) {
                \Log::info('No current tenant ID for user');
                return response()->json(['subscription' => null]);
            }

            // Get current tenant's subscription
            $subscription = \App\Models\Subscription::with(['subscriptionPlan', 'tenant'])
                ->where('tenant_id', $user->current_tenant_id)
                ->where('status', 'active')
                ->first();
                
            \Log::info('Subscription query result', [
                'tenant_id' => $user->current_tenant_id,
                'subscription_found' => $subscription ? $subscription->id : null
            ]);

            if (!$subscription) {
                \Log::info('No subscription found for tenant', ['tenant_id' => $user->current_tenant_id]);
                return response()->json(['subscription' => null]);
            }
            
            \Log::info('Subscription found', ['subscription_id' => $subscription->id, 'plan' => $subscription->plan]);

            // Get plan details using the relationship or fallback query
            $plan = $subscription->subscriptionPlan ?? \App\Models\SubscriptionPlan::where('key', $subscription->plan)->first();

            return response()->json([
                'subscription' => [
                    'id' => $subscription->id,
                    'plan' => $subscription->plan,
                    'plan_name' => $plan ? $plan->name : ucfirst($subscription->plan),
                    'status' => $subscription->status,
                    'amount' => $subscription->amount,
                    'currency' => $subscription->currency,
                    'billing_cycle' => $subscription->billing_cycle,
                    'next_billing_date' => $subscription->next_billing_date,
                    'paypal_subscription_id' => $subscription->paypal_subscription_id,
                    'is_trial' => $subscription->is_trial,
                    'trial_days' => $subscription->trial_days,
                    'starts_at' => $subscription->starts_at,
                    'created_at' => $subscription->created_at
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting current subscription: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener la suscripción'], 500);
        }
    }

    /**
     * Get subscription invoices from PayPal
     */
    public function getSubscriptionInvoices()
    {
        try {
            $user = auth()->user();
            
            if (!$user->current_tenant_id) {
                return response()->json(['invoices' => []]);
            }

            $subscription = \App\Models\Subscription::where('tenant_id', $user->current_tenant_id)
                ->where('status', 'active')
                ->first();

            if (!$subscription || !$subscription->paypal_subscription_id) {
                return response()->json(['invoices' => []]);
            }

            // Get invoices from PayPal API or database
            $invoices = $this->getPayPalInvoices($subscription->paypal_subscription_id);

            return response()->json(['invoices' => $invoices]);

        } catch (\Exception $e) {
            \Log::error('Error getting subscription invoices: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener las facturas'], 500);
        }
    }

    /**
     * Download specific invoice
     */
    public function downloadInvoice($invoiceId)
    {
        try {
            $user = auth()->user();
            $subscription = \App\Models\Subscription::where('tenant_id', $user->current_tenant_id)
                ->where('status', 'active')
                ->first();

            if (!$subscription) {
                abort(404, 'Suscripción no encontrada');
            }

            // Get PayPal configuration based on environment
            $environment = env('PAYPAL_ENVIRONMENT', 'sandbox');
            $clientId = env('PAYPAL_' . strtoupper($environment) . '_CLIENT_ID');
            $clientSecret = env('PAYPAL_' . strtoupper($environment) . '_CLIENT_SECRET');
            
            if (!$clientId || !$clientSecret) {
                \Log::error('PayPal credentials not configured for environment: ' . $environment);
                return response()->json(['error' => 'Configuración de PayPal incompleta'], 500);
            }

            // Get access token from PayPal
            $accessToken = $this->getPayPalAccessToken($environment, $clientId, $clientSecret);
            
            if (!$accessToken) {
                return response()->json(['error' => 'No se pudo obtener token de PayPal'], 500);
            }

            // Get invoice details from PayPal
            $invoiceDetails = $this->getPayPalInvoiceDetails($invoiceId, $accessToken, $environment);
            
            if (!$invoiceDetails) {
                return response()->json(['error' => 'Factura no encontrada en PayPal'], 404);
            }

            // Generate PDF from invoice details
            $pdfContent = $this->generateInvoicePDF($invoiceDetails, $subscription);
            
            // Return PDF file for download
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="Factura_' . $invoiceId . '.pdf"')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache');

        } catch (\Exception $e) {
            \Log::error('Error downloading invoice: ' . $e->getMessage());
            return response()->json(['error' => 'Error al descargar la factura: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get available plans for plan management
     */
    public function getAvailablePlans()
    {
        try {
            $user = auth()->user();
            
            if (!$user->current_tenant_id) {
                return response()->json(['error' => 'No hay tenant asociado'], 400);
            }

            $subscription = \App\Models\Subscription::with('subscriptionPlan')
                ->where('tenant_id', $user->current_tenant_id)
                ->where('status', 'active')
                ->first();

            if (!$subscription) {
                return response()->json(['error' => 'No hay suscripción activa'], 400);
            }

            // Get current plan details
            $currentPlan = $subscription->subscriptionPlan ?? \App\Models\SubscriptionPlan::where('key', $subscription->plan)->first();
            $currentPrice = $subscription->amount;
            
            // Get all available plans excluding:
            // - Free plans (price = 0)
            // - Corporate/custom plans
            // - Current plan
            $availablePlans = \App\Models\SubscriptionPlan::where('is_active', true)
                ->where('price', '>', 0) // Exclude free plans
                ->where('key', '!=', 'corporate')
                ->where('key', '!=', $subscription->plan) // Exclude current plan
                ->where('is_custom', false)
                ->orderBy('price', 'asc')
                ->get();

            // Separate into upgrades and downgrades based on price
            $upgrades = [];
            $downgrades = [];
            
            foreach ($availablePlans as $plan) {
                $planData = [
                    'key' => $plan->key,
                    'name' => $plan->name,
                    'price' => $plan->price,
                    'annual_price' => $plan->annual_price,
                    'billing_cycle' => 'monthly',
                    'features' => $plan->features ?? []
                ];
                
                if ($plan->price > $currentPrice) {
                    $upgrades[] = $planData;
                } else if ($plan->price < $currentPrice) {
                    $downgrades[] = $planData;
                }
            }

            return response()->json([
                'current_plan' => [
                    'key' => $subscription->plan,
                    'name' => $currentPlan ? $currentPlan->name : ucfirst($subscription->plan),
                    'price' => $subscription->amount,
                    'billing_cycle' => $subscription->billing_cycle
                ],
                'upgrades' => $upgrades,
                'downgrades' => $downgrades,
                'available_plans' => $availablePlans->map(function($plan) use ($currentPrice) {
                    return [
                        'key' => $plan->key,
                        'name' => $plan->name,
                        'price' => $plan->price,
                        'annual_price' => $plan->annual_price,
                        'billing_cycle' => 'monthly',
                        'features' => $plan->features ?? [],
                        'change_type' => $plan->price > $currentPrice ? 'upgrade' : 'downgrade'
                    ];
                })
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting available plans: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener los planes'], 500);
        }
    }

    /**
     * Change subscription plan with prorated billing
     */
    public function changePlan(\Illuminate\Http\Request $request)
    {
        try {
            $request->validate([
                'new_plan' => 'required|string',
                'change_type' => 'required|in:upgrade,downgrade'
            ]);

            $user = auth()->user();
            
            if (!$user->current_tenant_id) {
                return response()->json(['error' => 'No hay tenant asociado'], 400);
            }

            $subscription = \App\Models\Subscription::where('tenant_id', $user->current_tenant_id)
                ->where('status', 'active')
                ->first();

            if (!$subscription) {
                return response()->json(['error' => 'No hay suscripción activa'], 400);
            }

            $newPlan = \App\Models\SubscriptionPlan::where('key', $request->new_plan)->first();
            
            if (!$newPlan) {
                return response()->json(['error' => 'Plan no encontrado'], 400);
            }

            // Calculate prorated amount
            $proratedAmount = $this->calculateProratedAmount($subscription, $newPlan);

            // Update subscription in database
            $subscription->update([
                'plan' => $newPlan->key,
                'amount' => $newPlan->price,
                'updated_at' => now()
            ]);

            // Here you would integrate with PayPal API to change the subscription
            // For now, we'll log the change
            \Log::info('Plan change requested', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'old_plan' => $subscription->plan,
                'new_plan' => $newPlan->key,
                'prorated_amount' => $proratedAmount,
                'change_type' => $request->change_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Plan cambiado exitosamente',
                'prorated_amount' => $proratedAmount,
                'new_plan' => $newPlan->name
            ]);

        } catch (\Exception $e) {
            \Log::error('Error changing plan: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cambiar el plan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Calculate prorated amount for plan change
     */
    private function calculateProratedAmount($subscription, $newPlan)
    {
        // Get days remaining in current billing cycle
        $nextBillingDate = $subscription->next_billing_date;
        $now = now();
        
        if (!$nextBillingDate || $nextBillingDate->isPast()) {
            return $newPlan->price; // Full amount if no next billing date
        }
        
        $daysRemaining = $now->diffInDays($nextBillingDate);
        $totalDaysInCycle = $subscription->billing_cycle === 'yearly' ? 365 : 30;
        
        // Calculate unused amount from current plan
        $unusedAmount = ($subscription->amount / $totalDaysInCycle) * $daysRemaining;
        
        // Calculate new plan amount for remaining days
        $newPlanDailyRate = $newPlan->price / $totalDaysInCycle;
        $newPlanAmount = $newPlanDailyRate * $daysRemaining;
        
        // Prorated amount = new plan amount - unused current plan amount
        $proratedAmount = $newPlanAmount - $unusedAmount;
        
        return round(max(0, $proratedAmount), 2); // Ensure non-negative
    }

    /**
     * Get PayPal invoices (enhanced with real PayPal API data)
     */
    private function getPayPalInvoices($subscriptionId)
    {
        try {
            $subscription = \App\Models\Subscription::where('paypal_subscription_id', $subscriptionId)->first();
            
            if (!$subscription) {
                return [];
            }

            // Get PayPal environment configuration
            $environment = env('PAYPAL_ENVIRONMENT', 'sandbox');
            $clientId = env('PAYPAL_' . strtoupper($environment) . '_CLIENT_ID');
            $clientSecret = env('PAYPAL_' . strtoupper($environment) . '_CLIENT_SECRET');
            
            if (!$clientId || !$clientSecret) {
                \Log::warning('PayPal credentials not configured, using fallback data');
                return $this->getFallbackInvoices($subscription);
            }

            // Get access token
            $accessToken = $this->getPayPalAccessToken($environment, $clientId, $clientSecret);
            
            if (!$accessToken) {
                \Log::warning('Could not get PayPal access token, using fallback data');
                return $this->getFallbackInvoices($subscription);
            }

            // Get subscription transactions from PayPal
            $invoices = $this->getPayPalSubscriptionTransactions($subscriptionId, $accessToken, $environment);
            
            if (empty($invoices)) {
                \Log::info('No PayPal transactions found, using fallback data');
                return $this->getFallbackInvoices($subscription);
            }

            return $invoices;

        } catch (\Exception $e) {
            \Log::error('Error getting PayPal invoices: ' . $e->getMessage());
            return $this->getFallbackInvoices($subscription);
        }
    }

    /**
     * Get PayPal subscription transactions
     */
    private function getPayPalSubscriptionTransactions($subscriptionId, $accessToken, $environment)
    {
        try {
            $baseUrl = $environment === 'live' 
                ? 'https://api-m.paypal.com' 
                : 'https://api-m.sandbox.paypal.com';

            $startTime = now()->subYear()->toISOString();
            $endTime = now()->toISOString();

            $response = \Http::withToken($accessToken)
                ->get($baseUrl . '/v1/billing/subscriptions/' . $subscriptionId . '/transactions', [
                    'start_time' => $startTime,
                    'end_time' => $endTime
                ]);

            if (!$response->successful()) {
                \Log::warning('PayPal transactions API failed: ' . $response->body());
                return [];
            }

            $data = $response->json();
            $invoices = [];

            if (isset($data['transactions']) && is_array($data['transactions'])) {
                foreach ($data['transactions'] as $transaction) {
                    $invoices[] = [
                        'id' => $transaction['id'] ?? 'N/A',
                        'invoice_number' => $transaction['id'] ?? 'N/A',
                        'create_time' => $transaction['time'] ?? now()->toISOString(),
                        'total_amount' => [
                            'value' => $transaction['amount_with_breakdown']['gross_amount']['value'] ?? '0.00',
                            'currency_code' => $transaction['amount_with_breakdown']['gross_amount']['currency_code'] ?? 'USD'
                        ],
                        'status' => $transaction['status'] ?? 'COMPLETED',
                        'payment_method' => 'PayPal',
                        'description' => 'Pago de suscripción - ' . ($transaction['id'] ?? 'PayPal')
                    ];
                }
            }

            \Log::info('Retrieved ' . count($invoices) . ' PayPal transactions for subscription: ' . $subscriptionId);
            return $invoices;

        } catch (\Exception $e) {
            \Log::error('Error getting PayPal subscription transactions: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get fallback invoices when PayPal API is not available
     */
    private function getFallbackInvoices($subscription)
    {
        // Check local payment history first
        $payments = \App\Models\SubscriptionPayment::where('subscription_id', $subscription->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $invoices = [];
        
        foreach ($payments as $payment) {
            $invoices[] = [
                'id' => $payment->paypal_payment_id ?? ('INV-' . strtoupper(substr($payment->id, -8))),
                'invoice_number' => 'INV-' . str_pad($payment->id, 3, '0', STR_PAD_LEFT),
                'create_time' => $payment->created_at->toISOString(),
                'total_amount' => [
                    'value' => number_format($payment->amount, 2),
                    'currency_code' => $payment->currency ?? 'USD'
                ],
                'status' => 'PAID',
                'payment_method' => 'PayPal',
                'description' => 'Pago de suscripción - Plan ' . ucfirst($subscription->plan)
            ];
        }

        // If still no payments, create mock invoice with real subscription data
        if (empty($invoices)) {
            $invoices[] = [
                'id' => $subscription->paypal_subscription_id ?? ('INV-' . strtoupper(substr($subscription->id, -8))),
                'invoice_number' => 'INV-' . str_pad($subscription->id, 3, '0', STR_PAD_LEFT),
                'create_time' => $subscription->created_at->toISOString(),
                'total_amount' => [
                    'value' => number_format($subscription->amount, 2),
                    'currency_code' => $subscription->currency ?? 'USD'
                ],
                'status' => 'PAID',
                'payment_method' => 'PayPal',
                'description' => 'Pago inicial de suscripción - Plan ' . ucfirst($subscription->plan)
            ];
        }

        return $invoices;
    }

    /**
     * Get PayPal access token
     */
    private function getPayPalAccessToken($environment, $clientId, $clientSecret)
    {
        try {
            $baseUrl = $environment === 'live' 
                ? 'https://api-m.paypal.com' 
                : 'https://api-m.sandbox.paypal.com';

            $response = \Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post($baseUrl . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials'
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            }

            \Log::error('PayPal token error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            \Log::error('PayPal token exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get invoice details from PayPal (enhanced for transaction IDs)
     */
    private function getPayPalInvoiceDetails($invoiceId, $accessToken, $environment)
    {
        try {
            $baseUrl = $environment === 'live' 
                ? 'https://api-m.paypal.com' 
                : 'https://api-m.sandbox.paypal.com';

            \Log::info('Looking for PayPal invoice/transaction: ' . $invoiceId);

            // First, try to get as a subscription transaction
            if (strpos($invoiceId, 'I-') === 0 || strlen($invoiceId) > 10) {
                // This looks like a subscription ID or transaction ID
                $user = auth()->user();
                $subscription = \App\Models\Subscription::where('tenant_id', $user->current_tenant_id)
                    ->where('status', 'active')
                    ->first();

                if ($subscription && $subscription->paypal_subscription_id) {
                    $response = \Http::withToken($accessToken)
                        ->get($baseUrl . '/v1/billing/subscriptions/' . $subscription->paypal_subscription_id . '/transactions', [
                            'start_time' => now()->subYear()->toISOString(),
                            'end_time' => now()->toISOString()
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        
                        if (isset($data['transactions']) && is_array($data['transactions'])) {
                            // Find the specific transaction by ID
                            foreach ($data['transactions'] as $transaction) {
                                if ($transaction['id'] === $invoiceId) {
                                    \Log::info('Found matching transaction: ' . $invoiceId);
                                    return $transaction;
                                }
                            }
                            
                            // If exact match not found, return the most recent transaction
                            if (!empty($data['transactions'])) {
                                \Log::info('Using most recent transaction as fallback');
                                return $data['transactions'][0];
                            }
                        }
                    }
                }
            }

            // Try to get invoice from Invoicing API (for standalone invoices)
            $response = \Http::withToken($accessToken)
                ->get($baseUrl . '/v2/invoicing/invoices/' . $invoiceId);

            if ($response->successful()) {
                \Log::info('Found invoice via invoicing API: ' . $invoiceId);
                return $response->json();
            }

            // Try payments API if it's a payment ID
            $response = \Http::withToken($accessToken)
                ->get($baseUrl . '/v2/payments/captures/' . $invoiceId);

            if ($response->successful()) {
                \Log::info('Found payment via captures API: ' . $invoiceId);
                return $response->json();
            }

            \Log::warning('PayPal invoice/transaction not found via any API: ' . $invoiceId);
            
            // Return mock data based on subscription info
            $user = auth()->user();
            $subscription = \App\Models\Subscription::where('tenant_id', $user->current_tenant_id)
                ->where('status', 'active')
                ->first();

            if ($subscription) {
                return [
                    'id' => $invoiceId,
                    'amount' => [
                        'total' => $subscription->amount,
                        'currency_code' => $subscription->currency ?? 'USD'
                    ],
                    'create_time' => $subscription->created_at->toISOString(),
                    'status' => 'PAID',
                    'mock_data' => true
                ];
            }

            return null;

        } catch (\Exception $e) {
            \Log::error('PayPal invoice API exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate PDF from invoice details
     */
    private function generateInvoicePDF($invoiceDetails, $subscription)
    {
        try {
            // Handle different PayPal API response structures
            $amount = null;
            $currency = 'USD';
            
            // Try different amount field structures
            if (isset($invoiceDetails['amount_with_breakdown']['gross_amount'])) {
                // Subscription transaction format
                $amount = $invoiceDetails['amount_with_breakdown']['gross_amount']['value'];
                $currency = $invoiceDetails['amount_with_breakdown']['gross_amount']['currency_code'];
            } elseif (isset($invoiceDetails['amount']['total'])) {
                // Invoice format
                $amount = $invoiceDetails['amount']['total'];
                $currency = $invoiceDetails['amount']['currency_code'];
            } elseif (isset($invoiceDetails['amount']['value'])) {
                // Payment capture format
                $amount = $invoiceDetails['amount']['value'];
                $currency = $invoiceDetails['amount']['currency_code'];
            } else {
                // Fallback to subscription amount
                $amount = $subscription->amount;
                $currency = $subscription->currency ?? 'USD';
            }

            // Handle different time field structures
            $createTime = null;
            if (isset($invoiceDetails['time'])) {
                $createTime = $invoiceDetails['time'];
            } elseif (isset($invoiceDetails['create_time'])) {
                $createTime = $invoiceDetails['create_time'];
            } elseif (isset($invoiceDetails['created_at'])) {
                $createTime = $invoiceDetails['created_at'];
            }

            // Prepare invoice data
            $invoiceData = [
                'invoice_id' => $invoiceDetails['id'] ?? 'N/A',
                'amount' => $amount,
                'currency' => $currency,
                'date' => $createTime 
                    ? \Carbon\Carbon::parse($createTime)->format('d/m/Y')
                    : now()->format('d/m/Y'),
                'subscription_id' => $subscription->paypal_subscription_id,
                'plan' => ucfirst($subscription->plan),
                'company_name' => 'AvoControl Pro',
                'customer_name' => auth()->user()->name,
                'customer_email' => auth()->user()->email,
                'status' => strtoupper($invoiceDetails['status'] ?? 'PAID'),
                'payment_method' => 'PayPal',
                'environment' => env('PAYPAL_ENVIRONMENT', 'sandbox'),
                'is_mock_data' => isset($invoiceDetails['mock_data'])
            ];

            \Log::info('Generating PDF for invoice', [
                'invoice_id' => $invoiceData['invoice_id'],
                'amount' => $invoiceData['amount'],
                'currency' => $invoiceData['currency'],
                'environment' => $invoiceData['environment'],
                'is_mock' => $invoiceData['is_mock_data']
            ]);

            // Generate PDF using DomPDF
            $html = view('invoices.paypal-invoice', compact('invoiceData'))->render();
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($html);
            $pdf->setPaper('legal', 'portrait'); // Tamaño oficio (legal size)
            
            return $pdf->output();

        } catch (\Exception $e) {
            \Log::error('PDF generation error: ' . $e->getMessage());
            
            // Fallback: Generate simple PDF with basic info
            $invoiceId = $invoiceDetails['id'] ?? 'N/A';
            $amount = $invoiceDetails['amount']['total'] ?? $invoiceDetails['amount']['value'] ?? $subscription->amount ?? '0.00';
            $currency = $invoiceDetails['amount']['currency_code'] ?? $subscription->currency ?? 'USD';
            
            $fallbackHtml = "
                <h1>Factura PayPal</h1>
                <p><strong>ID:</strong> " . $invoiceId . "</p>
                <p><strong>Monto:</strong> $" . number_format($amount, 2) . " " . $currency . "</p>
                <p><strong>Fecha:</strong> " . now()->format('d/m/Y') . "</p>
                <p><strong>Plan:</strong> " . ucfirst($subscription->plan) . "</p>
                <p><strong>Usuario:</strong> " . auth()->user()->name . "</p>
                <p><strong>Email:</strong> " . auth()->user()->email . "</p>
                <p><strong>Ambiente:</strong> " . strtoupper(env('PAYPAL_ENVIRONMENT', 'sandbox')) . "</p>
                <hr>
                <p><small>Esta es una factura generada automáticamente. En caso de problemas para descargar la factura completa, contacte con soporte.</small></p>
            ";
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($fallbackHtml);
            $pdf->setPaper('legal', 'portrait'); // Tamaño oficio (legal size)
            return $pdf->output();
        }
    }

}
