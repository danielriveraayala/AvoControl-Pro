<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    /**
     * Display billing dashboard with metrics
     */
    public function index()
    {
        // Calculate MRR (Monthly Recurring Revenue)
        $mrr = Subscription::where('status', 'active')
            ->where('is_trial', false)
            ->sum('amount');

        // Calculate ARR (Annual Recurring Revenue)
        $arr = $mrr * 12;

        // Customer counts
        $totalCustomers = Tenant::count();
        $payingCustomers = Subscription::where('status', 'active')
            ->where('is_trial', false)
            ->count();
        $trialCustomers = Subscription::where('is_trial', true)
            ->where('status', 'active')
            ->count();

        // Churn rate (last 30 days)
        $cancelledLast30Days = Subscription::where('status', 'cancelled')
            ->where('cancelled_at', '>=', Carbon::now()->subDays(30))
            ->count();
        $activeStartOfPeriod = Subscription::where('created_at', '<=', Carbon::now()->subDays(30))->count();
        $churnRate = $activeStartOfPeriod > 0 ? ($cancelledLast30Days / $activeStartOfPeriod) * 100 : 0;

        // Revenue by plan
        $revenueByPlan = Subscription::where('status', 'active')
            ->where('is_trial', false)
            ->select('plan', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('plan')
            ->get();

        // Monthly revenue trend (last 6 months)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $revenue = SubscriptionPayment::where('status', 'completed')
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount');
            
            $monthlyRevenue[] = [
                'month' => $date->format('M Y'),
                'revenue' => $revenue
            ];
        }

        return view('developer.billing.index', compact(
            'mrr',
            'arr',
            'totalCustomers',
            'payingCustomers',
            'trialCustomers',
            'churnRate',
            'revenueByPlan',
            'monthlyRevenue'
        ));
    }

    /**
     * Display detailed analytics (placeholder)
     */
    public function analytics()
    {
        return view('developer.billing.analytics');
    }

    /**
     * Display MRR report (placeholder)
     */
    public function mrrReport()
    {
        return view('developer.billing.mrr-report');
    }

    /**
     * Display churn analysis (placeholder)
     */
    public function churnAnalysis()
    {
        return view('developer.billing.churn-analysis');
    }

    /**
     * Display payment logs (placeholder)
     */
    public function paymentLogs(Request $request)
    {
        return view('developer.billing.payment-logs');
    }
}
