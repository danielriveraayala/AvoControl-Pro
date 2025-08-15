<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Models\User;
use App\Mail\SubscriptionReportEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;

class GenerateSubscriptionReports extends Command
{
    protected $signature = 'subscriptions:generate-reports {--period=monthly : Report period (daily, weekly, monthly)} {--email : Send report via email to super admins}';
    protected $description = 'Generate comprehensive subscription reports for super admins';

    private array $reportData = [];

    public function handle()
    {
        $period = $this->option('period');
        $sendEmail = $this->option('email');

        $this->info("📊 Generando reporte de suscripciones ({$period})...");
        $this->newLine();

        // Generate report data
        $this->generateReportData($period);

        // Display summary
        $this->displaySummary($period);

        // Generate detailed report file
        $reportFile = $this->generateReportFile($period);

        if ($sendEmail) {
            $this->sendReportEmail($reportFile, $period);
        }

        $this->newLine();
        $this->info('✅ Reporte de suscripciones generado exitosamente!');
        
        if ($reportFile) {
            $this->info("📄 Archivo guardado en: storage/reports/{$reportFile}");
        }
    }

    private function generateReportData(string $period): void
    {
        $dateRange = $this->getDateRange($period);

        // Overall subscription metrics
        $this->reportData['overview'] = [
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'trial_subscriptions' => Subscription::where('is_trial', true)->where('status', 'active')->count(),
            'paid_subscriptions' => Subscription::where('is_trial', false)->where('status', 'active')->count(),
            'suspended_subscriptions' => Subscription::where('status', 'suspended')->count(),
            'cancelled_subscriptions' => Subscription::where('status', 'cancelled')->count(),
        ];

        // Revenue metrics
        $this->reportData['revenue'] = [
            'mrr' => $this->calculateMRR(),
            'arr' => $this->calculateARR(),
            'period_revenue' => $this->calculatePeriodRevenue($dateRange),
            'average_revenue_per_user' => $this->calculateARPU(),
        ];

        // Plan distribution
        $this->reportData['plans'] = [
            'by_plan' => Subscription::selectRaw('plan, COUNT(*) as count')
                ->groupBy('plan')
                ->get()
                ->pluck('count', 'plan')
                ->toArray(),
            'revenue_by_plan' => Subscription::where('status', 'active')
                ->where('is_trial', false)
                ->selectRaw('plan, SUM(amount) as revenue')
                ->groupBy('plan')
                ->get()
                ->pluck('revenue', 'plan')
                ->toArray(),
        ];

        // Period-specific metrics
        $this->reportData['period_metrics'] = [
            'new_subscriptions' => Subscription::whereBetween('created_at', $dateRange)->count(),
            'cancelled_subscriptions' => Subscription::whereBetween('cancelled_at', $dateRange)
                ->whereNotNull('cancelled_at')
                ->count(),
            'trial_conversions' => $this->calculateTrialConversions($dateRange),
            'churn_rate' => $this->calculateChurnRate($dateRange),
        ];

        // Payment metrics
        $this->reportData['payments'] = [
            'successful_payments' => SubscriptionPayment::where('status', 'completed')
                ->whereBetween('completed_at', $dateRange)
                ->count(),
            'failed_payments' => SubscriptionPayment::where('status', 'failed')
                ->whereBetween('failed_at', $dateRange)
                ->count(),
            'total_payment_amount' => SubscriptionPayment::where('status', 'completed')
                ->whereBetween('completed_at', $dateRange)
                ->sum('amount'),
        ];

        // Tenant metrics
        $this->reportData['tenants'] = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'suspended_tenants' => Tenant::where('status', 'suspended')->count(),
            'new_tenants' => Tenant::whereBetween('created_at', $dateRange)->count(),
        ];

        // Top performing subscriptions
        $this->reportData['top_performers'] = [
            'highest_revenue' => Subscription::where('is_trial', false)
                ->where('status', 'active')
                ->with('tenant')
                ->orderBy('amount', 'desc')
                ->limit(10)
                ->get()
                ->toArray(),
            'longest_active' => Subscription::where('status', 'active')
                ->with('tenant')
                ->orderBy('starts_at', 'asc')
                ->limit(10)
                ->get()
                ->toArray(),
        ];
    }

    private function getDateRange(string $period): array
    {
        $now = Carbon::now();
        
        return match($period) {
            'daily' => [$now->subDay()->startOfDay(), $now->subDay()->endOfDay()],
            'weekly' => [$now->subWeek()->startOfWeek(), $now->subWeek()->endOfWeek()],
            'monthly' => [$now->subMonth()->startOfMonth(), $now->subMonth()->endOfMonth()],
            default => [$now->subMonth()->startOfMonth(), $now->subMonth()->endOfMonth()],
        };
    }

    private function calculateMRR(): float
    {
        return Subscription::where('status', 'active')
            ->where('is_trial', false)
            ->sum('amount');
    }

    private function calculateARR(): float
    {
        return $this->calculateMRR() * 12;
    }

    private function calculatePeriodRevenue(array $dateRange): float
    {
        return SubscriptionPayment::where('status', 'completed')
            ->whereBetween('completed_at', $dateRange)
            ->sum('amount');
    }

    private function calculateARPU(): float
    {
        $activeSubscriptions = Subscription::where('status', 'active')
            ->where('is_trial', false)
            ->count();
            
        if ($activeSubscriptions === 0) {
            return 0;
        }

        return $this->calculateMRR() / $activeSubscriptions;
    }

    private function calculateTrialConversions(array $dateRange): array
    {
        $trialsStarted = Subscription::where('is_trial', true)
            ->whereBetween('trial_starts_at', $dateRange)
            ->count();

        $trialsConverted = Subscription::where('is_trial', false)
            ->whereBetween('starts_at', $dateRange)
            ->whereNotNull('trial_ends_at')
            ->count();

        $conversionRate = $trialsStarted > 0 ? ($trialsConverted / $trialsStarted) * 100 : 0;

        return [
            'trials_started' => $trialsStarted,
            'trials_converted' => $trialsConverted,
            'conversion_rate' => round($conversionRate, 2),
        ];
    }

    private function calculateChurnRate(array $dateRange): float
    {
        $startOfPeriod = Subscription::where('status', 'active')
            ->where('created_at', '<', $dateRange[0])
            ->count();

        $churned = Subscription::whereBetween('cancelled_at', $dateRange)
            ->whereNotNull('cancelled_at')
            ->count();

        return $startOfPeriod > 0 ? round(($churned / $startOfPeriod) * 100, 2) : 0;
    }

    private function displaySummary(string $period): void
    {
        $this->info("📈 Resumen del Reporte ({$period})");
        $this->newLine();

        // Overview metrics
        $overview = $this->reportData['overview'];
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total Suscripciones', number_format($overview['total_subscriptions'])],
                ['Suscripciones Activas', number_format($overview['active_subscriptions'])],
                ['Trials Activos', number_format($overview['trial_subscriptions'])],
                ['Suscripciones Pagadas', number_format($overview['paid_subscriptions'])],
                ['Suspendidas', number_format($overview['suspended_subscriptions'])],
                ['Canceladas', number_format($overview['cancelled_subscriptions'])],
            ]
        );

        // Revenue metrics
        $revenue = $this->reportData['revenue'];
        $this->info('💰 Métricas de Revenue');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['MRR (Monthly Recurring Revenue)', '$' . number_format($revenue['mrr'], 2)],
                ['ARR (Annual Recurring Revenue)', '$' . number_format($revenue['arr'], 2)],
                ['Revenue del Período', '$' . number_format($revenue['period_revenue'], 2)],
                ['ARPU (Average Revenue Per User)', '$' . number_format($revenue['average_revenue_per_user'], 2)],
            ]
        );

        // Plan distribution
        $plans = $this->reportData['plans']['by_plan'];
        if (!empty($plans)) {
            $this->info('📦 Distribución por Planes');
            $planData = [];
            foreach ($plans as $plan => $count) {
                $planData[] = [ucfirst($plan), number_format($count)];
            }
            $this->table(['Plan', 'Suscripciones'], $planData);
        }

        // Period metrics
        $period_metrics = $this->reportData['period_metrics'];
        $this->info("📊 Métricas del Período ({$period})");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Nuevas Suscripciones', number_format($period_metrics['new_subscriptions'])],
                ['Suscripciones Canceladas', number_format($period_metrics['cancelled_subscriptions'])],
                ['Trials Convertidos', number_format($period_metrics['trial_conversions']['trials_converted'])],
                ['Tasa de Conversión', $period_metrics['trial_conversions']['conversion_rate'] . '%'],
                ['Tasa de Churn', $period_metrics['churn_rate'] . '%'],
            ]
        );
    }

    private function generateReportFile(string $period): ?string
    {
        try {
            $filename = "subscription_report_{$period}_" . now()->format('Y-m-d_H-i-s') . '.json';
            
            $reportContent = [
                'generated_at' => now()->toISOString(),
                'period' => $period,
                'date_range' => $this->getDateRange($period),
                'data' => $this->reportData,
                'summary' => $this->generateSummaryText($period),
            ];

            // Ensure reports directory exists
            if (!Storage::disk('local')->exists('reports')) {
                Storage::disk('local')->makeDirectory('reports');
            }

            Storage::disk('local')->put("reports/{$filename}", json_encode($reportContent, JSON_PRETTY_PRINT));

            return $filename;

        } catch (\Exception $e) {
            $this->error("Error generando archivo de reporte: " . $e->getMessage());
            return null;
        }
    }

    private function generateSummaryText(string $period): string
    {
        $revenue = $this->reportData['revenue'];
        $overview = $this->reportData['overview'];
        
        return "Reporte de Suscripciones ({$period})\n" .
               "=====================================\n\n" .
               "MRR: $" . number_format($revenue['mrr'], 2) . "\n" .
               "ARR: $" . number_format($revenue['arr'], 2) . "\n" .
               "Suscripciones Activas: " . number_format($overview['active_subscriptions']) . "\n" .
               "Trials Activos: " . number_format($overview['trial_subscriptions']) . "\n" .
               "Tasa de Churn: " . $this->reportData['period_metrics']['churn_rate'] . "%\n";
    }

    private function sendReportEmail(?string $reportFile, string $period): void
    {
        try {
            // Get all super admin users
            $superAdmins = User::whereHas('roles', function($query) {
                $query->where('name', 'super_admin');
            })->get();

            if ($superAdmins->isEmpty()) {
                $this->warn('⚠️ No se encontraron usuarios super_admin para enviar el reporte.');
                return;
            }

            foreach ($superAdmins as $admin) {
                Mail::to($admin->email)->send(new SubscriptionReportEmail($this->reportData, $period, $reportFile));
                $this->info("📧 Reporte enviado a: {$admin->email}");
            }

        } catch (\Exception $e) {
            $this->error("Error enviando emails: " . $e->getMessage());
        }
    }
}