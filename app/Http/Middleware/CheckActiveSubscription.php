<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for non-authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip for super admin users
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Skip for specific routes that should always be accessible
        $excludedRoutes = [
            'login', 'logout', 'register', 'password.request', 'password.email', 
            'password.reset', 'password.update', 'verification.notice', 
            'verification.verify', 'verification.send', 'subscription.register',
            'subscription.create', 'subscription.store', 'subscription.success',
            'paypal.webhook', 'profile.index', 'profile.update', 'profile.password'
        ];

        if (in_array($request->route()->getName(), $excludedRoutes)) {
            return $next($request);
        }

        // Check if user has current tenant
        if (!$user->current_tenant_id) {
            Log::info('User has no current tenant', ['user_id' => $user->id]);
            return redirect()->route('tenant.select')->with('warning', 'Necesitas seleccionar una empresa para continuar.');
        }

        // Get active subscription for current tenant
        $subscription = Subscription::where('tenant_id', $user->current_tenant_id)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            Log::info('No active subscription found for tenant', [
                'user_id' => $user->id,
                'tenant_id' => $user->current_tenant_id
            ]);

            // Check if there's a suspended subscription
            $suspendedSubscription = Subscription::where('tenant_id', $user->current_tenant_id)
                ->where('status', 'suspended')
                ->first();

            if ($suspendedSubscription) {
                return $this->handleSuspendedSubscription($suspendedSubscription);
            }

            // Check if there's a cancelled subscription
            $cancelledSubscription = Subscription::where('tenant_id', $user->current_tenant_id)
                ->where('status', 'cancelled')
                ->first();

            if ($cancelledSubscription) {
                return $this->handleCancelledSubscription($cancelledSubscription);
            }

            // No subscription at all - redirect to subscription selection
            return redirect()->route('subscription.register', ['plan' => 'basic'])
                ->with('error', 'No tienes una suscripción activa. Selecciona un plan para continuar.');
        }

        // Check if subscription is in grace period
        if ($subscription->isInGracePeriod()) {
            $daysLeft = $subscription->daysUntilSuspension();
            session()->flash('warning', "Tu suscripción está en período de gracia. Tienes $daysLeft días para actualizar tu método de pago.");
        }

        // Check if trial is ending soon
        if ($subscription->is_trial && $subscription->trial_ends_at) {
            $daysLeft = $subscription->trial_ends_at->diffInDays(now());
            if ($daysLeft <= 3) {
                session()->flash('info', "Tu período de prueba termina en $daysLeft días. Actualiza tu suscripción para continuar usando el sistema.");
            }
        }

        return $next($request);
    }

    /**
     * Handle suspended subscription
     */
    private function handleSuspendedSubscription($subscription)
    {
        $reason = $subscription->suspension_reason ?? 'Suscripción suspendida';
        
        // Check if can be reactivated
        if ($subscription->canBeReactivated()) {
            return response()->view('subscription.suspended', [
                'subscription' => $subscription,
                'canReactivate' => true,
                'message' => "Tu suscripción está suspendida. Motivo: $reason"
            ], 403);
        }

        return response()->view('subscription.suspended', [
            'subscription' => $subscription,
            'canReactivate' => false,
            'message' => "Tu suscripción está suspendida permanentemente. Motivo: $reason"
        ], 403);
    }

    /**
     * Handle cancelled subscription
     */
    private function handleCancelledSubscription($subscription)
    {
        $reason = $subscription->cancellation_reason ?? 'Suscripción cancelada';
        
        return response()->view('subscription.cancelled', [
            'subscription' => $subscription,
            'message' => "Tu suscripción fue cancelada. Motivo: $reason"
        ], 403);
    }
}