<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PayPalService;
use App\Models\PayPalWebhookLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class PayPalController extends Controller
{
    private PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    /**
     * Display PayPal configuration dashboard
     */
    public function index()
    {
        // Get current environment with fallback
        $environment = config('paypal.mode') ?: env('PAYPAL_ENVIRONMENT', 'sandbox');
        
        // Get credentials with fallback strategy
        $sandboxClientId = config('paypal.sandbox.client_id') ?: env('PAYPAL_SANDBOX_CLIENT_ID', '');
        $sandboxClientSecret = config('paypal.sandbox.client_secret') ?: env('PAYPAL_SANDBOX_CLIENT_SECRET', '');
        $liveClientId = config('paypal.live.client_id') ?: env('PAYPAL_LIVE_CLIENT_ID', '');
        $liveClientSecret = config('paypal.live.client_secret') ?: env('PAYPAL_LIVE_CLIENT_SECRET', '');
        
        // Current environment credentials
        $currentClientId = $environment === 'sandbox' ? $sandboxClientId : $liveClientId;
        $currentClientSecret = $environment === 'sandbox' ? $sandboxClientSecret : $liveClientSecret;
        
        $config = [
            'environment' => $environment,
            'client_id' => $currentClientId ? substr($currentClientId, 0, 20) . '...' : 'Not configured',
            'client_secret' => $currentClientSecret ? '••••••••••••••••' : 'Not configured',
            'webhook_id' => config('paypal.webhook_id', 'Not configured'),
            'sandbox_configured' => !empty($sandboxClientId) && !empty($sandboxClientSecret),
            'live_configured' => !empty($liveClientId) && !empty($liveClientSecret),
            'api_keys_configured' => !empty($currentClientId) && !empty($currentClientSecret),
        ];

        // Test connection status
        $connectionStatus = $this->testConnectionStatus();

        // Get plans synchronization status
        $plansStatus = $this->getPlansStatus();

        // Get recent webhook events
        $recentWebhooks = $this->getRecentWebhooks();

        return view('developer.paypal.index', compact(
            'config',
            'connectionStatus',
            'plansStatus',
            'recentWebhooks'
        ));
    }

    /**
     * Show configuration form
     */
    public function config()
    {
        // Read directly from environment variables as fallback
        $config = [
            'environment' => env('PAYPAL_ENVIRONMENT', 'sandbox'),
            'sandbox' => [
                'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID', ''),
                'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET', ''),
                'webhook_id' => env('PAYPAL_SANDBOX_WEBHOOK_ID', ''),
            ],
            'live' => [
                'client_id' => env('PAYPAL_LIVE_CLIENT_ID', ''),
                'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET', ''),
                'webhook_id' => env('PAYPAL_LIVE_WEBHOOK_ID', ''),
            ],
        ];

        // Try to get from config first, fallback to env
        $config['environment'] = config('paypal.mode', $config['environment']);
        $config['sandbox']['client_id'] = config('paypal.sandbox.client_id', $config['sandbox']['client_id']);
        $config['sandbox']['client_secret'] = config('paypal.sandbox.client_secret', $config['sandbox']['client_secret']);
        $config['sandbox']['webhook_id'] = config('paypal.sandbox.webhook_id', $config['sandbox']['webhook_id']);
        $config['live']['client_id'] = config('paypal.live.client_id', $config['live']['client_id']);
        $config['live']['client_secret'] = config('paypal.live.client_secret', $config['live']['client_secret']);
        $config['live']['webhook_id'] = config('paypal.live.webhook_id', $config['live']['webhook_id']);

        return view('developer.paypal.config', compact('config'));
    }

    /**
     * Update PayPal configuration
     */
    public function updateConfig(Request $request)
    {
        $request->validate([
            'environment' => 'required|in:sandbox,live',
            'sandbox_client_id' => 'nullable|string',
            'sandbox_client_secret' => 'nullable|string',
            'sandbox_webhook_id' => 'nullable|string',
            'live_client_id' => 'nullable|string',
            'live_client_secret' => 'nullable|string',
            'live_webhook_id' => 'nullable|string',
        ]);

        try {
            // Update .env file
            $this->updateEnvFile([
                'PAYPAL_ENVIRONMENT' => $request->environment,
                'PAYPAL_SANDBOX_CLIENT_ID' => $request->sandbox_client_id ?: '',
                'PAYPAL_SANDBOX_CLIENT_SECRET' => $request->sandbox_client_secret ?: '',
                'PAYPAL_SANDBOX_WEBHOOK_ID' => $request->sandbox_webhook_id ?: '',
                'PAYPAL_LIVE_CLIENT_ID' => $request->live_client_id ?: '',
                'PAYPAL_LIVE_CLIENT_SECRET' => $request->live_client_secret ?: '',
                'PAYPAL_LIVE_WEBHOOK_ID' => $request->live_webhook_id ?: '',
            ]);

            // Clear config cache
            Artisan::call('config:clear');

            // Refresh PayPal service configuration
            try {
                $this->paypalService->refreshConfig();
            } catch (\Exception $e) {
                Log::warning('PayPal service refresh failed after config update', [
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('developer.paypal.config')
                ->with('success', 'Configuración de PayPal actualizada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error updating PayPal config', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al actualizar la configuración: ' . $e->getMessage());
        }
    }

    /**
     * Sync plans with PayPal
     */
    public function syncPlans(Request $request)
    {
        try {
            // Run the sync command
            Artisan::call('paypal:sync-plans', [
                '--force' => $request->boolean('force'),
            ]);

            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Planes sincronizados exitosamente.',
                'output' => $output
            ]);

        } catch (\Exception $e) {
            Log::error('Error syncing PayPal plans', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar planes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test PayPal connection
     */
    public function testConnection(Request $request)
    {
        try {
            // Refresh configuration first in case environment was changed
            $this->paypalService->refreshConfig();
            
            $result = $this->paypalService->testConnection();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conexión exitosa con PayPal.',
                    'data' => $result['data'] ?? null
                ]);
            } else {
                throw new \Exception($result['error'] ?? 'Connection failed');
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display webhook events
     */
    public function webhooks()
    {
        // Get webhook URL for display
        $webhookUrl = url('/paypal/webhook');
        
        // Get webhook configuration
        $webhookConfig = [
            'url' => $webhookUrl,
            'events' => [
                'BILLING.SUBSCRIPTION.CREATED',
                'BILLING.SUBSCRIPTION.ACTIVATED',
                'BILLING.SUBSCRIPTION.UPDATED',
                'BILLING.SUBSCRIPTION.EXPIRED',
                'BILLING.SUBSCRIPTION.CANCELLED',
                'BILLING.SUBSCRIPTION.SUSPENDED',
                'BILLING.SUBSCRIPTION.PAYMENT.FAILED',
                'PAYMENT.SALE.COMPLETED',
                'PAYMENT.SALE.REFUNDED',
            ]
        ];

        return view('developer.paypal.webhooks', compact('webhookConfig'));
    }

    /**
     * Test webhook endpoint
     */
    public function testWebhook(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'subscription_id' => 'nullable|string',
        ]);

        try {
            // Create test webhook data
            $testData = [
                'id' => 'WH-TEST-' . uniqid(),
                'event_type' => $request->event_type,
                'resource' => [
                    'id' => $request->subscription_id ?: 'I-TEST' . strtoupper(uniqid()),
                    'status' => 'ACTIVE',
                ]
            ];

            // Process the test webhook
            $result = $this->paypalService->processWebhook($testData);

            return response()->json([
                'success' => true,
                'message' => 'Webhook de prueba procesado.',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update .env file
     */
    private function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envPath, $envContent);
    }

    /**
     * Test connection status
     */
    private function testConnectionStatus(): array
    {
        try {
            $result = $this->paypalService->testConnection();
            $environment = config('paypal.mode') ?: env('PAYPAL_ENVIRONMENT', 'sandbox');
            return [
                'connected' => $result['success'],
                'message' => $result['success'] ? 'Conexión exitosa' : ($result['error'] ?? 'No conectado'),
                'environment' => $environment,
            ];
        } catch (\Exception $e) {
            $environment = config('paypal.mode') ?: env('PAYPAL_ENVIRONMENT', 'sandbox');
            return [
                'connected' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'environment' => $environment,
            ];
        }
    }

    /**
     * Get plans synchronization status
     */
    private function getPlansStatus(): array
    {
        // Try to get plans from config, with fallback to direct file inclusion
        $plans = config('paypal.plans', []);
        
        // If config returns empty, try loading directly from file
        if (empty($plans)) {
            try {
                $paypalConfig = include(config_path('paypal.php'));
                $plans = $paypalConfig['plans'] ?? [];
            } catch (\Exception $e) {
                // If file loading fails, return empty array
                $plans = [];
            }
        }
        
        $status = [];

        foreach ($plans as $planKey => $planConfig) {
            $status[$planKey] = [
                'name' => $planConfig['name'] ?? ucfirst($planKey),
                'price' => $planConfig['amount'] ?? 0,
                'synced' => !empty($planConfig['paypal_plan_id']), // Check if plan has PayPal ID
                'local_id' => $planConfig['paypal_plan_id'] ?? null,
            ];
        }

        return $status;
    }

    /**
     * Get recent webhook events (mock for now)
     */
    private function getRecentWebhooks(): array
    {
        // In production, this would fetch from a webhook_logs table
        return [];
    }

    /**
     * Retry failed webhook
     */
    public function retryWebhook(PayPalWebhookLog $webhookLog)
    {
        if (!$webhookLog->canRetry()) {
            return response()->json([
                'success' => false,
                'message' => 'Este webhook no puede ser reintentado.'
            ], 422);
        }

        try {
            // Reset webhook status
            $webhookLog->resetForRetry();

            // Reprocess the webhook
            $result = $this->paypalService->processWebhook($webhookLog->payload, $webhookLog);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook reintentado exitosamente.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al reintentar webhook: ' . ($result['error'] ?? 'Error desconocido')
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reintentar webhook: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export webhook logs
     */
    public function exportWebhooks(Request $request)
    {
        $query = PayPalWebhookLog::with(['subscription', 'tenant']);

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('event_type') && $request->event_type !== '') {
            $query->where('event_type', $request->event_type);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->where('received_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->where('received_at', '<=', $request->date_to . ' 23:59:59');
        }

        $webhooks = $query->orderBy('received_at', 'desc')->get();

        // Generate CSV
        $filename = 'paypal_webhooks_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($webhooks) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Webhook ID',
                'Tipo de Evento',
                'Estado',
                'Suscripción',
                'Tenant',
                'Recibido',
                'Procesado',
                'Duración (s)',
                'Notas'
            ]);

            // CSV data
            foreach ($webhooks as $webhook) {
                fputcsv($file, [
                    $webhook->id,
                    $webhook->webhook_id,
                    $webhook->formatted_event_type,
                    ucfirst($webhook->status),
                    $webhook->subscription ? '#' . $webhook->subscription->id : '-',
                    $webhook->tenant ? $webhook->tenant->name : ($webhook->subscription?->tenant?->name ?? '-'),
                    $webhook->received_at->format('d/m/Y H:i:s'),
                    $webhook->processed_at ? $webhook->processed_at->format('d/m/Y H:i:s') : '-',
                    $webhook->processing_duration ?? '-',
                    $webhook->processing_notes ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get webhook details
     */
    public function webhookDetails(PayPalWebhookLog $webhookLog)
    {
        return response()->json([
            'webhook' => [
                'id' => $webhookLog->id,
                'webhook_id' => $webhookLog->webhook_id,
                'event_type' => $webhookLog->event_type,
                'formatted_event_type' => $webhookLog->formatted_event_type,
                'status' => $webhookLog->status,
                'status_badge' => $webhookLog->status_badge,
                'resource_type' => $webhookLog->resource_type,
                'resource_id' => $webhookLog->resource_id,
                'received_at' => $webhookLog->received_at->format('d/m/Y H:i:s'),
                'processed_at' => $webhookLog->processed_at ? $webhookLog->processed_at->format('d/m/Y H:i:s') : null,
                'processing_duration' => $webhookLog->processing_duration,
                'processing_notes' => $webhookLog->processing_notes,
                'ip_address' => $webhookLog->ip_address,
                'user_agent' => $webhookLog->user_agent,
                'payload' => $webhookLog->payload,
                'subscription' => $webhookLog->subscription ? [
                    'id' => $webhookLog->subscription->id,
                    'plan' => $webhookLog->subscription->plan,
                    'status' => $webhookLog->subscription->status,
                    'amount' => $webhookLog->subscription->amount,
                ] : null,
                'tenant' => $webhookLog->tenant ? [
                    'id' => $webhookLog->tenant->id,
                    'name' => $webhookLog->tenant->name,
                    'status' => $webhookLog->tenant->status,
                ] : null,
            ]
        ]);
    }
}