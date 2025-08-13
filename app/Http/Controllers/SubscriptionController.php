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
     * Handle PayPal subscription success
     */
    public function success(Request $request)
    {
        $subscriptionId = $request->get('subscription_id');
        $token = $request->get('token');

        if (!$subscriptionId) {
            return redirect()
                ->route('subscription.plans')
                ->with('error', 'No se pudo confirmar tu suscripción. Por favor intenta nuevamente.');
        }

        try {
            // Get subscription details from PayPal
            $paypalResponse = $this->paypalService->getSubscription($subscriptionId);
            
            if (!$paypalResponse['success']) {
                throw new \Exception('Failed to get subscription from PayPal: ' . $paypalResponse['error']);
            }

            $paypalSubscription = $paypalResponse['data'];

            // Find and update local subscription
            $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
            
            if ($subscription) {
                $subscription->update([
                    'status' => strtolower($paypalSubscription['status']),
                    'paypal_metadata' => $paypalSubscription,
                    'starts_at' => Carbon::parse($paypalSubscription['start_time']),
                    'next_billing_date' => isset($paypalSubscription['billing_info']['next_billing_time']) 
                        ? Carbon::parse($paypalSubscription['billing_info']['next_billing_time'])
                        : Carbon::now()->addMonth()
                ]);

                // Update tenant status
                $subscription->tenant->update(['status' => 'active']);
            }

            return redirect()
                ->route('dashboard')
                ->with('success', '¡Tu suscripción ha sido activada exitosamente!')
                ->with('subscription_info', [
                    'plan' => $subscription->getPlanNameFormatted(),
                    'next_billing' => $subscription->getNextBillingDateFormatted()
                ]);

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
}