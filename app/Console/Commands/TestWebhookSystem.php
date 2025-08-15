<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PayPalService;
use App\Models\PayPalWebhookLog;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TestWebhookSystem extends Command
{
    protected $signature = 'paypal:test-webhooks';
    protected $description = 'Test PayPal webhook processing system';

    private PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        parent::__construct();
        $this->paypalService = $paypalService;
    }

    public function handle()
    {
        $this->info('🚀 Iniciando pruebas del sistema de webhooks PayPal...');
        $this->newLine();

        // Test 1: Database connectivity
        $this->testDatabaseConnectivity();
        
        // Test 2: Test webhook log creation
        $this->testWebhookLogCreation();
        
        // Test 3: Test webhook processing
        $this->testWebhookProcessing();
        
        // Test 4: Test webhook retry functionality
        $this->testWebhookRetry();
        
        // Test 5: Show statistics
        $this->showWebhookStatistics();
        
        $this->newLine();
        $this->info('✅ Todas las pruebas del sistema de webhooks completadas!');
    }

    private function testDatabaseConnectivity()
    {
        $this->info('📊 Test 1: Conectividad de base de datos');
        
        try {
            $count = PayPalWebhookLog::count();
            $this->info("   ✓ Conexión a tabla paypal_webhook_logs exitosa ({$count} registros)");
            
            $subscriptionCount = Subscription::count();
            $this->info("   ✓ Conexión a tabla subscriptions exitosa ({$subscriptionCount} registros)");
            
            $tenantCount = Tenant::count();
            $this->info("   ✓ Conexión a tabla tenants exitosa ({$tenantCount} registros)");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error de conectividad: " . $e->getMessage());
            return false;
        }
        
        $this->newLine();
        return true;
    }

    private function testWebhookLogCreation()
    {
        $this->info('🔗 Test 2: Creación de logs de webhook');
        
        try {
            $testLog = PayPalWebhookLog::create([
                'webhook_id' => 'WH-TEST-' . uniqid(),
                'event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED',
                'resource_type' => 'subscription',
                'resource_id' => 'I-TEST123',
                'payload' => [
                    'id' => 'WH-TEST123',
                    'event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED',
                    'resource' => [
                        'id' => 'I-TEST123',
                        'status' => 'ACTIVE'
                    ]
                ],
                'status' => 'received',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PayPal-Webhook-Test',
                'received_at' => now(),
            ]);

            $this->info("   ✓ Webhook log creado exitosamente (ID: {$testLog->id})");
            $this->info("   ✓ Event type: {$testLog->formatted_event_type}");
            $this->info("   ✓ Status badge: " . strip_tags($testLog->status_badge));
            
            // Test model methods
            $this->info("   ✓ Can retry: " . ($testLog->canRetry() ? 'No' : 'Si'));
            
            // Clean up
            $testLog->delete();
            $this->info("   ✓ Test webhook log eliminado");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error creando webhook log: " . $e->getMessage());
            return false;
        }
        
        $this->newLine();
        return true;
    }

    private function testWebhookProcessing()
    {
        $this->info('⚙️ Test 3: Procesamiento de webhooks');
        
        $testCases = [
            [
                'id' => 'WH-TEST-ACTIVATION',
                'event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED',
                'resource' => [
                    'id' => 'I-TEST-SUBSCRIPTION',
                    'status' => 'ACTIVE',
                    'start_time' => now()->toISOString(),
                ]
            ],
            [
                'id' => 'WH-TEST-PAYMENT',
                'event_type' => 'BILLING.SUBSCRIPTION.PAYMENT.COMPLETED',
                'resource' => [
                    'id' => 'PAY-TEST123',
                    'billing_agreement_id' => 'I-TEST-SUBSCRIPTION',
                    'amount' => [
                        'total' => '29.00',
                        'currency' => 'USD'
                    ]
                ]
            ],
            [
                'id' => 'WH-TEST-UNKNOWN',
                'event_type' => 'UNKNOWN.EVENT.TYPE',
                'resource' => [
                    'id' => 'UNKNOWN-123'
                ]
            ]
        ];

        foreach ($testCases as $testCase) {
            try {
                $result = $this->paypalService->processWebhook($testCase);
                
                if ($result['success']) {
                    $this->info("   ✓ Webhook {$testCase['event_type']} procesado: {$result['message']}");
                } else {
                    $this->warn("   ⚠️ Webhook {$testCase['event_type']} no procesado: {$result['error']}");
                }
                
            } catch (\Exception $e) {
                $this->error("   ❌ Error procesando {$testCase['event_type']}: " . $e->getMessage());
            }
        }
        
        $this->newLine();
        return true;
    }

    private function testWebhookRetry()
    {
        $this->info('🔄 Test 4: Funcionalidad de reintentos');
        
        try {
            // Create a failed webhook log
            $failedLog = PayPalWebhookLog::create([
                'webhook_id' => 'WH-RETRY-TEST',
                'event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED',
                'payload' => [
                    'id' => 'WH-RETRY-TEST',
                    'event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED',
                    'resource' => ['id' => 'I-RETRY-TEST']
                ],
                'status' => 'failed',
                'processing_notes' => 'Test failure for retry',
                'ip_address' => '127.0.0.1',
                'received_at' => now(),
                'processed_at' => now(),
            ]);

            $this->info("   ✓ Webhook fallido creado para prueba (ID: {$failedLog->id})");
            
            // Test if it can be retried
            if ($failedLog->canRetry()) {
                $this->info("   ✓ Webhook puede ser reintentado");
                
                // Reset for retry
                $failedLog->resetForRetry();
                $this->info("   ✓ Webhook reseteado para reintento");
                $this->info("   ✓ Nuevo status: {$failedLog->fresh()->status}");
                
                // Process again
                $result = $this->paypalService->processWebhook($failedLog->payload, $failedLog);
                $this->info("   ✓ Reintento procesado: " . ($result['success'] ? 'Exitoso' : 'Fallido'));
            } else {
                $this->warn("   ⚠️ Webhook no puede ser reintentado");
            }
            
            // Clean up
            $failedLog->delete();
            $this->info("   ✓ Test webhook eliminado");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error en test de reintentos: " . $e->getMessage());
            return false;
        }
        
        $this->newLine();
        return true;
    }

    private function showWebhookStatistics()
    {
        $this->info('📈 Test 5: Estadísticas del sistema');
        
        try {
            $stats = PayPalWebhookLog::getStats();
            
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Total de Webhooks', number_format($stats['total'])],
                    ['Procesados', number_format($stats['processed'])],
                    ['Fallidos', number_format($stats['failed'])],
                    ['Pendientes', number_format($stats['pending'])],
                    ['Hoy', number_format($stats['today'])],
                    ['Esta Semana', number_format($stats['this_week'])],
                ]
            );
            
            // Get recent webhooks count
            $recentCount = PayPalWebhookLog::getRecent(10)->count();
            $this->info("   ✓ Webhooks recientes disponibles: {$recentCount}");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error obteniendo estadísticas: " . $e->getMessage());
            return false;
        }
        
        return true;
    }
}