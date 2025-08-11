<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PushSubscription;

try {
    echo "Depurando subscripción...\n\n";
    
    $subscription = PushSubscription::where('active', true)->first();
    
    if (!$subscription) {
        echo "No hay subscripciones activas\n";
        exit;
    }
    
    echo "Subscription ID: {$subscription->id}\n";
    echo "User ID: {$subscription->user_id}\n";
    echo "Endpoint: " . substr($subscription->endpoint, 0, 50) . "...\n";
    echo "P256DH Key: " . substr($subscription->p256dh_key, 0, 20) . "...\n";
    echo "Auth Token: " . substr($subscription->auth_token, 0, 20) . "...\n";
    echo "P256DH Key Length: " . strlen($subscription->p256dh_key) . "\n";
    echo "Auth Token Length: " . strlen($subscription->auth_token) . "\n";
    
    // Test if keys are base64
    echo "\nTesting key formats:\n";
    echo "P256DH is valid base64: " . (base64_decode($subscription->p256dh_key, true) !== false ? "Yes" : "No") . "\n";
    echo "Auth Token is valid base64: " . (base64_decode($subscription->auth_token, true) !== false ? "Yes" : "No") . "\n";
    
    // Try to decode
    $p256dh_decoded = base64_decode($subscription->p256dh_key, true);
    $auth_decoded = base64_decode($subscription->auth_token, true);
    
    echo "P256DH decoded length: " . ($p256dh_decoded ? strlen($p256dh_decoded) : "Failed") . "\n";
    echo "Auth decoded length: " . ($auth_decoded ? strlen($auth_decoded) : "Failed") . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}