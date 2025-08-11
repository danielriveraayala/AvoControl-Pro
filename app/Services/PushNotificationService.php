<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    protected $webPush;
    
    public function __construct()
    {
        // Ensure VAPID keys are properly formatted
        $publicKey = env('VAPID_PUBLIC_KEY');
        $privateKey = env('VAPID_PRIVATE_KEY');
        
        // Make sure keys are base64url encoded (remove any padding and convert characters)
        $publicKey = str_replace(['+', '/', '='], ['-', '_', ''], $publicKey);
        $privateKey = str_replace(['+', '/', '='], ['-', '_', ''], $privateKey);
        
        $auth = [
            'VAPID' => [
                'subject' => env('VAPID_SUBJECT', 'mailto:avocontrol@kreativos.pro'),
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ];

        $this->webPush = new WebPush($auth);
        
        // Set default options
        $this->webPush->setDefaultOptions([
            'TTL' => 86400, // 24 hours
            'urgency' => 'normal',
            'topic' => 'avocontrol-notification'
        ]);
    }

    /**
     * Enviar notificación push a una subscripción específica
     */
    public function sendToSubscription(PushSubscription $subscription, array $payload)
    {
        try {
            // Create subscription object for the library using raw keys
            $subscriptionData = [
                'endpoint' => $subscription->endpoint,
                'publicKey' => $subscription->p256dh_key,
                'authToken' => $subscription->auth_token,
            ];
            
            $webPushSubscription = Subscription::create($subscriptionData);
            
            // Queue notification for sending
            $this->webPush->queueNotification(
                $webPushSubscription,
                json_encode($payload)
            );
            
            // Send notifications
            $results = $this->webPush->flush();
            $success = true;
            
            // Process results
            foreach ($results as $result) {
                if (!$result->isSuccess()) {
                    $success = false;
                    
                    // Check if subscription is expired (410 = Gone, 404 = Not Found)
                    if (in_array($result->getStatusCode(), [410, 404])) {
                        $subscription->update(['active' => false]);
                        Log::warning('Push subscription expired, deactivated', [
                            'subscription_id' => $subscription->id,
                            'status_code' => $result->getStatusCode(),
                            'reason' => $result->getReason()
                        ]);
                    } else {
                        Log::error('Push notification failed', [
                            'subscription_id' => $subscription->id,
                            'status_code' => $result->getStatusCode(),
                            'reason' => $result->getReason()
                        ]);
                    }
                }
            }
            
            if ($success) {
                $subscription->update(['last_used_at' => now()]);
                
                Log::info('Push notification sent successfully', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'payload_type' => $payload['type'] ?? 'unknown'
                ]);
            }
            
            return $success;

        } catch (\Exception $e) {
            Log::error('Error sending push notification to subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Enviar notificación push a un usuario específico
     */
    public function sendToUser(User $user, array $payload)
    {
        $subscriptions = PushSubscription::where('user_id', $user->id)
                                       ->where('active', true)
                                       ->get();

        if ($subscriptions->isEmpty()) {
            Log::info('No active subscriptions found for user', [
                'user_id' => $user->id
            ]);
            return ['sent' => 0, 'failed' => 0];
        }

        $sent = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $payload)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    /**
     * Enviar notificación push a múltiples usuarios
     */
    public function sendToUsers(array $userIds, array $payload)
    {
        $users = User::whereIn('id', $userIds)->get();
        
        $totalSent = 0;
        $totalFailed = 0;

        foreach ($users as $user) {
            $result = $this->sendToUser($user, $payload);
            $totalSent += $result['sent'];
            $totalFailed += $result['failed'];
        }

        Log::info('Bulk push notification completed', [
            'total_users' => count($users),
            'total_sent' => $totalSent,
            'total_failed' => $totalFailed,
            'payload_type' => $payload['type'] ?? 'unknown'
        ]);

        return [
            'sent' => $totalSent,
            'failed' => $totalFailed,
            'users_targeted' => count($users)
        ];
    }

    /**
     * Enviar notificación push a todos los usuarios activos
     */
    public function sendToAllUsers(array $payload)
    {
        $subscriptions = PushSubscription::where('active', true)
                                       ->with('user')
                                       ->get();

        if ($subscriptions->isEmpty()) {
            Log::info('No active subscriptions found for broadcast');
            return ['sent' => 0, 'failed' => 0];
        }

        $sent = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $payload)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        Log::info('Broadcast push notification completed', [
            'total_subscriptions' => $subscriptions->count(),
            'sent' => $sent,
            'failed' => $failed,
            'payload_type' => $payload['type'] ?? 'unknown'
        ]);

        return ['sent' => $sent, 'failed' => $failed];
    }

    /**
     * Enviar notificación push a usuarios por rol
     */
    public function sendToRole(string $role, array $payload)
    {
        $users = User::where('role', $role)->get();
        $userIds = $users->pluck('id')->toArray();

        return $this->sendToUsers($userIds, $payload);
    }


    /**
     * Crear payload estándar para diferentes tipos de notificaciones
     */
    public static function createPayload($type, $title, $body, $options = [])
    {
        $defaultOptions = [
            'icon' => '/favicon.png',
            'badge' => '/favicon.png',
            'url' => '/dashboard',
            'priority' => 'normal',
            'tracking_id' => uniqid('push_'),
            'timestamp' => now()->toISOString()
        ];

        $payload = array_merge($defaultOptions, $options, [
            'type' => $type,
            'title' => $title,
            'body' => $body
        ]);

        return $payload;
    }

    /**
     * Crear notificaciones específicas del negocio
     */
    public function sendLowInventoryAlert($userIds = null)
    {
        $payload = self::createPayload(
            'low_inventory',
            '⚠️ Inventario Bajo',
            'Hay productos con inventario crítico que requieren atención inmediata',
            [
                'url' => '/acopio',
                'priority' => 'high',
                'icon' => '📦'
            ]
        );

        if ($userIds) {
            return $this->sendToUsers($userIds, $payload);
        } else {
            return $this->sendToRole('admin', $payload);
        }
    }

    public function sendPaymentReminder($userIds = null)
    {
        $payload = self::createPayload(
            'payment_reminder',
            '💰 Recordatorio de Pagos',
            'Tienes pagos pendientes que requieren tu atención',
            [
                'url' => '/payments',
                'priority' => 'high',
                'icon' => '💰'
            ]
        );

        if ($userIds) {
            return $this->sendToUsers($userIds, $payload);
        } else {
            return $this->sendToAllUsers($payload);
        }
    }

    public function sendDailyReport($userIds = null)
    {
        $payload = self::createPayload(
            'daily_report',
            '📊 Reporte Diario Disponible',
            'Tu reporte diario de operaciones está listo para revisar',
            [
                'url' => '/dashboard',
                'priority' => 'normal',
                'icon' => '📊'
            ]
        );

        if ($userIds) {
            return $this->sendToUsers($userIds, $payload);
        } else {
            return $this->sendToAllUsers($payload);
        }
    }

    public function sendNewLotAlert($lotId, $userIds = null)
    {
        $payload = self::createPayload(
            'new_lot',
            '🚚 Nuevo Lote Recibido',
            'Se ha registrado un nuevo lote de aguacates en el sistema',
            [
                'url' => "/lots/{$lotId}",
                'priority' => 'normal',
                'icon' => '🚚',
                'lot_id' => $lotId
            ]
        );

        if ($userIds) {
            return $this->sendToUsers($userIds, $payload);
        } else {
            return $this->sendToRole('admin', $payload);
        }
    }

    public function sendSystemAlert($message, $priority = 'normal', $userIds = null)
    {
        $icons = [
            'low' => '📢',
            'normal' => '⚠️',
            'high' => '🚨',
            'critical' => '🆘'
        ];

        $payload = self::createPayload(
            'system_alert',
            '🚨 Alerta del Sistema',
            $message,
            [
                'url' => '/configuration',
                'priority' => $priority,
                'icon' => $icons[$priority] ?? '⚠️'
            ]
        );

        if ($userIds) {
            return $this->sendToUsers($userIds, $payload);
        } else {
            return $this->sendToRole('admin', $payload);
        }
    }

    /**
     * Obtener estadísticas de push notifications
     */
    public function getStats()
    {
        return [
            'total_subscriptions' => PushSubscription::count(),
            'active_subscriptions' => PushSubscription::where('active', true)->count(),
            'users_with_subscriptions' => PushSubscription::where('active', true)->distinct('user_id')->count(),
            'subscriptions_by_browser' => $this->getBrowserStats(),
            'recent_subscriptions' => PushSubscription::where('created_at', '>=', now()->subDays(7))->count()
        ];
    }

    /**
     * Get browser statistics (MySQL compatible)
     */
    private function getBrowserStats()
    {
        $subscriptions = PushSubscription::where('active', true)->get();
        $browserCounts = [];
        
        foreach ($subscriptions as $subscription) {
            $browser = 'Other';
            $userAgent = $subscription->user_agent ?? '';
            
            if (strpos($userAgent, 'Chrome') !== false) {
                $browser = 'Chrome';
            } elseif (strpos($userAgent, 'Firefox') !== false) {
                $browser = 'Firefox';
            } elseif (strpos($userAgent, 'Safari') !== false) {
                $browser = 'Safari';
            } elseif (strpos($userAgent, 'Edge') !== false) {
                $browser = 'Edge';
            }
            
            $browserCounts[$browser] = ($browserCounts[$browser] ?? 0) + 1;
        }
        
        return $browserCounts;
    }

    /**
     * Limpiar subscripciones inactivas
     */
    public function cleanupInactiveSubscriptions($daysInactive = 30)
    {
        $deleted = PushSubscription::where('active', false)
                                 ->orWhere('last_used_at', '<', now()->subDays($daysInactive))
                                 ->delete();

        Log::info('Cleaned up inactive push subscriptions', [
            'deleted_count' => $deleted
        ]);

        return $deleted;
    }
    
    /**
     * Convert base64url to base64
     */
    private function base64url_decode($data)
    {
        // Replace URL-safe characters
        $data = strtr($data, '-_', '+/');
        
        // Add padding if needed
        $padding = 4 - (strlen($data) % 4);
        if ($padding < 4) {
            $data .= str_repeat('=', $padding);
        }
        
        return base64_decode($data);
    }
}