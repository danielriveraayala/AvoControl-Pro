<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\SubscriptionPayment;
use App\Services\PayPalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class SubscriptionController extends Controller
{
    private PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->middleware(['auth', 'developer']);
        $this->paypalService = $paypalService;
    }

    /**
     * Display subscriptions management dashboard
     */
    public function index()
    {
        $stats = $this->getSubscriptionStats();
        $metrics = $this->getBusinessMetrics();
        $recentActivity = $this->getRecentActivity();
        $chartData = $this->getChartData();
        
        // Critical alerts
        $alerts = $this->getCriticalAlerts();

        return view('developer.subscriptions.index', compact(
            'stats', 
            'metrics',
            'recentActivity',
            'chartData',
            'alerts'
        ));
    }

    /**
     * Show specific subscription details
     */
    public function show(Subscription $subscription)
    {
        $subscription->load(['tenant', 'user', 'payments' => function($query) {
            $query->orderBy('payment_date', 'desc');
        }]);

        $paymentStats = [
            'total_paid' => $subscription->payments()->where('status', 'completed')->sum('amount'),
            'failed_payments' => $subscription->payments()->where('status', 'failed')->count(),
            'last_payment' => $subscription->payments()->where('status', 'completed')->latest('payment_date')->first(),
            'next_payment' => $subscription->next_billing_date,
        ];

        // Get PayPal subscription details if available
        $paypalDetails = null;
        if ($subscription->paypal_subscription_id) {
            $paypalResponse = $this->paypalService->getSubscription($subscription->paypal_subscription_id);
            if ($paypalResponse['success']) {
                $paypalDetails = $paypalResponse['data'];
            }
        }

        return view('developer.subscriptions.show', compact(
            'subscription', 
            'paymentStats', 
            'paypalDetails'
        ));
    }

    /**
     * Get subscriptions data for DataTables (enhanced)
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            \Log::info('Developer SubscriptionController getData called', [
                'user_id' => auth()->id(),
                'request_params' => $request->all()
            ]);

            $query = Subscription::with(['tenant', 'user'])
                ->select('subscriptions.*');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        if ($request->filled('is_trial')) {
            $query->where('is_trial', $request->boolean('is_trial'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return DataTables::eloquent($query)
            ->addColumn('tenant_info', function ($subscription) {
                $tenant = $subscription->tenant;
                if (!$tenant) return '<span class="text-muted">N/A</span>';
                
                $status = $tenant->status;
                $badgeClass = $status === 'active' ? 'success' : ($status === 'suspended' ? 'danger' : 'warning');
                
                return '<div>' . 
                       '<strong>' . htmlspecialchars($tenant->name) . '</strong><br>' .
                       '<small class="text-muted">' . $tenant->slug . '</small><br>' .
                       '<span class="badge badge-' . $badgeClass . '">' . ucfirst($status) . '</span>' .
                       '</div>';
            })
            ->addColumn('plan_details', function ($subscription) {
                $planName = $subscription->getPlanNameFormatted();
                $amount = $subscription->getAmountFormatted();
                $trialBadge = $subscription->is_trial ? '<br><small class="badge badge-info">Trial</small>' : '';
                
                return '<div>' .
                       '<strong>' . $planName . '</strong><br>' .
                       '<small class="text-muted">' . $amount . ($subscription->interval ? '/' . $subscription->interval : '') . '</small>' .
                       $trialBadge .
                       '</div>';
            })
            ->addColumn('status_info', function ($subscription) {
                $badge = $subscription->getStatusBadge();
                $extra = '';
                
                // Grace period info
                if ($subscription->isInGracePeriod()) {
                    $daysLeft = $subscription->daysUntilSuspension();
                    $extra .= '<br><small class="text-warning"><i class="fas fa-clock"></i> Gracia: ' . $daysLeft . ' días</small>';
                }
                
                // Failed payments
                if ($subscription->failed_payment_count > 0) {
                    $extra .= '<br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Fallos: ' . $subscription->failed_payment_count . '</small>';
                }
                
                // Trial info
                if ($subscription->is_trial && $subscription->trial_ends_at) {
                    $trialDays = $subscription->trial_ends_at->diffInDays(now());
                    $extra .= '<br><small class="text-info"><i class="fas fa-gift"></i> Trial: ' . $trialDays . ' días</small>';
                }
                
                return '<span class="badge ' . $badge['class'] . '">' . $badge['text'] . '</span>' . $extra;
            })
            ->addColumn('billing_info', function ($subscription) {
                $html = '<div>';
                
                if ($subscription->next_billing_date) {
                    $date = $subscription->next_billing_date;
                    $diffDays = $date->diffInDays(now());
                    $color = $diffDays <= 3 ? 'text-danger' : ($diffDays <= 7 ? 'text-warning' : 'text-success');
                    
                    $html .= '<strong class="' . $color . '">' . $date->format('d/m/Y') . '</strong><br>';
                    $html .= '<small class="text-muted">En ' . $diffDays . ' días</small>';
                } else {
                    $html .= '<span class="text-muted">N/A</span>';
                }
                
                if ($subscription->billing_cycle_count > 0) {
                    $html .= '<br><small class="text-info">Ciclo: ' . $subscription->billing_cycle_count . '</small>';
                }
                
                $html .= '</div>';
                return $html;
            })
            ->addColumn('revenue_info', function ($subscription) {
                $totalPaid = $subscription->getTotalAmountPaid();
                $html = '<div>';
                $html .= '<strong class="text-success">$' . number_format($totalPaid, 2) . '</strong><br>';
                $html .= '<small class="text-muted">Total pagado</small>';
                
                if ($subscription->failed_payment_count > 0) {
                    $html .= '<br><small class="text-danger">Fallos: ' . $subscription->failed_payment_count . '</small>';
                }
                
                $html .= '</div>';
                return $html;
            })
            ->addColumn('actions', function ($subscription) {
                return $this->getEnhancedActionButtons($subscription);
            })
            ->addColumn('created_formatted', function ($subscription) {
                return '<div>' .
                       '<strong>' . $subscription->created_at->format('d/m/Y') . '</strong><br>' .
                       '<small class="text-muted">' . $subscription->created_at->format('H:i') . '</small>' .
                       '</div>';
            })
            ->rawColumns(['tenant_info', 'plan_details', 'status_info', 'billing_info', 'revenue_info', 'actions', 'created_formatted'])
            ->make(true);
            
        } catch (\Exception $e) {
            \Log::error('Error in Developer SubscriptionController getData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error cargando suscripciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suspend subscription manually (enhanced)
     */
    public function suspend(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $subscription = Subscription::findOrFail($id);
            
            if (!$subscription->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La suscripción no está activa'
                ], 400);
            }
            
            // Use the enhanced suspend method from the model
            $suspended = $subscription->suspend($request->reason, 'manual-admin');
            
            if ($suspended) {
                // Handle PayPal suspension if needed
                if ($subscription->paypal_subscription_id) {
                    $result = $this->paypalService->suspendSubscription(
                        $subscription->paypal_subscription_id,
                        $request->reason
                    );
                    
                    if (!$result['success']) {
                        Log::warning('Failed to suspend PayPal subscription', [
                            'subscription_id' => $id,
                            'paypal_error' => $result['error'] ?? 'Unknown error'
                        ]);
                    }
                }
                
                Log::info('Subscription manually suspended', [
                    'subscription_id' => $id,
                    'tenant_id' => $subscription->tenant_id,
                    'reason' => $request->reason,
                    'suspended_by' => auth()->id()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Suscripción suspendida exitosamente'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se pudo suspender la suscripción'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al suspender: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivate subscription manually (enhanced)
     */
    public function reactivate(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $subscription = Subscription::findOrFail($id);
            
            if (!$subscription->canBeReactivated()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La suscripción no puede ser reactivada'
                ], 400);
            }
            
            // Use the enhanced reactivate method from the model
            $reactivated = $subscription->reactivate($request->reason, 'manual-admin');
            
            if ($reactivated) {
                // Handle PayPal reactivation if needed
                if ($subscription->paypal_subscription_id) {
                    $result = $this->paypalService->activateSubscription(
                        $subscription->paypal_subscription_id,
                        $request->reason
                    );
                    
                    if (!$result['success']) {
                        Log::warning('Failed to activate PayPal subscription', [
                            'subscription_id' => $id,
                            'paypal_error' => $result['error'] ?? 'Unknown error'
                        ]);
                    }
                }
                
                Log::info('Subscription manually reactivated', [
                    'subscription_id' => $id,
                    'tenant_id' => $subscription->tenant_id,
                    'reason' => $request->reason,
                    'reactivated_by' => auth()->id()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Suscripción reactivada exitosamente'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se pudo reactivar la suscripción'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Subscription $subscription)
    {
        try {
            if ($subscription->paypal_subscription_id) {
                $result = $this->paypalService->cancelSubscription(
                    $subscription->paypal_subscription_id,
                    'Cancelled by administrator'
                );
                
                if (!$result['success']) {
                    throw new \Exception('Failed to cancel PayPal subscription: ' . $result['error']);
                }
            }

            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => Carbon::now(),
                'cancelled_by' => 'admin'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Suscripción cancelada exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extend trial period
     */
    public function extendTrial(Request $request, Subscription $subscription)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:30'
        ]);

        try {
            if (!$subscription->is_trial) {
                throw new \Exception('Esta suscripción no está en período de trial.');
            }

            $currentTrialEnd = $subscription->trial_ends_at ?: Carbon::now();
            $newTrialEnd = $currentTrialEnd->addDays($request->days);

            $subscription->update([
                'trial_ends_at' => $newTrialEnd,
                'next_billing_date' => $newTrialEnd
            ]);

            return response()->json([
                'success' => true,
                'message' => "Trial extendido por {$request->days} días hasta {$newTrialEnd->format('d/m/Y H:i')}."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al extender el trial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change subscription plan
     */
    public function changePlan(Request $request, $id)
    {
        $request->validate([
            'new_plan' => 'required|in:basic,premium,enterprise,corporate',
            'reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        
        try {
            $subscription = Subscription::findOrFail($id);
            $oldPlan = $subscription->plan;
            $newPlan = $request->new_plan;
            
            // Get plan pricing
            $planPricing = $this->getPlanPricing($newPlan);
            
            // Update subscription
            $subscription->update([
                'plan' => $newPlan,
                'amount' => $planPricing['amount'],
                'plan_features' => $planPricing['features'],
            ]);
            
            // Log the change
            Log::info('Subscription plan changed', [
                'subscription_id' => $id,
                'tenant_id' => $subscription->tenant_id,
                'old_plan' => $oldPlan,
                'new_plan' => $newPlan,
                'reason' => $request->reason,
                'changed_by' => auth()->id()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Plan cambiado de {$oldPlan} a {$newPlan} exitosamente"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync subscription with PayPal
     */
    public function syncPayPal($id)
    {
        try {
            $subscription = Subscription::findOrFail($id);
            
            if (!$subscription->paypal_subscription_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Suscripción no tiene ID de PayPal'
                ], 400);
            }
            
            // Get subscription from PayPal
            $paypalResponse = $this->paypalService->getSubscription($subscription->paypal_subscription_id);
            
            if ($paypalResponse['success']) {
                $paypalData = $paypalResponse['data'];
                
                // Update local subscription with PayPal data
                $subscription->update([
                    'status' => $this->mapPayPalStatus($paypalData['status']),
                    'next_billing_date' => isset($paypalData['billing_info']['next_billing_time']) 
                        ? Carbon::parse($paypalData['billing_info']['next_billing_time']) 
                        : null,
                    'paypal_metadata' => $paypalData
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Sincronización exitosa con PayPal'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener información de PayPal'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en sincronización: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comprehensive subscription statistics
     */
    private function getSubscriptionStats(): array
    {
        return [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'suspended' => Subscription::where('status', 'suspended')->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
            'pending' => Subscription::where('status', 'pending')->count(),
            'trial' => Subscription::where('is_trial', true)->count(),
            'in_grace_period' => Subscription::whereNotNull('grace_period_ends_at')
                ->where('grace_period_ends_at', '>', now())
                ->count(),
            'failing_payments' => Subscription::where('failed_payment_count', '>', 0)->count()
        ];
    }

    /**
     * Get advanced business metrics
     */
    private function getBusinessMetrics(): array
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        
        // MRR (Monthly Recurring Revenue)
        $currentMRR = Subscription::where('status', 'active')
            ->where('is_trial', false)
            ->sum('amount');
            
        $lastMonthMRR = Subscription::where('status', 'active')
            ->where('is_trial', false)
            ->where('created_at', '<', $thisMonth)
            ->sum('amount');
            
        $mrrGrowth = $lastMonthMRR > 0 ? (($currentMRR - $lastMonthMRR) / $lastMonthMRR) * 100 : 0;
        
        // Churn Rate
        $activeStart = Subscription::where('created_at', '<', $thisMonth)->count();
        $churnedThisMonth = Subscription::where('status', 'cancelled')
            ->whereMonth('cancelled_at', now()->month)
            ->count();
        $churnRate = $activeStart > 0 ? ($churnedThisMonth / $activeStart) * 100 : 0;
        
        // ARPU (Average Revenue Per User)
        $totalRevenue = Subscription::where('status', 'active')->sum('amount');
        $activeUsers = Subscription::where('status', 'active')->count();
        $arpu = $activeUsers > 0 ? $totalRevenue / $activeUsers : 0;
        
        // Conversion Rate
        $conversionRate = $this->getConversionRate();
        
        return [
            'mrr' => $currentMRR,
            'mrr_growth' => round($mrrGrowth, 2),
            'arr' => $currentMRR * 12,
            'arpu' => round($arpu, 2),
            'churn_rate' => round($churnRate, 2),
            'conversion_rate' => $conversionRate,
            'ltv' => round($arpu > 0 && $churnRate > 0 ? $arpu / ($churnRate/100) : 0, 2),
            'monthly_growth_rate' => round($mrrGrowth, 2)
        ];
    }

    /**
     * Get recent subscription activity
     */
    private function getRecentActivity(): array
    {
        return Subscription::with(['tenant'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'tenant_name' => $subscription->tenant->name ?? 'N/A',
                    'action' => $this->getLastAction($subscription),
                    'timestamp' => $subscription->updated_at,
                    'status' => $subscription->status,
                    'plan' => $subscription->plan
                ];
            })
            ->toArray();
    }

    /**
     * Get chart data for dashboard
     */
    private function getChartData(): array
    {
        // Revenue chart (last 12 months)
        $revenueData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = SubscriptionPayment::where('status', 'completed')
                ->whereMonth('payment_date', $month->month)
                ->whereYear('payment_date', $month->year)
                ->sum('amount');
            
            $revenueData[] = [
                'month' => $month->format('M Y'),
                'revenue' => (float) $revenue
            ];
        }
        
        // Plan distribution
        $planDistribution = Subscription::where('status', 'active')
            ->groupBy('plan')
            ->selectRaw('plan, count(*) as count')
            ->get()
            ->map(function ($item) {
                return [
                    'plan' => ucfirst($item->plan),
                    'count' => $item->count
                ];
            });
            
        // Add default data if no subscriptions exist
        if ($planDistribution->isEmpty()) {
            $planDistribution = collect([
                ['plan' => 'Trial', 'count' => 0],
                ['plan' => 'Basic', 'count' => 0],
                ['plan' => 'Premium', 'count' => 0],
                ['plan' => 'Enterprise', 'count' => 0]
            ]);
        }
            
        // Status distribution
        $statusDistribution = Subscription::groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst($item->status),
                    'count' => $item->count
                ];
            });
            
        // Add default data if no subscriptions exist
        if ($statusDistribution->isEmpty()) {
            $statusDistribution = collect([
                ['status' => 'Active', 'count' => 0],
                ['status' => 'Suspended', 'count' => 0],
                ['status' => 'Cancelled', 'count' => 0],
                ['status' => 'Pending', 'count' => 0]
            ]);
        }

        return [
            'revenue' => collect($revenueData),
            'plans' => $planDistribution,
            'status' => $statusDistribution
        ];
    }

    /**
     * Get critical alerts that need attention
     */
    private function getCriticalAlerts(): array
    {
        $alerts = [];
        
        // Trials ending soon
        $trialEndingSoon = Subscription::where('is_trial', true)
            ->where('trial_ends_at', '<=', now()->addDays(3))
            ->where('trial_ends_at', '>=', now())
            ->count();
            
        if ($trialEndingSoon > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fas fa-clock',
                'message' => "{$trialEndingSoon} trials terminan en los próximos 3 días",
                'action_url' => route('developer.subscriptions.index', ['filter' => 'trial_ending'])
            ];
        }
        
        // Failed payments
        $failedPayments = Subscription::where('failed_payment_count', '>', 0)->count();
        if ($failedPayments > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fas fa-exclamation-triangle',
                'message' => "{$failedPayments} suscripciones con pagos fallidos",
                'action_url' => route('developer.subscriptions.index', ['filter' => 'failed_payments'])
            ];
        }
        
        // Suspended accounts
        $suspended = Subscription::where('status', 'suspended')->count();
        if ($suspended > 0) {
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fas fa-pause',
                'message' => "{$suspended} cuentas suspendidas",
                'action_url' => route('developer.subscriptions.index', ['filter' => 'suspended'])
            ];
        }
        
        return $alerts;
    }

    /**
     * Helper methods
     */
    private function getPlanPricing($plan): array
    {
        $pricing = [
            'basic' => [
                'amount' => 29.00, 
                'features' => [
                    'users' => 5, 
                    'lots_per_month' => 500,
                    'storage_gb' => 2,
                    'support' => 'email'
                ]
            ],
            'premium' => [
                'amount' => 79.00, 
                'features' => [
                    'users' => 25, 
                    'lots_per_month' => 2000,
                    'storage_gb' => 10,
                    'support' => 'priority'
                ]
            ],
            'enterprise' => [
                'amount' => 199.00, 
                'features' => [
                    'users' => 100, 
                    'lots_per_month' => 'unlimited',
                    'storage_gb' => 50,
                    'support' => '24/7'
                ]
            ],
            'corporate' => [
                'amount' => 0, 
                'features' => [
                    'users' => 'unlimited', 
                    'tenants' => 'unlimited',
                    'storage_gb' => 'unlimited',
                    'support' => 'dedicated'
                ]
            ]
        ];
        
        return $pricing[$plan] ?? $pricing['basic'];
    }

    private function mapPayPalStatus($paypalStatus): string
    {
        $statusMap = [
            'ACTIVE' => 'active',
            'SUSPENDED' => 'suspended',
            'CANCELLED' => 'cancelled',
            'EXPIRED' => 'expired'
        ];
        
        return $statusMap[$paypalStatus] ?? 'pending';
    }

    private function getConversionRate(): float
    {
        $totalTrials = Subscription::where('is_trial', true)->count();
        $convertedTrials = Subscription::where('is_trial', false)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('subscriptions as s2')
                    ->whereColumn('s2.tenant_id', 'subscriptions.tenant_id')
                    ->where('s2.is_trial', true);
            })->count();
            
        return $totalTrials > 0 ? ($convertedTrials / $totalTrials) * 100 : 0;
    }

    private function getLastAction($subscription): string
    {
        if ($subscription->reactivated_at && $subscription->reactivated_at->gt($subscription->suspended_at ?? Carbon::minValue())) {
            return 'Reactivada';
        }
        if ($subscription->suspended_at) {
            return 'Suspendida';
        }
        if ($subscription->cancelled_at) {
            return 'Cancelada';
        }
        
        return 'Creada';
    }

    /**
     * Generate enhanced action buttons
     */
    private function getEnhancedActionButtons($subscription): string
    {
        $actions = '<div class="btn-group btn-group-sm" role="group">';
        
        // View details
        $actions .= '<button class="btn btn-info btn-sm" onclick="viewSubscription(' . $subscription->id . ')" title="Ver Detalles">
            <i class="fas fa-eye"></i>
        </button>';
        
        // Edit/Change plan
        $actions .= '<button class="btn btn-warning btn-sm" onclick="editSubscription(' . $subscription->id . ')" title="Cambiar Plan">
            <i class="fas fa-edit"></i>
        </button>';
        
        // Suspend/Reactivate
        if ($subscription->isActive()) {
            $actions .= '<button class="btn btn-danger btn-sm" onclick="suspendSubscription(' . $subscription->id . ')" title="Suspender">
                <i class="fas fa-pause"></i>
            </button>';
        } elseif ($subscription->isSuspended() && $subscription->canBeReactivated()) {
            $actions .= '<button class="btn btn-success btn-sm" onclick="reactivateSubscription(' . $subscription->id . ')" title="Reactivar">
                <i class="fas fa-play"></i>
            </button>';
        }
        
        // Sync with PayPal
        if ($subscription->paypal_subscription_id) {
            $actions .= '<button class="btn btn-secondary btn-sm" onclick="syncWithPayPal(' . $subscription->id . ')" title="Sincronizar PayPal">
                <i class="fab fa-paypal"></i>
            </button>';
        }
        
        // Extend trial (if applicable)
        if ($subscription->is_trial && $subscription->trial_ends_at && $subscription->trial_ends_at->gt(now())) {
            $actions .= '<button class="btn btn-info btn-sm" onclick="extendTrial(' . $subscription->id . ')" title="Extender Trial">
                <i class="fas fa-calendar-plus"></i>
            </button>';
        }
        
        $actions .= '</div>';
        
        return $actions;
    }

    /**
     * Generate status badge HTML
     */
    private function getStatusBadge(string $status): string
    {
        $badges = [
            'active' => '<span class="badge badge-success badge-sm">Activa</span>',
            'suspended' => '<span class="badge badge-warning badge-sm">Suspendida</span>',
            'cancelled' => '<span class="badge badge-danger badge-sm">Cancelada</span>',
            'expired' => '<span class="badge badge-secondary badge-sm">Expirada</span>',
            'pending' => '<span class="badge badge-info badge-sm">Pendiente</span>',
        ];

        return $badges[$status] ?? '<span class="badge badge-secondary badge-sm">' . ucfirst($status) . '</span>';
    }

    /**
     * Generate plan badge HTML
     */
    private function getPlanBadge(string $plan): string
    {
        $badges = [
            'trial' => '<span class="badge badge-warning badge-sm">Trial</span>',
            'basic' => '<span class="badge badge-primary badge-sm">Basic</span>',
            'premium' => '<span class="badge badge-purple badge-sm">Premium</span>',
            'enterprise' => '<span class="badge badge-dark badge-sm">Enterprise</span>',
            'corporate' => '<span class="badge badge-secondary badge-sm">Corporate</span>',
        ];

        return $badges[$plan] ?? '<span class="badge badge-light badge-sm">' . ucfirst($plan) . '</span>';
    }

    /**
     * Generate action buttons HTML
     */
    private function getActionButtons(Subscription $subscription): string
    {
        $buttons = '<div class="btn-group btn-group-sm" role="group">';
        
        // View button
        $buttons .= '<a href="' . route('developer.subscriptions.show', $subscription) . '" class="btn btn-info btn-sm" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                     </a>';

        // Action buttons based on status
        if ($subscription->status === 'active') {
            $buttons .= '<button class="btn btn-warning btn-sm" onclick="suspendSubscription(' . $subscription->id . ')" title="Suspender">
                            <i class="fas fa-pause"></i>
                         </button>';
        } elseif ($subscription->status === 'suspended') {
            $buttons .= '<button class="btn btn-success btn-sm" onclick="activateSubscription(' . $subscription->id . ')" title="Activar">
                            <i class="fas fa-play"></i>
                         </button>';
        }

        if (in_array($subscription->status, ['active', 'suspended'])) {
            $buttons .= '<button class="btn btn-danger btn-sm" onclick="cancelSubscription(' . $subscription->id . ')" title="Cancelar">
                            <i class="fas fa-times"></i>
                         </button>';
        }

        if ($subscription->is_trial) {
            $buttons .= '<button class="btn btn-info btn-sm" onclick="extendTrial(' . $subscription->id . ')" title="Extender trial">
                            <i class="fas fa-calendar-plus"></i>
                         </button>';
        }

        $buttons .= '</div>';
        
        return $buttons;
    }
}
