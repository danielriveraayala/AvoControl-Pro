<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\PaymentLog;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class PayPalService
{
    private Client $client;
    private string $mode;
    private string $baseUrl;
    private array $config;
    private ?string $accessToken = null;
    private ?Carbon $tokenExpiry = null;

    public function __construct()
    {
        $this->mode = config('paypal.mode', 'sandbox');
        $this->baseUrl = $this->mode === 'sandbox' 
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
            
        $this->config = [
            'client_id' => $this->mode === 'sandbox' 
                ? config('paypal.sandbox.client_id') 
                : config('paypal.live.client_id'),
            'client_secret' => $this->mode === 'sandbox' 
                ? config('paypal.sandbox.client_secret') 
                : config('paypal.live.client_secret'),
        ];

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);
    }

    /**
     * Get PayPal access token
     */
    private function getAccessToken(): ?string
    {
        // Return cached token if still valid
        if ($this->accessToken && $this->tokenExpiry && $this->tokenExpiry->isFuture()) {
            return $this->accessToken;
        }

        try {
            $response = $this->client->post('/v1/oauth2/token', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Accept-Language' => 'en_US',
                ],
                'auth' => [$this->config['client_id'], $this->config['client_secret']],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            $this->accessToken = $data['access_token'];
            $this->tokenExpiry = Carbon::now()->addSeconds($data['expires_in'] - 60); // 60s buffer
            
            $this->logPayPalAction('token_acquired', 'info', 'PayPal access token acquired successfully', [
                'expires_in' => $data['expires_in'],
                'token_type' => $data['token_type']
            ]);

            return $this->accessToken;
        } catch (RequestException $e) {
            $this->logPayPalAction('token_failed', 'error', 'Failed to acquire PayPal access token', [
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            
            return null;
        }
    }

    /**
     * Make authenticated request to PayPal API
     */
    private function makeRequest(string $method, string $endpoint, array $data = null): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'Failed to get access token'];
        }

        try {
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$token}",
                    'PayPal-Request-Id' => (string) Str::uuid(), // Idempotency
                ]
            ];

            if ($data) {
                $options['json'] = $data;
            }

            $response = $this->client->request($method, $endpoint, $options);
            $responseData = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'data' => $responseData,
                'status_code' => $response->getStatusCode()
            ];
        } catch (RequestException $e) {
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;
            $errorData = $responseBody ? json_decode($responseBody, true) : null;

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_data' => $errorData,
                'status_code' => $e->hasResponse() ? $e->getResponse()->getStatusCode() : null
            ];
        }
    }

    /**
     * Create PayPal product for subscription plans
     */
    public function createProduct(string $name, string $description, string $type = 'SERVICE'): array
    {
        $data = [
            'name' => $name,
            'description' => $description,
            'type' => $type,
            'category' => 'SOFTWARE',
            'image_url' => config('app.url') . '/images/logo.png',
            'home_url' => config('app.url')
        ];

        $result = $this->makeRequest('POST', '/v1/catalogs/products', $data);

        if ($result['success']) {
            $this->logPayPalAction('product_created', 'info', 'PayPal product created successfully', [
                'product_id' => $result['data']['id'],
                'product_name' => $name,
                'status_code' => $result['status_code']
            ], $data, $result['data']);

            return [
                'success' => true,
                'product_id' => $result['data']['id'],
                'data' => $result['data']
            ];
        } else {
            $this->logPayPalAction('product_creation_failed', 'error', 'Failed to create PayPal product', [
                'product_name' => $name,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ], $data);

            return [
                'success' => false,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ];
        }
    }

    /**
     * Create PayPal subscription plan
     */
    public function createPlan(string $productId, string $planId, array $planData): array
    {
        $data = [
            'product_id' => $productId,
            'name' => $planData['name'],
            'description' => $planData['description'],
            'status' => 'ACTIVE',
            'billing_cycles' => [
                [
                    'frequency' => [
                        'interval_unit' => strtoupper($planData['interval_unit']), // DAY, WEEK, MONTH, YEAR
                        'interval_count' => $planData['interval_count'] ?? 1
                    ],
                    'tenure_type' => 'REGULAR',
                    'sequence' => 1,
                    'total_cycles' => 0, // Infinite
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => (string)$planData['amount'],
                            'currency_code' => $planData['currency'] ?? 'USD'
                        ]
                    ]
                ]
            ],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee' => [
                    'value' => '0',
                    'currency_code' => $planData['currency'] ?? 'USD'
                ],
                'setup_fee_failure_action' => 'CONTINUE',
                'payment_failure_threshold' => 3
            ],
            'taxes' => [
                'percentage' => '0',
                'inclusive' => false
            ]
        ];

        // Add trial period if specified
        if (isset($planData['trial_days']) && $planData['trial_days'] > 0) {
            // Insert trial cycle at the beginning
            array_unshift($data['billing_cycles'], [
                'frequency' => [
                    'interval_unit' => 'DAY',
                    'interval_count' => $planData['trial_days']
                ],
                'tenure_type' => 'TRIAL',
                'sequence' => 1,
                'total_cycles' => 1,
                'pricing_scheme' => [
                    'fixed_price' => [
                        'value' => '0',
                        'currency_code' => $planData['currency'] ?? 'USD'
                    ]
                ]
            ]);
            
            // Update regular cycle sequence
            $data['billing_cycles'][1]['sequence'] = 2;
        }

        $result = $this->makeRequest('POST', '/v1/billing/plans', $data);

        if ($result['success']) {
            $this->logPayPalAction('plan_created', 'info', 'PayPal subscription plan created successfully', [
                'plan_id' => $result['data']['id'],
                'plan_name' => $planData['name'],
                'amount' => $planData['amount'],
                'currency' => $planData['currency'] ?? 'USD',
                'status_code' => $result['status_code']
            ], $data, $result['data']);

            return [
                'success' => true,
                'plan_id' => $result['data']['id'],
                'data' => $result['data']
            ];
        } else {
            $this->logPayPalAction('plan_creation_failed', 'error', 'Failed to create PayPal subscription plan', [
                'plan_name' => $planData['name'],
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ], $data);

            return [
                'success' => false,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ];
        }
    }

    /**
     * Create subscription for tenant
     */
    public function createSubscription(Tenant $tenant, string $planId, array $subscriptionData): array
    {
        $data = [
            'plan_id' => $planId,
            'start_time' => Carbon::now()->addMinute()->toISOString(), // Start 1 minute from now
            'quantity' => '1',
            'subscriber' => [
                'name' => [
                    'given_name' => $subscriptionData['subscriber']['first_name'] ?? $tenant->name,
                    'surname' => $subscriptionData['subscriber']['last_name'] ?? ''
                ],
                'email_address' => $subscriptionData['subscriber']['email'] ?? $tenant->email
            ],
            'application_context' => [
                'brand_name' => config('app.name'),
                'locale' => 'en-US',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'SUBSCRIBE_NOW',
                'payment_method' => [
                    'payer_selected' => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED'
                ],
                'return_url' => route('subscription.success'),
                'cancel_url' => route('subscription.cancelled')
            ],
            'custom_id' => $tenant->uuid, // Use tenant UUID for reference
        ];

        $result = $this->makeRequest('POST', '/v1/billing/subscriptions', $data);

        if ($result['success']) {
            // Create local subscription record
            $subscription = Subscription::create([
                'uuid' => Str::uuid(),
                'tenant_id' => $tenant->id,
                'user_id' => auth()->id() ?? $tenant->users()->first()->id,
                'paypal_subscription_id' => $result['data']['id'],
                'paypal_plan_id' => $planId,
                'plan' => $subscriptionData['plan'] ?? 'basic',
                'amount' => $subscriptionData['amount'] ?? 29.00,
                'currency' => $subscriptionData['currency'] ?? 'USD',
                'interval' => $subscriptionData['interval'] ?? 'monthly',
                'status' => 'pending',
                'is_trial' => $subscriptionData['is_trial'] ?? true,
                'trial_starts_at' => Carbon::now(),
                'trial_ends_at' => Carbon::now()->addDays($subscriptionData['trial_days'] ?? 7),
                'paypal_metadata' => $result['data']
            ]);

            $this->logPayPalAction('subscription_created', 'info', 'PayPal subscription created successfully', [
                'subscription_id' => $result['data']['id'],
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'plan' => $subscriptionData['plan'] ?? 'basic',
                'amount' => $subscriptionData['amount'] ?? 29.00,
                'status_code' => $result['status_code']
            ], $data, $result['data'], $subscription->id, $tenant->id);

            return [
                'success' => true,
                'subscription_id' => $result['data']['id'],
                'approval_url' => $this->getApprovalUrl($result['data']['links']),
                'local_subscription' => $subscription,
                'data' => $result['data']
            ];
        } else {
            $this->logPayPalAction('subscription_creation_failed', 'error', 'Failed to create PayPal subscription', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ], $data, null, null, $tenant->id);

            return [
                'success' => false,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ];
        }
    }

    /**
     * Get subscription details from PayPal
     */
    public function getSubscription(string $subscriptionId): array
    {
        $result = $this->makeRequest('GET', "/v1/billing/subscriptions/{$subscriptionId}");

        if ($result['success']) {
            $this->logPayPalAction('subscription_retrieved', 'info', 'PayPal subscription details retrieved', [
                'subscription_id' => $subscriptionId,
                'status' => $result['data']['status'],
                'status_code' => $result['status_code']
            ], null, $result['data']);

            return [
                'success' => true,
                'data' => $result['data']
            ];
        } else {
            $this->logPayPalAction('subscription_retrieval_failed', 'error', 'Failed to retrieve PayPal subscription', [
                'subscription_id' => $subscriptionId,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ]);

            return [
                'success' => false,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ];
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(string $subscriptionId, string $reason = 'User requested cancellation'): array
    {
        $data = [
            'reason' => $reason
        ];

        $result = $this->makeRequest('POST', "/v1/billing/subscriptions/{$subscriptionId}/cancel", $data);

        if ($result['success']) {
            // Update local subscription
            $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
            if ($subscription) {
                $subscription->update([
                    'status' => 'cancelled',
                    'cancelled_at' => Carbon::now(),
                    'cancellation_reason' => $reason,
                    'cancelled_by' => 'user'
                ]);
            }

            $this->logPayPalAction('subscription_cancelled', 'info', 'PayPal subscription cancelled successfully', [
                'subscription_id' => $subscriptionId,
                'reason' => $reason,
                'status_code' => $result['status_code']
            ], $data, $result['data'] ?? [], $subscription?->id, $subscription?->tenant_id);

            return [
                'success' => true,
                'data' => $result['data'] ?? []
            ];
        } else {
            $this->logPayPalAction('subscription_cancellation_failed', 'error', 'Failed to cancel PayPal subscription', [
                'subscription_id' => $subscriptionId,
                'reason' => $reason,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ], $data);

            return [
                'success' => false,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ];
        }
    }

    /**
     * Suspend subscription
     */
    public function suspendSubscription(string $subscriptionId, string $reason = 'Payment failure'): array
    {
        $data = [
            'reason' => $reason
        ];

        $result = $this->makeRequest('POST', "/v1/billing/subscriptions/{$subscriptionId}/suspend", $data);

        if ($result['success']) {
            // Update local subscription
            $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
            if ($subscription) {
                $subscription->update([
                    'status' => 'suspended',
                    'suspended_at' => Carbon::now()
                ]);
            }

            $this->logPayPalAction('subscription_suspended', 'info', 'PayPal subscription suspended successfully', [
                'subscription_id' => $subscriptionId,
                'reason' => $reason,
                'status_code' => $result['status_code']
            ], $data, $result['data'] ?? [], $subscription?->id, $subscription?->tenant_id);

            return [
                'success' => true,
                'data' => $result['data'] ?? []
            ];
        } else {
            $this->logPayPalAction('subscription_suspension_failed', 'error', 'Failed to suspend PayPal subscription', [
                'subscription_id' => $subscriptionId,
                'reason' => $reason,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ], $data);

            return [
                'success' => false,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ];
        }
    }

    /**
     * Activate suspended subscription
     */
    public function activateSubscription(string $subscriptionId, string $reason = 'Reactivated by user'): array
    {
        $data = [
            'reason' => $reason
        ];

        $result = $this->makeRequest('POST', "/v1/billing/subscriptions/{$subscriptionId}/activate", $data);

        if ($result['success']) {
            // Update local subscription
            $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
            if ($subscription) {
                $subscription->update([
                    'status' => 'active',
                    'suspended_at' => null
                ]);
            }

            $this->logPayPalAction('subscription_activated', 'info', 'PayPal subscription activated successfully', [
                'subscription_id' => $subscriptionId,
                'reason' => $reason,
                'status_code' => $result['status_code']
            ], $data, $result['data'] ?? [], $subscription?->id, $subscription?->tenant_id);

            return [
                'success' => true,
                'data' => $result['data'] ?? []
            ];
        } else {
            $this->logPayPalAction('subscription_activation_failed', 'error', 'Failed to activate PayPal subscription', [
                'subscription_id' => $subscriptionId,
                'reason' => $reason,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ], $data);

            return [
                'success' => false,
                'error' => $result['error'],
                'error_data' => $result['error_data'] ?? null ?? null
            ];
        }
    }

    /**
     * Get approval URL from PayPal response links
     */
    private function getApprovalUrl(array $links): ?string
    {
        foreach ($links as $link) {
            if ($link['rel'] === 'approve') {
                return $link['href'];
            }
        }
        return null;
    }

    /**
     * Test connection to PayPal API
     */
    public function testConnection(): array
    {
        try {
            // Try to get access token
            $token = $this->getAccessToken();
            
            if (!$token) {
                return [
                    'success' => false,
                    'error' => 'No se pudo obtener el token de acceso. Verifica las credenciales.'
                ];
            }

            // Make a simple API call to verify connection
            $response = $this->client->get('/v1/notifications/webhooks', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                return [
                    'success' => true,
                    'data' => [
                        'environment' => $this->mode,
                        'connected_at' => now()->toISOString(),
                    ]
                ];
            }

            return [
                'success' => false,
                'error' => 'Conexión establecida pero respuesta inesperada'
            ];

        } catch (\Exception $e) {
            Log::error('PayPal connection test failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process webhook from PayPal
     */
    public function processWebhook(array $webhookData, $webhookLog = null): array
    {
        $eventType = $webhookData['event_type'] ?? null;
        $resource = $webhookData['resource'] ?? [];
        
        $this->logPayPalAction('webhook_received', 'info', "PayPal webhook received: {$eventType}", [
            'event_type' => $eventType,
            'webhook_id' => $webhookData['id'] ?? null,
            'create_time' => $webhookData['create_time'] ?? null
        ], null, $webhookData);

        try {
            switch ($eventType) {
                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                    return $this->handleSubscriptionActivated($resource, $webhookLog);
                    
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                    return $this->handleSubscriptionCancelled($resource, $webhookLog);
                    
                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                    return $this->handleSubscriptionSuspended($resource, $webhookLog);
                    
                case 'BILLING.SUBSCRIPTION.PAYMENT.COMPLETED':
                    return $this->handlePaymentCompleted($resource, $webhookLog);
                    
                case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                    return $this->handlePaymentFailed($resource, $webhookLog);
                    
                default:
                    $this->logPayPalAction('webhook_unhandled', 'warning', "Unhandled webhook event type: {$eventType}", [
                        'event_type' => $eventType
                    ], null, $webhookData);
                    
                    if ($webhookLog) {
                        $webhookLog->markAsIgnored("Event type '{$eventType}' not handled");
                    }
                    
                    return ['success' => true, 'message' => 'Webhook received but not processed'];
            }
        } catch (Exception $e) {
            $this->logPayPalAction('webhook_processing_error', 'error', 'Error processing PayPal webhook', [
                'event_type' => $eventType,
                'error' => $e->getMessage()
            ], null, $webhookData);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle subscription activated webhook
     */
    private function handleSubscriptionActivated(array $resource, $webhookLog = null): array
    {
        $subscriptionId = $resource['id'];
        $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => 'active',
                'starts_at' => Carbon::parse($resource['start_time'] ?? now()),
                'next_billing_date' => isset($resource['billing_info']['next_billing_time']) 
                    ? Carbon::parse($resource['billing_info']['next_billing_time']) 
                    : now()->addMonth()
            ]);

            // Update webhook log with subscription and tenant info
            if ($webhookLog) {
                $webhookLog->update([
                    'subscription_id' => $subscription->id,
                    'tenant_id' => $subscription->tenant_id,
                ]);
            }

            $this->logPayPalAction('subscription_activated', 'info', 'Subscription activated via webhook', [
                'subscription_id' => $subscriptionId,
                'local_subscription_id' => $subscription->id
            ], null, $resource, $subscription->id, $subscription->tenant_id);
            
            return ['success' => true, 'message' => 'Subscription activated successfully'];
        }

        // If subscription not found, still return success (might be a different system)
        if ($webhookLog) {
            $webhookLog->markAsIgnored('Subscription not found in local database');
        }
        
        return ['success' => true, 'message' => 'Subscription not found locally'];
    }

    /**
     * Handle subscription cancelled webhook
     */
    private function handleSubscriptionCancelled(array $resource): array
    {
        $subscriptionId = $resource['id'];
        $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => Carbon::now(),
                'cancelled_by' => 'paypal'
            ]);

            $this->logPayPalAction('subscription_cancelled', 'info', 'Subscription cancelled via webhook', [
                'subscription_id' => $subscriptionId,
                'local_subscription_id' => $subscription->id
            ], null, $resource, $subscription->id, $subscription->tenant_id);
        }

        return ['success' => true, 'message' => 'Subscription cancelled'];
    }

    /**
     * Handle subscription suspended webhook
     */
    private function handleSubscriptionSuspended(array $resource): array
    {
        $subscriptionId = $resource['id'];
        $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => 'suspended',
                'suspended_at' => Carbon::now()
            ]);

            $this->logPayPalAction('subscription_suspended', 'info', 'Subscription suspended via webhook', [
                'subscription_id' => $subscriptionId,
                'local_subscription_id' => $subscription->id
            ], null, $resource, $subscription->id, $subscription->tenant_id);
        }

        return ['success' => true, 'message' => 'Subscription suspended'];
    }

    /**
     * Handle payment completed webhook
     */
    private function handlePaymentCompleted(array $resource): array
    {
        $subscriptionId = $resource['billing_agreement_id'] ?? $resource['subscription_id'] ?? null;
        $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if ($subscription) {
            // Create payment record
            SubscriptionPayment::create([
                'uuid' => Str::uuid(),
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'paypal_payment_id' => $resource['id'],
                'amount' => $resource['amount']['total'] ?? $subscription->amount,
                'currency' => $resource['amount']['currency'] ?? $subscription->currency,
                'type' => $subscription->billing_cycle_count === 0 ? 'initial' : 'recurring',
                'status' => 'completed',
                'billing_cycle' => $subscription->billing_cycle_count + 1,
                'payment_date' => Carbon::parse($resource['create_time'] ?? now()),
                'completed_at' => Carbon::parse($resource['update_time'] ?? now()),
                'paypal_response' => $resource
            ]);

            // Update subscription
            $subscription->increment('billing_cycle_count');
            $subscription->update([
                'failed_payment_count' => 0, // Reset failed payment count
                'next_billing_date' => isset($resource['billing_info']['next_billing_time'])
                    ? Carbon::parse($resource['billing_info']['next_billing_time'])
                    : now()->addMonth()
            ]);

            $this->logPayPalAction('payment_completed', 'info', 'Payment completed via webhook', [
                'subscription_id' => $subscriptionId,
                'payment_id' => $resource['id'],
                'amount' => $resource['amount']['total'] ?? $subscription->amount,
                'billing_cycle' => $subscription->billing_cycle_count
            ], null, $resource, $subscription->id, $subscription->tenant_id);
        }

        return ['success' => true, 'message' => 'Payment completed'];
    }

    /**
     * Handle payment failed webhook
     */
    private function handlePaymentFailed(array $resource): array
    {
        $subscriptionId = $resource['billing_agreement_id'] ?? $resource['subscription_id'] ?? null;
        $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if ($subscription) {
            // Create failed payment record
            SubscriptionPayment::create([
                'uuid' => Str::uuid(),
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'paypal_payment_id' => $resource['id'],
                'amount' => $resource['amount']['total'] ?? $subscription->amount,
                'currency' => $resource['amount']['currency'] ?? $subscription->currency,
                'type' => 'retry',
                'status' => 'failed',
                'billing_cycle' => $subscription->billing_cycle_count + 1,
                'payment_date' => Carbon::parse($resource['create_time'] ?? now()),
                'failed_at' => Carbon::parse($resource['update_time'] ?? now()),
                'failure_reason' => $resource['reason_code'] ?? 'Payment failed',
                'failure_details' => $resource['reason_description'] ?? 'Payment failed without specific reason',
                'paypal_response' => $resource
            ]);

            // Update subscription
            $subscription->increment('failed_payment_count');
            
            // Start grace period if not already started
            if ($subscription->failed_payment_count === 1) {
                $subscription->update([
                    'grace_period_ends_at' => Carbon::now()->addDays($subscription->grace_period_days)
                ]);
            }

            // Suspend if too many failures
            if ($subscription->failed_payment_count >= 3) {
                $subscription->update(['status' => 'suspended']);
            }

            $this->logPayPalAction('payment_failed', 'warning', 'Payment failed via webhook', [
                'subscription_id' => $subscriptionId,
                'payment_id' => $resource['id'],
                'failed_count' => $subscription->failed_payment_count,
                'reason' => $resource['reason_code'] ?? 'Unknown'
            ], null, $resource, $subscription->id, $subscription->tenant_id);
        }

        return ['success' => true, 'message' => 'Payment failure processed'];
    }

    /**
     * Log PayPal actions for debugging and auditing
     */
    private function logPayPalAction(
        string $event,
        string $level,
        string $message,
        array $context = [],
        array $requestData = null,
        $responseData = null,
        int $subscriptionId = null,
        int $tenantId = null
    ): void {
        try {
            PaymentLog::create([
                'uuid' => Str::uuid(),
                'subscription_id' => $subscriptionId,
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'type' => 'api_request',
                'event' => $event,
                'level' => $level,
                'message' => $message,
                'context' => $context,
                'request_data' => $requestData,
                'response_data' => $responseData,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'session_id' => session()->getId(),
                'occurred_at' => Carbon::now()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create payment log', [
                'event' => $event,
                'error' => $e->getMessage(),
                'context' => $context
            ]);
        }

        // Also log to Laravel log
        Log::channel('daily')->{$level}($message, $context);
    }

    /**
     * Get available subscription plans
     */
    public function getSubscriptionPlans(): array
    {
        return config('paypal.plans');
    }

    /**
     * Get current configuration
     */
    public function getConfig(): array
    {
        return [
            'mode' => $this->mode,
            'client_id' => $this->config['client_id'],
            'base_url' => $this->baseUrl,
            'webhook_url' => route('paypal.webhook'),
            'return_url' => route('subscription.success'),
            'cancel_url' => route('subscription.cancelled')
        ];
    }
}