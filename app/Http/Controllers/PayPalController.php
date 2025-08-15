<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\PayPalService;
use App\Models\PayPalWebhookLog;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayPalController extends Controller
{
    private PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    /**
     * Handle PayPal webhook events
     */
    public function webhook(Request $request): Response
    {
        $receivedAt = Carbon::now();
        $webhookLog = null;

        try {
            // Get the raw POST data
            $rawBody = $request->getContent();
            $webhookData = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON in PayPal webhook', [
                    'json_error' => json_last_error_msg(),
                    'raw_body' => $rawBody
                ]);
                return response('Invalid JSON', 400);
            }

            // Create webhook log entry
            $webhookLog = PayPalWebhookLog::create([
                'webhook_id' => $webhookData['id'] ?? null,
                'event_type' => $webhookData['event_type'] ?? 'unknown',
                'event_version' => $webhookData['event_version'] ?? null,
                'resource_type' => $webhookData['resource_type'] ?? null,
                'resource_id' => $webhookData['resource']['id'] ?? null,
                'payload' => $webhookData,
                'status' => 'received',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'received_at' => $receivedAt,
            ]);

            Log::info('PayPal webhook received and logged', [
                'webhook_log_id' => $webhookLog->id,
                'event_type' => $webhookData['event_type'] ?? 'unknown',
                'resource_id' => $webhookData['resource']['id'] ?? null
            ]);

            // TODO: Implement webhook signature verification for production
            // This is important for security but requires additional setup
            
            // Process the webhook
            $result = $this->paypalService->processWebhook($webhookData, $webhookLog);

            if ($result['success']) {
                $webhookLog->markAsProcessed($result['message'] ?? 'Successfully processed');
                
                Log::info('PayPal webhook processed successfully', [
                    'webhook_log_id' => $webhookLog->id,
                    'event_type' => $webhookData['event_type'] ?? 'unknown',
                    'result' => $result
                ]);
                return response('OK', 200);
            } else {
                $webhookLog->markAsFailed($result['error'] ?? 'Unknown processing error');
                
                Log::error('Failed to process PayPal webhook', [
                    'webhook_log_id' => $webhookLog->id,
                    'event_type' => $webhookData['event_type'] ?? 'unknown',
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
                return response('Processing failed', 500);
            }

        } catch (\Exception $e) {
            if ($webhookLog) {
                $webhookLog->markAsFailed('Exception: ' . $e->getMessage());
            }
            
            Log::error('Exception processing PayPal webhook', [
                'webhook_log_id' => $webhookLog?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response('Server error', 500);
        }
    }

    /**
     * Test webhook endpoint for development
     */
    public function testWebhook(Request $request): Response
    {
        if (!app()->environment('local')) {
            return response('Not available in production', 403);
        }

        // Sample webhook data for testing
        $testWebhookData = [
            'id' => 'WH-' . uniqid(),
            'event_version' => '1.0',
            'create_time' => now()->toISOString(),
            'resource_type' => 'subscription',
            'event_type' => $request->get('event_type', 'BILLING.SUBSCRIPTION.ACTIVATED'),
            'summary' => 'A billing subscription was activated',
            'resource' => [
                'id' => $request->get('subscription_id', 'I-TEST123'),
                'status' => 'ACTIVE',
                'start_time' => now()->toISOString(),
                'billing_info' => [
                    'next_billing_time' => now()->addMonth()->toISOString()
                ]
            ]
        ];

        Log::info('Processing test webhook', $testWebhookData);

        try {
            $result = $this->paypalService->processWebhook($testWebhookData);
            
            return response()->json([
                'success' => true,
                'test_data' => $testWebhookData,
                'processing_result' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'test_data' => $testWebhookData
            ], 500);
        }
    }
}
