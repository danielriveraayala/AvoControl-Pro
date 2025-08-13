<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'subscription_id',
        'subscription_payment_id',
        'tenant_id',
        'user_id',
        'type',
        'event',
        'level',
        'message',
        'description',
        'context',
        'paypal_request_id',
        'paypal_correlation_id',
        'paypal_debug_id',
        'request_data',
        'response_data',
        'response_code',
        'execution_time',
        'occurred_at',
        'ip_address',
        'user_agent',
        'session_id',
        'processed',
        'processed_at',
        'processed_by'
    ];

    protected $casts = [
        'context' => 'array',
        'request_data' => 'array',
        'response_data' => 'array',
        'response_code' => 'integer',
        'execution_time' => 'decimal:3',
        'occurred_at' => 'datetime',
        'processed_at' => 'datetime',
        'processed' => 'boolean'
    ];

    /**
     * Get the subscription associated with the log
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the subscription payment associated with the log
     */
    public function subscriptionPayment(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPayment::class);
    }

    /**
     * Get the tenant associated with the log
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user associated with the log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get log level badge
     */
    public function getLevelBadge(): array
    {
        $badges = [
            'debug' => ['class' => 'badge-secondary', 'text' => 'Debug'],
            'info' => ['class' => 'badge-info', 'text' => 'Info'],
            'warning' => ['class' => 'badge-warning', 'text' => 'Warning'],
            'error' => ['class' => 'badge-danger', 'text' => 'Error'],
            'critical' => ['class' => 'badge-dark', 'text' => 'Critical']
        ];

        return $badges[$this->level] ?? ['class' => 'badge-secondary', 'text' => 'Unknown'];
    }

    /**
     * Get event type badge
     */
    public function getEventBadge(): array
    {
        $eventCategories = [
            // PayPal Events - Blue
            'subscription_created' => ['class' => 'badge-primary', 'icon' => 'fas fa-plus'],
            'subscription_activated' => ['class' => 'badge-success', 'icon' => 'fas fa-play'],
            'subscription_cancelled' => ['class' => 'badge-secondary', 'icon' => 'fas fa-times'],
            'subscription_suspended' => ['class' => 'badge-warning', 'icon' => 'fas fa-pause'],
            'subscription_reactivated' => ['class' => 'badge-success', 'icon' => 'fas fa-redo'],
            'payment_completed' => ['class' => 'badge-success', 'icon' => 'fas fa-check'],
            'payment_failed' => ['class' => 'badge-danger', 'icon' => 'fas fa-exclamation'],
            'payment_refunded' => ['class' => 'badge-info', 'icon' => 'fas fa-undo'],
            'webhook_received' => ['class' => 'badge-info', 'icon' => 'fas fa-webhook'],
            
            // System Events - Purple
            'trial_started' => ['class' => 'badge-primary', 'icon' => 'fas fa-clock'],
            'trial_ending_soon' => ['class' => 'badge-warning', 'icon' => 'fas fa-clock'],
            'trial_expired' => ['class' => 'badge-danger', 'icon' => 'fas fa-clock'],
            'plan_upgraded' => ['class' => 'badge-success', 'icon' => 'fas fa-arrow-up'],
            'plan_downgraded' => ['class' => 'badge-warning', 'icon' => 'fas fa-arrow-down'],
            'account_suspended' => ['class' => 'badge-danger', 'icon' => 'fas fa-ban'],
            'account_reactivated' => ['class' => 'badge-success', 'icon' => 'fas fa-check'],
            'grace_period_started' => ['class' => 'badge-warning', 'icon' => 'fas fa-hourglass-start'],
            'grace_period_ended' => ['class' => 'badge-danger', 'icon' => 'fas fa-hourglass-end'],
            
            // User Events - Green
            'user_subscribed' => ['class' => 'badge-success', 'icon' => 'fas fa-user-plus'],
            'user_cancelled' => ['class' => 'badge-secondary', 'icon' => 'fas fa-user-times'],
            'user_updated_payment' => ['class' => 'badge-info', 'icon' => 'fas fa-credit-card'],
            'user_accessed_dashboard' => ['class' => 'badge-light', 'icon' => 'fas fa-eye'],
            
            // Admin Events - Orange
            'admin_suspended_account' => ['class' => 'badge-warning', 'icon' => 'fas fa-user-lock'],
            'admin_reactivated_account' => ['class' => 'badge-success', 'icon' => 'fas fa-user-check'],
            'admin_refunded_payment' => ['class' => 'badge-info', 'icon' => 'fas fa-hand-holding-usd'],
            'admin_extended_trial' => ['class' => 'badge-primary', 'icon' => 'fas fa-calendar-plus'],
            
            // Errors - Red
            'api_error' => ['class' => 'badge-danger', 'icon' => 'fas fa-exclamation-triangle'],
            'webhook_error' => ['class' => 'badge-danger', 'icon' => 'fas fa-exclamation-triangle'],
            'payment_processing_error' => ['class' => 'badge-danger', 'icon' => 'fas fa-exclamation-triangle'],
            'sync_error' => ['class' => 'badge-danger', 'icon' => 'fas fa-exclamation-triangle']
        ];

        return $eventCategories[$this->event] ?? ['class' => 'badge-secondary', 'icon' => 'fas fa-question'];
    }

    /**
     * Get type formatted
     */
    public function getTypeFormatted(): string
    {
        $types = [
            'api_request' => 'API Request',
            'api_response' => 'API Response',
            'webhook' => 'Webhook',
            'system' => 'System',
            'user_action' => 'User Action',
            'cron' => 'CRON Job',
            'error' => 'Error',
            'debug' => 'Debug'
        ];

        return $types[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get event formatted
     */
    public function getEventFormatted(): string
    {
        $events = [
            'subscription_created' => 'Suscripción Creada',
            'subscription_activated' => 'Suscripción Activada',
            'subscription_cancelled' => 'Suscripción Cancelada',
            'subscription_suspended' => 'Suscripción Suspendida',
            'subscription_reactivated' => 'Suscripción Reactivada',
            'payment_completed' => 'Pago Completado',
            'payment_failed' => 'Pago Falló',
            'payment_refunded' => 'Pago Reembolsado',
            'webhook_received' => 'Webhook Recibido',
            'trial_started' => 'Trial Iniciado',
            'trial_ending_soon' => 'Trial Terminando',
            'trial_expired' => 'Trial Expirado',
            'plan_upgraded' => 'Plan Actualizado',
            'plan_downgraded' => 'Plan Degradado',
            'account_suspended' => 'Cuenta Suspendida',
            'account_reactivated' => 'Cuenta Reactivada',
            'grace_period_started' => 'Período de Gracia Iniciado',
            'grace_period_ended' => 'Período de Gracia Terminado',
            'user_subscribed' => 'Usuario Suscrito',
            'user_cancelled' => 'Usuario Canceló',
            'user_updated_payment' => 'Usuario Actualizó Pago',
            'user_accessed_dashboard' => 'Usuario Accedió al Dashboard',
            'admin_suspended_account' => 'Admin Suspendió Cuenta',
            'admin_reactivated_account' => 'Admin Reactivó Cuenta',
            'admin_refunded_payment' => 'Admin Reembolsó Pago',
            'admin_extended_trial' => 'Admin Extendió Trial',
            'api_error' => 'Error de API',
            'webhook_error' => 'Error de Webhook',
            'payment_processing_error' => 'Error Procesando Pago',
            'sync_error' => 'Error de Sincronización'
        ];

        return $events[$this->event] ?? ucwords(str_replace('_', ' ', $this->event));
    }

    /**
     * Get occurred time formatted
     */
    public function getOccurredAtFormatted(): string
    {
        if (!$this->occurred_at) {
            return 'N/A';
        }

        return $this->occurred_at->format('M d, Y H:i:s');
    }

    /**
     * Get occurred time for humans
     */
    public function getOccurredAtHuman(): string
    {
        if (!$this->occurred_at) {
            return 'N/A';
        }

        return $this->occurred_at->diffForHumans();
    }

    /**
     * Get execution time formatted
     */
    public function getExecutionTimeFormatted(): string
    {
        if (!$this->execution_time) {
            return 'N/A';
        }

        if ($this->execution_time < 1) {
            return number_format($this->execution_time * 1000, 2) . 'ms';
        }

        return number_format($this->execution_time, 3) . 's';
    }

    /**
     * Get response code status
     */
    public function getResponseCodeStatus(): array
    {
        if (!$this->response_code) {
            return ['class' => 'badge-secondary', 'text' => 'N/A'];
        }

        if ($this->response_code >= 200 && $this->response_code < 300) {
            return ['class' => 'badge-success', 'text' => $this->response_code];
        } elseif ($this->response_code >= 300 && $this->response_code < 400) {
            return ['class' => 'badge-info', 'text' => $this->response_code];
        } elseif ($this->response_code >= 400 && $this->response_code < 500) {
            return ['class' => 'badge-warning', 'text' => $this->response_code];
        } elseif ($this->response_code >= 500) {
            return ['class' => 'badge-danger', 'text' => $this->response_code];
        }

        return ['class' => 'badge-secondary', 'text' => $this->response_code];
    }

    /**
     * Check if log has error
     */
    public function hasError(): bool
    {
        return in_array($this->level, ['error', 'critical']) || 
               ($this->response_code && $this->response_code >= 400);
    }

    /**
     * Check if log is processed
     */
    public function isProcessed(): bool
    {
        return $this->processed;
    }

    /**
     * Get context summary
     */
    public function getContextSummary(): string
    {
        if (!$this->context || empty($this->context)) {
            return 'No additional context';
        }

        $summary = [];
        foreach ($this->context as $key => $value) {
            if (is_array($value)) {
                $summary[] = $key . ': [Array]';
            } elseif (is_bool($value)) {
                $summary[] = $key . ': ' . ($value ? 'true' : 'false');
            } else {
                $summary[] = $key . ': ' . (string)$value;
            }
        }

        return implode(', ', array_slice($summary, 0, 3)) . (count($summary) > 3 ? '...' : '');
    }

    /**
     * Scope for specific log level
     */
    public function scopeLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for specific event type
     */
    public function scopeEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope for error logs
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('level', ['error', 'critical']);
    }

    /**
     * Scope for warning logs
     */
    public function scopeWarnings($query)
    {
        return $query->where('level', 'warning');
    }

    /**
     * Scope for info logs
     */
    public function scopeInfo($query)
    {
        return $query->where('level', 'info');
    }

    /**
     * Scope for PayPal API logs
     */
    public function scopePayPalApi($query)
    {
        return $query->whereIn('type', ['api_request', 'api_response']);
    }

    /**
     * Scope for webhook logs
     */
    public function scopeWebhooks($query)
    {
        return $query->where('type', 'webhook');
    }

    /**
     * Scope for system logs
     */
    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    /**
     * Scope for user action logs
     */
    public function scopeUserActions($query)
    {
        return $query->where('type', 'user_action');
    }

    /**
     * Scope for unprocessed logs
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope for today's logs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('occurred_at', Carbon::today());
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('occurred_at', '>=', Carbon::now()->subHours($hours));
    }

    /**
     * Scope for logs in date range
     */
    public function scopeInDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('occurred_at', [$startDate, $endDate]);
    }

    /**
     * Scope for logs by subscription
     */
    public function scopeBySubscription($query, int $subscriptionId)
    {
        return $query->where('subscription_id', $subscriptionId);
    }

    /**
     * Scope for logs by tenant
     */
    public function scopeByTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope for logs with PayPal correlation ID
     */
    public function scopeByCorrelationId($query, string $correlationId)
    {
        return $query->where('paypal_correlation_id', $correlationId);
    }
}