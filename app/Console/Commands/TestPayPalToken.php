<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class TestPayPalToken extends Command
{
    protected $signature = 'paypal:test-token';
    protected $description = 'Test PayPal token acquisition directly';

    public function handle()
    {
        $this->info('🧪 Testing PayPal Token Acquisition...');
        $this->newLine();

        // Get credentials from environment
        $environment = env('PAYPAL_ENVIRONMENT', 'sandbox');
        $clientId = env('PAYPAL_SANDBOX_CLIENT_ID');
        $clientSecret = env('PAYPAL_SANDBOX_CLIENT_SECRET');

        if ($environment === 'live') {
            $clientId = env('PAYPAL_LIVE_CLIENT_ID');
            $clientSecret = env('PAYPAL_LIVE_CLIENT_SECRET');
        }

        $this->info("Environment: {$environment}");
        $this->info("Client ID: " . ($clientId ? substr($clientId, 0, 20) . '...' : 'NOT SET'));
        $this->info("Client Secret: " . ($clientSecret ? 'SET' : 'NOT SET'));
        $this->newLine();

        if (!$clientId || !$clientSecret) {
            $this->error('❌ PayPal credentials not configured for ' . $environment . ' environment');
            return 1;
        }

        // Determine API URL
        $apiUrl = $environment === 'sandbox' 
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        $this->info("API URL: {$apiUrl}");
        $this->newLine();

        // Test token acquisition
        try {
            $client = new Client();
            
            $this->info('🔄 Requesting access token...');
            
            $response = $client->post($apiUrl . '/v1/oauth2/token', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Accept-Language' => 'en_US',
                ],
                'auth' => [$clientId, $clientSecret],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if ($statusCode === 200 && isset($data['access_token'])) {
                $this->info('✅ SUCCESS! PayPal token acquired successfully');
                $this->newLine();
                $this->info("Token Type: {$data['token_type']}");
                $this->info("Expires In: {$data['expires_in']} seconds");
                $this->info("Access Token: " . substr($data['access_token'], 0, 20) . '...');
                
                // Test a simple API call
                $this->newLine();
                $this->info('🔄 Testing API call with token...');
                
                $apiResponse = $client->get($apiUrl . '/v1/notifications/webhooks', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $data['access_token'],
                        'Content-Type' => 'application/json',
                    ],
                ]);

                if ($apiResponse->getStatusCode() === 200) {
                    $this->info('✅ API call successful! PayPal connection fully verified');
                } else {
                    $this->warn('⚠️  Token acquired but API call failed');
                }

                return 0;
            } else {
                $this->error('❌ Unexpected response format');
                $this->line($body);
                return 1;
            }

        } catch (RequestException $e) {
            $this->error('❌ HTTP Request failed:');
            $this->error($e->getMessage());
            
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                $this->newLine();
                $this->error('Response body:');
                $this->line($responseBody);
            }
            
            return 1;
        } catch (\Exception $e) {
            $this->error('❌ Unexpected error:');
            $this->error($e->getMessage());
            return 1;
        }
    }
}