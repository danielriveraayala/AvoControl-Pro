<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\PayPalService;
use App\Mail\TrialWelcomeEmail;
use App\Mail\TrialReminderEmail;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    private PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    /**
     * Show subscription plans page
     */
    public function plans()
    {
        $plans = $this->paypalService->getSubscriptionPlans();
        
        return view('subscription.plans', compact('plans'));
    }

    /**
     * Process unified registration (User + Tenant + Trial)
     */
    public function registerWithTrial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'company_name' => 'required|string|max:255',
            'company_slug' => 'required|string|max:50|alpha_dash|unique:tenants,slug',
            'plan' => 'required|in:trial,basic,premium,enterprise',
            'terms' => 'required|accepted',
        ], [
            'company_slug.unique' => 'Este nombre de empresa ya está en uso. Por favor elige otro.',
            'company_slug.alpha_dash' => 'El nombre de empresa solo puede contener letras, números, guiones y guiones bajos.',
            'email.unique' => 'Este email ya está registrado.',
            'terms.accepted' => 'Debes aceptar los términos y condiciones.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // 1. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'email_verified_at' => Carbon::now(), // Auto-verify for trial users
            ]);

            // 2. Create Tenant
            $tenant = Tenant::create([
                'uuid' => Str::uuid(),
                'name' => $request->company_name,
                'slug' => $request->company_slug,
                'email' => $request->email,
                'plan' => $request->plan,
                'status' => 'active',
                'trial_ends_at' => Carbon::now()->addDays(7),
                'created_by' => $user->id,
            ]);

            // 3. Associate User with Tenant
            $user->update(['current_tenant_id' => $tenant->id]);

            // 4. Assign default role to user
            $user->assignRole('admin'); // Admin of their own tenant

            // 5. Create Trial Subscription
            $subscription = Subscription::create([
                'uuid' => Str::uuid(),
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'plan' => $request->plan,
                'amount' => $this->getPlanAmount($request->plan),
                'currency' => 'USD',
                'interval' => 'monthly',
                'status' => 'active', // Trial is immediately active
                'is_trial' => true,
                'trial_starts_at' => Carbon::now(),
                'trial_ends_at' => Carbon::now()->addDays(7),
                'starts_at' => Carbon::now(),
                'next_billing_date' => Carbon::now()->addDays(7), // Billing starts after trial
                'plan_features' => $this->getPlanFeatures($request->plan)
            ]);

            // 6. Create tenant settings with default configuration
            $tenant->settings()->createMany([
                ['key' => 'trial_welcome_sent', 'value' => 'false', 'type' => 'boolean'],
                ['key' => 'trial_reminder_sent', 'value' => 'false', 'type' => 'boolean'],
                ['key' => 'company_size', 'value' => $request->company_size ?? 'small', 'type' => 'string'],
                ['key' => 'industry', 'value' => $request->industry ?? 'agriculture', 'type' => 'string'],
                ['key' => 'timezone', 'value' => 'America/Mexico_City', 'type' => 'string'],
            ]);

            // 7. Send welcome email
            try {
                Mail::to($user->email)->send(new TrialWelcomeEmail($user, $tenant, $subscription));
                
                // Mark welcome email as sent
                $tenant->settings()->where('key', 'trial_welcome_sent')->update(['value' => 'true']);
            } catch (\Exception $e) {
                // Log email error but don't fail registration
                \Log::warning('Failed to send trial welcome email', [
                    'user_id' => $user->id,
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();

            // 8. Log the user in
            Auth::login($user);

            // 9. Redirect to onboarding or dashboard
            return redirect()
                ->route('dashboard')
                ->with('success', '¡Bienvenido a AvoControl Pro! Tu trial de 7 días ha comenzado.')
                ->with('trial_info', [
                    'days_remaining' => 7,
                    'plan' => $request->plan,
                    'trial_ends_at' => Carbon::now()->addDays(7)->format('d/m/Y')
                ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Failed to register user with trial', [
                'email' => $request->email,
                'company_name' => $request->company_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withErrors(['general' => 'Hubo un error al crear tu cuenta. Por favor intenta nuevamente.'])
                ->withInput();
        }
    }

    /**
     * Show registration form for a specific plan
     */
    public function showRegister($planKey)
    {
        // Get plan details
        $plan = SubscriptionPlan::where('key', $planKey)->where('is_active', true)->first();
        
        if (!$plan) {
            return redirect()->route('home')->with('error', 'Plan no encontrado.');
        }
        
        return view('subscription.register', compact('plan'));
    }
    
    /**
     * Store registration data and redirect to PayPal
     */
    public function storeRegister(Request $request)
    {
        try {
            \Log::info('Hybrid registration attempt', [
                'has_subscription_id' => $request->has('subscription_id'),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);
            
            // Check for existing pre-registration (pending cleanup)
            $existingUser = User::where('email', $request->email)->first();
            
            if ($existingUser && !$request->has('subscription_id')) {
                // If user exists and has pending_cleanup_at, they're in pre-registration
                if ($existingUser->pending_cleanup_at) {
                    $hoursRemaining = Carbon::parse($existingUser->pending_cleanup_at)->diffInHours(now());
                    
                    if ($hoursRemaining > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => "Ya existe un pre-registro con este email. Tienes {$hoursRemaining} horas para completar tu pago. Si necesitas ayuda, contacta al soporte.",
                            'type' => 'pre_registration_exists',
                            'hours_remaining' => $hoursRemaining
                        ], 409);
                    }
                    
                    // Pre-registration expired, delete it
                    $existingUser->tenant?->delete();
                    $existingUser->delete();
                    \Log::info('Deleted expired pre-registration', ['email' => $request->email]);
                } else {
                    // User exists and is fully registered
                    return response()->json([
                        'success' => false,
                        'message' => 'Este email ya está registrado en el sistema.',
                        'type' => 'email_exists'
                    ], 409);
                }
            }
            
            // Validate input data
            $emailRule = $existingUser && $request->has('subscription_id') ? 'required|string|email|max:255' : 'required|string|email|max:255|unique:users';
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => $emailRule,
                'password' => 'required|string|min:8|confirmed',
                'company_name' => 'required|string|max:255',
                'plan_key' => 'required|string|exists:subscription_plans,key',
                'billing_cycle' => 'required|in:monthly,yearly',
                'subscription_id' => 'nullable|string' // PayPal subscription ID (if coming from PayPal approval)
            ], [
                'name.required' => 'El nombre es requerido.',
                'email.required' => 'El email es requerido.',
                'email.email' => 'Ingresa un email válido.',
                'email.unique' => 'Este email ya está registrado.',
                'password.required' => 'La contraseña es requerida.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'company_name.required' => 'El nombre de la empresa es requerido.',
                'plan_key.required' => 'Debe seleccionar un plan.',
                'plan_key.exists' => 'El plan seleccionado no es válido.',
                'billing_cycle.required' => 'Debe seleccionar un ciclo de facturación.',
                'billing_cycle.in' => 'El ciclo de facturación no es válido.'
            ]);

            if ($validator->fails()) {
                \Log::warning('Validation failed in hybrid registration', [
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->except(['password', 'password_confirmation'])
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de registro inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();
            
            // If this is coming from PayPal approval, create the user immediately
            if ($request->has('subscription_id')) {
                return $this->createUserWithSubscription($validated, $request->subscription_id);
            }
            
            // Otherwise, just return the PayPal plan ID for frontend to handle
            $plan = SubscriptionPlan::where('key', $validated['plan_key'])->first();
            
            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plan no encontrado.'
                ], 404);
            }
            
            $isYearly = $validated['billing_cycle'] === 'yearly';
            
            // Get appropriate PayPal plan ID
            $paypalPlanId = $isYearly && $plan->paypal_annual_plan_id 
                ? $plan->paypal_annual_plan_id 
                : $plan->paypal_plan_id;
                
            if (!$paypalPlanId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este plan no está disponible para suscripción automática. Por favor contacta al soporte.'
                ], 400);
            }
            
            \Log::info('Returning PayPal plan ID for frontend', [
                'plan_key' => $validated['plan_key'],
                'billing_cycle' => $validated['billing_cycle'],
                'paypal_plan_id' => $paypalPlanId,
                'email' => $validated['email']
            ]);
            
            // Return PayPal plan ID for frontend to handle
            return response()->json([
                'success' => true,
                'paypal_plan_id' => $paypalPlanId,
                'plan_name' => $plan->name,
                'redirect_url' => route('subscription.success')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in hybrid storeRegister', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor. Por favor intenta nuevamente.'
            ], 500);
        }
    }
    
    /**
     * Check if email has existing pre-registration
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'available' => true,
                'message' => 'Email disponible'
            ]);
        }
        
        // Check if user is in pre-registration
        if ($user->pending_cleanup_at) {
            $hoursRemaining = Carbon::parse($user->pending_cleanup_at)->diffInHours(now());
            
            if ($hoursRemaining > 0) {
                return response()->json([
                    'available' => false,
                    'type' => 'pre_registration',
                    'message' => "Ya existe un pre-registro con este email. Tienes {$hoursRemaining} horas para completar tu pago.",
                    'hours_remaining' => $hoursRemaining
                ]);
            } else {
                // Pre-registration expired, can be used
                return response()->json([
                    'available' => true,
                    'message' => 'Pre-registro expirado, email disponible',
                    'expired_pre_registration' => true
                ]);
            }
        }
        
        // User exists and is fully registered
        return response()->json([
            'available' => false,
            'type' => 'registered_user',
            'message' => 'Este email ya está registrado en el sistema.'
        ]);
    }
    
    /**
     * Create user with subscription after PayPal approval
     */
    private function createUserWithSubscription(array $validated, string $subscriptionId)
    {
        DB::beginTransaction();
        
        try {
            // 1. Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'email_verified_at' => now(),
                'pending_cleanup_at' => now()->addDay() // Mark for cleanup in 24h if no subscription
            ]);
            
            // 2. Create tenant
            $tenant = Tenant::create([
                'uuid' => Str::uuid(),
                'name' => $validated['company_name'],
                'slug' => Str::slug($validated['company_name'] . '-' . time()),
                'email' => $validated['email'],
                'plan' => $validated['plan_key'],
                'status' => 'active',
                'trial_ends_at' => null,
                'owner_id' => $user->id
            ]);
            
            // 3. Associate user with tenant
            $tenant->users()->attach($user->id, [
                'role_within_tenant' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
                'invited_at' => now()
            ]);
            
            // 4. Set as current tenant for user
            $user->update(['current_tenant_id' => $tenant->id]);
            
            // 5. Assign admin role to the user (RBAC system)
            $adminRole = \App\Models\Role::where('name', 'admin')->first();
            if ($adminRole) {
                $user->roles()->attach($adminRole->id, [
                    'is_primary' => true
                ]);
            }
            
            // 6. Get plan details
            $plan = SubscriptionPlan::where('key', $validated['plan_key'])->first();
            $isYearly = $validated['billing_cycle'] === 'yearly';
            
            $planPrice = $isYearly && $plan->annual_price ? $plan->annual_price : $plan->price;
            $billingCycle = $isYearly ? 'yearly' : 'monthly';
            
            // 7. Create subscription
            $subscription = Subscription::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'plan' => $validated['plan_key'],
                'status' => 'active', // Will be updated by PayPal webhook
                'amount' => $planPrice,
                'currency' => 'USD',
                'billing_cycle' => $billingCycle,
                'paypal_subscription_id' => $subscriptionId,
                'starts_at' => now(),
                'next_billing_date' => now()->addMonth(),
                'is_trial' => false,
                'trial_days' => $plan->trial_days ?? 0
            ]);
            
            // 8. Clear pending cleanup since user now has subscription
            $user->update(['pending_cleanup_at' => null]);
            
            DB::commit();
            
            \Log::info('User and subscription created successfully via hybrid flow', [
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'paypal_subscription_id' => $subscriptionId
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Cuenta creada exitosamente.',
                'user_id' => $user->id,
                'redirect_url' => route('subscription.success', ['subscription_id' => $subscriptionId])
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Failed to create user with subscription in hybrid flow', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la cuenta. Por favor contacta al soporte.'
            ], 500);
        }
    }
    
    /**
     * Handle PayPal subscription success
     */
    public function success(Request $request)
    {
        $subscriptionId = $request->get('subscription_id');
        $token = $request->get('token');

        if (!$subscriptionId) {
            return redirect()
                ->route('home')
                ->with('error', 'No se pudo confirmar tu suscripción. Por favor intenta nuevamente.');
        }

        try {
            \Log::info('Processing PayPal subscription success', [
                'subscription_id' => $subscriptionId,
                'token' => $token,
                'user_id' => auth()->id(),
                'has_pending_registration' => session()->has('pending_registration')
            ]);

            // Get subscription details from PayPal
            $paypalResponse = $this->paypalService->getSubscription($subscriptionId);
            
            if (!$paypalResponse['success']) {
                throw new \Exception('Failed to get subscription from PayPal: ' . $paypalResponse['error']);
            }

            $paypalSubscription = $paypalResponse['data'];
            
            // Handle user registration if coming from registration flow
            if (session()->has('pending_registration') && !auth()->check()) {
                $user = $this->createUserFromPendingRegistration($subscriptionId, $paypalSubscription);
                if ($user) {
                    auth()->login($user);
                    session()->forget('pending_registration');
                    
                    return redirect()
                        ->route('dashboard')
                        ->with('success', '¡Bienvenido! Tu cuenta y suscripción han sido creadas exitosamente.')
                        ->with('subscription_info', [
                            'plan' => $user->currentTenant->subscription->getPlanNameFormatted(),
                            'next_billing' => $user->currentTenant->subscription->getNextBillingDateFormatted()
                        ]);
                }
            }

            // Find and update existing local subscription
            $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
            
            \Log::info('Subscription lookup result', [
                'subscription_found' => $subscription ? true : false,
                'subscription_id_searched' => $subscriptionId,
                'total_subscriptions' => Subscription::count()
            ]);
            
            if ($subscription) {
                $subscription->update([
                    'status' => strtolower($paypalSubscription['status']),
                    'paypal_metadata' => $paypalSubscription,
                    'starts_at' => Carbon::parse($paypalSubscription['start_time']),
                    'next_billing_date' => isset($paypalSubscription['billing_info']['next_billing_time']) 
                        ? Carbon::parse($paypalSubscription['billing_info']['next_billing_time'])
                        : Carbon::now()->addMonth()
                ]);

                // Update tenant status if tenant exists
                if ($subscription->tenant) {
                    $subscription->tenant->update(['status' => 'active']);
                } else {
                    \Log::warning('Subscription has no associated tenant', [
                        'subscription_id' => $subscription->id,
                        'tenant_id' => $subscription->tenant_id
                    ]);
                }
                
                return redirect()
                    ->route('dashboard')
                    ->with('success', '¡Tu suscripción ha sido activada exitosamente!')
                    ->with('subscription_info', [
                        'plan' => $subscription->getPlanNameFormatted(),
                        'next_billing' => $subscription->getNextBillingDateFormatted()
                    ]);
            } else {
                // Subscription not found in local database - create it
                \Log::warning('PayPal subscription not found in local database, creating new one', [
                    'paypal_subscription_id' => $subscriptionId,
                    'paypal_plan_id' => $paypalSubscription['plan_id'] ?? 'unknown',
                    'user_id' => auth()->id()
                ]);
                
                // Try to determine plan from PayPal plan_id
                $planKey = $this->getPlanKeyFromPayPalPlanId($paypalSubscription['plan_id'] ?? null);
                
                if (!$planKey) {
                    \Log::error('Could not determine plan from PayPal plan_id', [
                        'paypal_plan_id' => $paypalSubscription['plan_id'] ?? 'missing',
                        'subscription_id' => $subscriptionId
                    ]);
                    
                    return redirect()
                        ->route('dashboard')
                        ->with('warning', 'Tu suscripción ha sido procesada pero necesita configuración adicional. Contacta al soporte.');
                }
                
                // Create subscription in local database
                $subscription = $this->createSubscriptionFromPayPal(
                    $subscriptionId, 
                    $paypalSubscription, 
                    $planKey
                );
                
                if ($subscription) {
                    return redirect()
                        ->route('dashboard')
                        ->with('success', '¡Tu suscripción ha sido activada exitosamente!')
                        ->with('subscription_info', [
                            'plan' => $subscription->getPlanNameFormatted(),
                            'next_billing' => $subscription->getNextBillingDateFormatted()
                        ]);
                } else {
                    return redirect()
                        ->route('dashboard')
                        ->with('warning', 'Tu suscripción ha sido procesada pero hubo un problema al configurarla. Contacta al soporte.');
                }
            }

        } catch (\Exception $e) {
            \Log::error('Failed to process PayPal subscription success', [
                'subscription_id' => $subscriptionId,
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->route('subscription.plans')
                ->with('error', 'Hubo un problema al confirmar tu suscripción. Nuestro equipo ha sido notificado.');
        }
    }

    /**
     * Handle PayPal subscription cancellation
     */
    public function cancelled(Request $request)
    {
        $subscriptionId = $request->get('subscription_id');
        $token = $request->get('token');

        return redirect()
            ->route('subscription.plans')
            ->with('warning', 'Has cancelado el proceso de suscripción. Puedes intentar nuevamente cuando desees.');
    }

    /**
     * Show subscription dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $tenant = $user->currentTenant;
        
        if (!$tenant) {
            return redirect()->route('tenant.select');
        }

        $subscription = $tenant->subscription;
        
        if (!$subscription) {
            return redirect()->route('subscription.plans');
        }

        $recentPayments = $subscription->payments()
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get();

        $usageStats = [
            'users' => $tenant->users()->count(),
            'lots_this_month' => $tenant->lots()->whereMonth('created_at', Carbon::now()->month)->count(),
            'sales_this_month' => $tenant->sales()->whereMonth('created_at', Carbon::now()->month)->count(),
            'revenue_this_month' => $tenant->sales()
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('total_amount')
        ];

        $planLimits = $subscription->getFeatures();
        $usageViolations = $subscription->isWithinLimits();

        return view('subscription.dashboard', compact(
            'subscription', 
            'recentPayments', 
            'usageStats', 
            'planLimits', 
            'usageViolations'
        ));
    }

    /**
     * Upgrade subscription plan
     */
    public function upgrade(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan' => 'required|in:basic,premium,enterprise,corporate'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = Auth::user();
        $tenant = $user->currentTenant;
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return back()->with('error', 'No tienes una suscripción activa.');
        }

        // Check if upgrade is valid
        $currentPlan = $subscription->plan;
        $newPlan = $request->plan;
        
        if (!$this->isValidUpgrade($currentPlan, $newPlan)) {
            return back()->with('error', 'No puedes cambiar a este plan desde tu plan actual.');
        }

        try {
            // If current subscription is trial, create new PayPal subscription
            if ($subscription->is_trial) {
                return $this->createPaidSubscription($subscription, $newPlan);
            } else {
                // Update existing PayPal subscription
                return $this->updatePaidSubscription($subscription, $newPlan);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to upgrade subscription', [
                'subscription_id' => $subscription->id,
                'current_plan' => $currentPlan,
                'new_plan' => $newPlan,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Hubo un error al actualizar tu suscripción. Por favor intenta nuevamente.');
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->currentTenant;
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return back()->with('error', 'No tienes una suscripción activa.');
        }

        $reason = $request->get('reason', 'User requested cancellation');

        try {
            if ($subscription->paypal_subscription_id) {
                // Cancel PayPal subscription
                $result = $this->paypalService->cancelSubscription($subscription->paypal_subscription_id, $reason);
                
                if (!$result['success']) {
                    throw new \Exception('Failed to cancel PayPal subscription: ' . $result['error']);
                }
            }

            // Update local subscription
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => Carbon::now(),
                'cancellation_reason' => $reason,
                'cancelled_by' => 'user'
            ]);

            return redirect()
                ->route('subscription.dashboard')
                ->with('success', 'Tu suscripción ha sido cancelada. Tienes acceso hasta el final del período facturado.');

        } catch (\Exception $e) {
            \Log::error('Failed to cancel subscription', [
                'subscription_id' => $subscription->id,
                'paypal_subscription_id' => $subscription->paypal_subscription_id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Hubo un error al cancelar tu suscripción. Por favor contacta soporte.');
        }
    }

    /**
     * Get plan amount
     */
    private function getPlanAmount(string $plan): float
    {
        $plans = config('paypal.plans');
        return $plans[$plan]['amount'] ?? 0;
    }

    /**
     * Get plan features
     */
    private function getPlanFeatures(string $plan): array
    {
        $plans = config('paypal.plans');
        return $plans[$plan]['features'] ?? [];
    }

    /**
     * Check if upgrade is valid
     */
    private function isValidUpgrade(string $currentPlan, string $newPlan): bool
    {
        $planHierarchy = ['trial' => 1, 'basic' => 2, 'premium' => 3, 'enterprise' => 4, 'corporate' => 5];
        
        return ($planHierarchy[$newPlan] ?? 0) > ($planHierarchy[$currentPlan] ?? 0);
    }

    /**
     * Create new paid subscription from trial
     */
    private function createPaidSubscription(Subscription $subscription, string $newPlan)
    {
        $planConfig = config("paypal.plans.{$newPlan}");
        $planId = $planConfig['paypal_plan_id'] ?? null;

        if (!$planId) {
            throw new \Exception("PayPal plan ID not configured for plan: {$newPlan}");
        }

        // Create PayPal subscription
        $result = $this->paypalService->createSubscription($subscription->tenant, $planId, [
            'plan' => $newPlan,
            'amount' => $planConfig['amount'],
            'currency' => $planConfig['currency'],
            'interval' => 'monthly',
            'is_trial' => false,
            'subscriber' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email
            ]
        ]);

        if (!$result['success']) {
            throw new \Exception('Failed to create PayPal subscription: ' . $result['error']);
        }

        // Update local subscription
        $subscription->update([
            'paypal_subscription_id' => $result['subscription_id'],
            'paypal_plan_id' => $planId,
            'plan' => $newPlan,
            'amount' => $planConfig['amount'],
            'is_trial' => false,
            'trial_ends_at' => null,
            'plan_features' => $planConfig['features']
        ]);

        // Redirect to PayPal for approval
        return redirect($result['approval_url']);
    }

    /**
     * Update existing paid subscription
     */
    private function updatePaidSubscription(Subscription $subscription, string $newPlan)
    {
        // For now, we'll update locally and handle PayPal plan changes via webhooks
        // In a full implementation, you'd need to use PayPal's subscription revision API
        
        $planConfig = config("paypal.plans.{$newPlan}");
        
        $subscription->update([
            'plan' => $newPlan,
            'amount' => $planConfig['amount'],
            'plan_features' => $planConfig['features']
        ]);

        return back()->with('success', 'Tu plan ha sido actualizado. Los cambios se aplicarán en tu próximo período de facturación.');
    }
    
    /**
     * Get plan key from PayPal plan ID
     */
    private function getPlanKeyFromPayPalPlanId($paypalPlanId): ?string
    {
        if (!$paypalPlanId) {
            return null;
        }
        
        // Map PayPal plan IDs to local plan keys
        $planMapping = [
            config('services.paypal.basic_plan_id') => 'basic',
            config('services.paypal.premium_plan_id') => 'premium', 
            config('services.paypal.enterprise_plan_id') => 'enterprise',
            config('services.paypal.corporate_plan_id') => 'corporate',
        ];
        
        return $planMapping[$paypalPlanId] ?? null;
    }
    
    /**
     * Create subscription from PayPal data
     */
    private function createSubscriptionFromPayPal($subscriptionId, $paypalSubscription, $planKey): ?Subscription
    {
        try {
            $user = auth()->user();
            
            // Get or create tenant for user
            $tenant = $user->currentTenant;
            if (!$tenant) {
                // Create a tenant if user doesn't have one
                $tenant = Tenant::create([
                    'uuid' => Str::uuid(),
                    'name' => $user->name . '\'s Company',
                    'slug' => Str::slug($user->name . '-company-' . time()),
                    'email' => $user->email,
                    'plan' => $planKey,
                    'status' => 'active',
                    'trial_ends_at' => null,
                ]);
                
                // Associate user with tenant
                $tenant->users()->attach($user->id, [
                    'role_within_tenant' => 'owner',
                    'status' => 'active',
                    'joined_at' => now()
                ]);
                
                // Set as current tenant
                $user->update(['current_tenant_id' => $tenant->id]);
            }
            
            // Get plan details
            $planData = $this->getPlanData($planKey);
            
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan' => $planKey,
                'status' => strtolower($paypalSubscription['status']),
                'amount' => $planData['price'] ?? 0,
                'currency' => 'USD',
                'billing_cycle' => $planData['billing_cycle'] ?? 'monthly',
                'paypal_subscription_id' => $subscriptionId,
                'paypal_metadata' => $paypalSubscription,
                'starts_at' => Carbon::parse($paypalSubscription['start_time']),
                'next_billing_date' => isset($paypalSubscription['billing_info']['next_billing_time']) 
                    ? Carbon::parse($paypalSubscription['billing_info']['next_billing_time'])
                    : Carbon::now()->addMonth(),
                'is_trial' => false
            ]);
            
            \Log::info('Created subscription from PayPal data', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $tenant->id,
                'plan' => $planKey
            ]);
            
            return $subscription;
            
        } catch (\Exception $e) {
            \Log::error('Failed to create subscription from PayPal data', [
                'paypal_subscription_id' => $subscriptionId,
                'plan_key' => $planKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }
    
    /**
     * Get plan data by key
     */
    private function getPlanData($planKey): array
    {
        $plans = [
            'basic' => ['price' => 29.00, 'billing_cycle' => 'monthly'],
            'premium' => ['price' => 79.00, 'billing_cycle' => 'monthly'],
            'enterprise' => ['price' => 199.00, 'billing_cycle' => 'monthly'],
            'corporate' => ['price' => 499.00, 'billing_cycle' => 'monthly'],
        ];
        
        return $plans[$planKey] ?? ['price' => 0, 'billing_cycle' => 'monthly'];
    }
    
    /**
     * Create user from pending registration data
     */
    private function createUserFromPendingRegistration($subscriptionId, $paypalSubscription): ?User
    {
        try {
            $pendingData = session('pending_registration');
            
            if (!$pendingData) {
                \Log::error('No pending registration data found', ['subscription_id' => $subscriptionId]);
                return null;
            }
            
            \Log::info('Creating user from pending registration', [
                'email' => $pendingData['email'],
                'company_name' => $pendingData['company_name'],
                'plan_key' => $pendingData['plan_key']
            ]);
            
            DB::beginTransaction();
            
            // 1. Create user
            $user = User::create([
                'name' => $pendingData['name'],
                'email' => $pendingData['email'],
                'password' => $pendingData['password'], // Already hashed
                'email_verified_at' => now()
            ]);
            
            // 2. Create tenant
            $tenant = Tenant::create([
                'uuid' => Str::uuid(),
                'name' => $pendingData['company_name'],
                'slug' => Str::slug($pendingData['company_name'] . '-' . time()),
                'email' => $pendingData['email'],
                'plan' => $pendingData['plan_key'],
                'status' => 'active',
                'trial_ends_at' => null,
                'owner_id' => $user->id
            ]);
            
            // 3. Associate user with tenant
            $tenant->users()->attach($user->id, [
                'role_within_tenant' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
                'invited_at' => now()
            ]);
            
            // 4. Set as current tenant for user
            $user->update(['current_tenant_id' => $tenant->id]);
            
            // 5. Get plan details
            $plan = SubscriptionPlan::where('key', $pendingData['plan_key'])->first();
            $isYearly = $pendingData['billing_cycle'] === 'yearly';
            
            $planPrice = $isYearly && $plan->annual_price ? $plan->annual_price : $plan->price;
            $billingCycle = $isYearly ? 'yearly' : 'monthly';
            
            // 6. Create subscription
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan' => $pendingData['plan_key'],
                'status' => strtolower($paypalSubscription['status']),
                'amount' => $planPrice,
                'currency' => 'USD',
                'billing_cycle' => $billingCycle,
                'paypal_subscription_id' => $subscriptionId,
                'paypal_metadata' => $paypalSubscription,
                'starts_at' => Carbon::parse($paypalSubscription['start_time']),
                'next_billing_date' => isset($paypalSubscription['billing_info']['next_billing_time']) 
                    ? Carbon::parse($paypalSubscription['billing_info']['next_billing_time'])
                    : Carbon::now()->addMonth(),
                'is_trial' => false,
                'trial_days' => $plan->trial_days ?? 0
            ]);
            
            DB::commit();
            
            \Log::info('User and subscription created successfully', [
                'user_id' => $user->id,
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id
            ]);
            
            return $user;
            
        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Failed to create user from pending registration', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }
}