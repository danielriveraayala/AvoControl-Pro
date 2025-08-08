<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        // Get dashboard metrics
        $metrics = $this->reportService->getDashboardMetrics();
        
        // Get recent sales
        $recentSales = Sale::with('customer')
            ->latest('sale_date')
            ->take(5)
            ->get();

        return view('dashboard', compact('metrics', 'recentSales'));
    }
}