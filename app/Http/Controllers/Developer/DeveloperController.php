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
        
        // Get system health
        $health = $this->getSystemHealth();
        
        return view('developer.dashboard', compact('metrics', 'health'));
    }

    /**
     * Get system metrics for dashboard.
     */
    private function getSystemMetrics()
    {
        return Cache::remember('developer_metrics', 300, function () {
            $totalUsers = User::count();
            $activeUsers = User::whereNull('suspended_at')->count();
            $suspendedUsers = User::whereNotNull('suspended_at')->count();
            $lastLogin = User::latest('updated_at')->first();
            
            return [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'suspended_users' => $suspendedUsers,
                'last_login' => $lastLogin ? $lastLogin->updated_at->diffForHumans() : 'Sin datos',
                'super_admins' => User::whereHas('roles', function ($q) {
                    $q->where('name', 'super_admin');
                })->count(),
                'total_lots' => \App\Models\Lot::count(),
                'total_sales' => \App\Models\Sale::count(),
                'total_payments' => \App\Models\Payment::count(),
            ];
        });
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
            return true;
        } catch (\Exception $e) {
            return false;
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
            
            return $value === 'ok';
        } catch (\Exception $e) {
            return false;
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
            
            return $percentage < 90; // Return true if usage is below 90%
        } catch (\Exception $e) {
            return false;
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
            
            return $failed <= 10 && $jobs <= 100; // Return true if queues are healthy
        } catch (\Exception $e) {
            return true; // If queue tables don't exist, consider it ok
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
            return response()->json([
                'success' => true,
                'message' => 'All caches cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing caches: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Run maintenance mode.
     */
    public function maintenance(Request $request)
    {
        try {
            $isDown = app()->isDownForMaintenance();
            
            if ($isDown) {
                \Artisan::call('up');
                return response()->json([
                    'success' => true,
                    'message' => 'Maintenance mode disabled'
                ]);
            } else {
                \Artisan::call('down', [
                    '--message' => 'System maintenance in progress',
                    '--retry' => 60,
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Maintenance mode enabled'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error managing maintenance mode: ' . $e->getMessage()
            ]);
        }
    }
}