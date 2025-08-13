<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class SubscriptionPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'subscription_id',
        'tenant_id',
        'paypal_payment_id',
        'paypal_capture_id',
        'paypal_order_id',
        'paypal_payer_id',
        'amount',
        'currency',
        'type',
        'status',
        'billing_cycle',
        'billing_period_start',
        'billing_period_end',
        'payment_date',
        'completed_at',
        'failed_at',
        'refunded_at',
        'failure_reason',
        'failure_details',
        'retry_count',
        'next_retry_at',
        'refunded_amount',
        'refund_reason',
        'refunded_by',
        'paypal_response',
        'webhook_data',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'billing_cycle' => 'integer',
        'retry_count' => 'integer',
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'payment_date' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'paypal_response' => 'array',
        'webhook_data' => 'array'
    ];

    /**
     * Get the subscription that owns the payment
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the tenant that owns the payment
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is refunded
     */
    public function isRefunded(): bool
    {
        return in_array($this->status, ['refunded', 'partially_refunded']);
    }

    /**
     * Check if payment is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get amount formatted with currency
     */
    public function getAmountFormatted(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '$';
        return $symbol . number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Get refunded amount formatted
     */
    public function getRefundedAmountFormatted(): string
    {
        if ($this->refunded_amount <= 0) {
            return 'N/A';
        }

        $symbol = $this->currency === 'USD' ? '$' : '$';
        return $symbol . number_format($this->refunded_amount, 2) . ' ' . $this->currency;
    }

    /**
     * Get payment status badge
     */
    public function getStatusBadge(): array
    {
        $badges = [
            'pending' => ['class' => 'badge-warning', 'text' => 'Pendiente'],
            'completed' => ['class' => 'badge-success', 'text' => 'Completado'],
            'failed' => ['class' => 'badge-danger', 'text' => 'Falló'],
            'cancelled' => ['class' => 'badge-secondary', 'text' => 'Cancelado'],
            'refunded' => ['class' => 'badge-info', 'text' => 'Reembolsado'],
            'partially_refunded' => ['class' => 'badge-info', 'text' => 'Parcial. Reembolsado']
        ];

        return $badges[$this->status] ?? ['class' => 'badge-secondary', 'text' => 'Desconocido'];
    }

    /**
     * Get payment type formatted
     */
    public function getTypeFormatted(): string
    {
        $types = [
            'initial' => 'Pago Inicial',
            'recurring' => 'Pago Recurrente',
            'retry' => 'Reintento',
            'refund' => 'Reembolso',
            'partial_refund' => 'Reembolso Parcial'
        ];

        return $types[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get payment date formatted
     */
    public function getPaymentDateFormatted(): string
    {
        if (!$this->payment_date) {
            return 'N/A';
        }

        return $this->payment_date->format('M d, Y H:i');
    }

    /**
     * Get failure details summary
     */
    public function getFailureSummary(): string
    {
        if (!$this->isFailed()) {
            return 'N/A';
        }

        $reason = $this->failure_reason ?? 'Unknown error';
        $details = $this->failure_details;

        return $details ? "{$reason}: {$details}" : $reason;
    }

    /**
     * Check if payment can be retried
     */
    public function canRetry(): bool
    {
        return $this->isFailed() && 
               $this->retry_count < 3 && 
               ($this->next_retry_at === null || $this->next_retry_at->isPast());
    }

    /**
     * Get next retry date formatted
     */
    public function getNextRetryFormatted(): string
    {
        if (!$this->next_retry_at) {
            return 'N/A';
        }

        if ($this->next_retry_at->isPast()) {
            return 'Ready to retry';
        }

        return $this->next_retry_at->format('M d, Y H:i');
    }

    /**
     * Calculate net amount (amount - refunded)
     */
    public function getNetAmount(): float
    {
        return $this->amount - $this->refunded_amount;
    }

    /**
     * Get net amount formatted
     */
    public function getNetAmountFormatted(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '$';
        return $symbol . number_format($this->getNetAmount(), 2) . ' ' . $this->currency;
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed payments
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for refunded payments
     */
    public function scopeRefunded($query)
    {
        return $query->whereIn('status', ['refunded', 'partially_refunded']);
    }

    /**
     * Scope for initial payments
     */
    public function scopeInitial($query)
    {
        return $query->where('type', 'initial');
    }

    /**
     * Scope for recurring payments
     */
    public function scopeRecurring($query)
    {
        return $query->where('type', 'recurring');
    }

    /**
     * Scope for payments that can be retried
     */
    public function scopeCanRetry($query)
    {
        return $query->where('status', 'failed')
                    ->where('retry_count', '<', 3)
                    ->where(function($q) {
                        $q->whereNull('next_retry_at')
                          ->orWhere('next_retry_at', '<=', Carbon::now());
                    });
    }

    /**
     * Scope for payments in date range
     */
    public function scopeInDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope for payments by billing cycle
     */
    public function scopeByCycle($query, int $cycle)
    {
        return $query->where('billing_cycle', $cycle);
    }

    /**
     * Scope for today's payments
     */
    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', Carbon::today());
    }

    /**
     * Scope for this month's payments
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', Carbon::now()->month)
                    ->whereYear('payment_date', Carbon::now()->year);
    }
}