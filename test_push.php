<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\PushNotificationService;
use App\Models\User;

try {
    echo "Iniciando prueba de push notification...\n";
    
    $service = new PushNotificationService();
    $user = User::find(1);
    
    if (!$user) {
        echo "Usuario no encontrado\n";
        exit;
    }
    
    echo "Usuario encontrado: {$user->email}\n";
    
    $payload = [
        'title' => '🧪 Prueba desde Backend',
        'body' => 'Esta es una prueba directa desde el servidor PHP',
        'type' => 'test',
        'url' => '/dashboard',
        'priority' => 'normal'
    ];
    
    echo "Enviando notificación...\n";
    $result = $service->sendToUser($user, $payload);
    
    echo "Resultado: Enviado: {$result['sent']}, Fallidos: {$result['failed']}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}