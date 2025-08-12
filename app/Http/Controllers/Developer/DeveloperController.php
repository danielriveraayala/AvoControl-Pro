<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Lot;
use App\Models\Sale;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DeveloperController extends Controller
{
    /**
     * Display the developer dashboard.
     */
    public function index()
    {
        // Get system metrics
        $metrics = $this->getSystemMetrics();
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity();
        
        // Get system health
        $systemHealth = $this->getSystemHealth();
        
        return view('developer.dashboard', compact('metrics', 'recentActivity', 'systemHealth'));
    }

    /**
     * Get system metrics for dashboard.
     */
    private function getSystemMetrics()
    {
        return Cache::remember('developer_metrics', 300, function () {
            return [
                'users' => [
                    'total' => User::count(),
                    'super_admins' => User::whereHas('roles', function ($q) {
                        $q->where('name', 'super_admin');
                    })->count(),
                    'admins' => User::whereHas('roles', function ($q) {
                        $q->where('name', 'admin');
                    })->count(),
                    'active_today' => User::where('updated_at', '>=', Carbon::today())->count(),
                ],
                'business' => [
                    'total_lots' => Lot::count(),
                    'total_sales' => Sale::count(),
                    'total_revenue' => Payment::where('type', 'cliente')->sum('amount'),
                    'total_payments' => Payment::where('type', 'proveedor')->sum('amount'),
                ],
                'database' => [
                    'size' => $this->getDatabaseSize(),
                    'tables' => DB::select('SHOW TABLES'),
                    'connections' => DB::select('SHOW STATUS WHERE Variable_name = "Threads_connected"')[0]->Value ?? 0,
                ],
                'storage' => [
                    'disk_usage' => $this->getDiskUsage(),
                    'cache_size' => $this->getCacheSize(),
                ],
            ];
        });
    }

    /**
     * Get recent system activity.
     */
    private function getRecentActivity()
    {
        return [
            'recent_users' => User::latest()->take(5)->get(),
            'recent_lots' => Lot::latest()->take(5)->get(),
            'recent_sales' => Sale::latest()->take(5)->get(),
            'recent_payments' => Payment::latest()->take(5)->get(),
        ];
    }

    /**
     * Get system health indicators.
     */
    private function getSystemHealth()
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
            'queue' => $this->checkQueueHealth(),
        ];
    }

    /**
     * Check database health.
     */
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check cache health.
     */
    private function checkCacheHealth()
    {
        try {
            Cache::put('health_check', 'ok', 1);
            $value = Cache::get('health_check');
            Cache::forget('health_check');
            
            if ($value === 'ok') {
                return ['status' => 'healthy', 'message' => 'Cache system OK'];
            }
            return ['status' => 'warning', 'message' => 'Cache may not be working properly'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check storage health.
     */
    private function checkStorageHealth()
    {
        try {
            $free = disk_free_space(storage_path());
            $total = disk_total_space(storage_path());
            $used = $total - $free;
            $percentage = round(($used / $total) * 100, 2);
            
            if ($percentage > 90) {
                return ['status' => 'error', 'message' => "Disk usage critical: {$percentage}%"];
            } elseif ($percentage > 70) {
                return ['status' => 'warning', 'message' => "Disk usage high: {$percentage}%"];
            }
            
            return ['status' => 'healthy', 'message' => "Disk usage normal: {$percentage}%"];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check queue health.
     */
    private function checkQueueHealth()
    {
        try {
            $jobs = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            
            if ($failed > 10) {
                return ['status' => 'error', 'message' => "Too many failed jobs: {$failed}"];
            } elseif ($jobs > 100) {
                return ['status' => 'warning', 'message' => "Queue backlog: {$jobs} pending jobs"];
            }
            
            return ['status' => 'healthy', 'message' => "Queue OK: {$jobs} pending, {$failed} failed"];
        } catch (\Exception $e) {
            return ['status' => 'info', 'message' => 'Queue tables not configured'];
        }
    }

    /**
     * Get database size.
     */
    private function getDatabaseSize()
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $result = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.tables 
                WHERE table_schema = ?
            ", [$dbName]);
            
            return $result[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get disk usage.
     */
    private function getDiskUsage()
    {
        try {
            $storagePath = storage_path();
            $free = disk_free_space($storagePath);
            $total = disk_total_space($storagePath);
            
            return [
                'free_gb' => round($free / 1024 / 1024 / 1024, 2),
                'total_gb' => round($total / 1024 / 1024 / 1024, 2),
                'used_gb' => round(($total - $free) / 1024 / 1024 / 1024, 2),
                'percentage' => round((($total - $free) / $total) * 100, 2),
            ];
        } catch (\Exception $e) {
            return [
                'free_gb' => 0,
                'total_gb' => 0,
                'used_gb' => 0,
                'percentage' => 0,
            ];
        }
    }

    /**
     * Get cache size.
     */
    private function getCacheSize()
    {
        try {
            $cachePath = storage_path('framework/cache');
            $size = 0;
            
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($cachePath)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
            
            return round($size / 1024 / 1024, 2); // MB
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Show system logs.
     */
    public function logs(Request $request)
    {
        $logFile = $request->get('file', 'laravel.log');
        $logs = [];
        
        try {
            $path = storage_path("logs/{$logFile}");
            if (file_exists($path)) {
                $logs = array_slice(file($path), -100); // Last 100 lines
                $logs = array_reverse($logs);
            }
        } catch (\Exception $e) {
            $logs = ['Error reading log file: ' . $e->getMessage()];
        }
        
        return view('developer.logs', compact('logs', 'logFile'));
    }

    /**
     * Clear system caches.
     */
    public function clearCache()
    {
        try {
            \Artisan::call('optimize:clear');
            return redirect()->route('developer.index')
                ->with('success', 'All caches cleared successfully');
        } catch (\Exception $e) {
            return redirect()->route('developer.index')
                ->with('error', 'Error clearing caches: ' . $e->getMessage());
        }
    }

    /**
     * Run maintenance mode.
     */
    public function maintenance(Request $request)
    {
        $action = $request->get('action', 'status');
        
        try {
            if ($action === 'enable') {
                \Artisan::call('down', [
                    '--message' => 'System maintenance in progress',
                    '--retry' => 60,
                ]);
                return redirect()->route('developer.index')
                    ->with('success', 'Maintenance mode enabled');
            } elseif ($action === 'disable') {
                \Artisan::call('up');
                return redirect()->route('developer.index')
                    ->with('success', 'Maintenance mode disabled');
            }
        } catch (\Exception $e) {
            return redirect()->route('developer.index')
                ->with('error', 'Error managing maintenance mode: ' . $e->getMessage());
        }
        
        return redirect()->route('developer.index');
    }
}