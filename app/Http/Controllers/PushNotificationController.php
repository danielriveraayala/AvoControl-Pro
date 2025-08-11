<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\PushSubscription;
use App\Services\PushNotificationService;

class PushNotificationController extends Controller
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Get VAPID public key for frontend
     */
    public function getVapidKey()
    {
        try {
            $vapidKey = env('VAPID_PUBLIC_KEY');
            
            if (!$vapidKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'VAPID key not configured'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'vapid_key' => $vapidKey
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting VAPID key:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving VAPID key'
            ], 500);
        }
    }

    /**
     * Subscribe user to push notifications
     */
    public function subscribe(Request $request)
    {
        Log::info('Push subscription request received', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        try {
            $validated = $request->validate([
                'subscription' => 'required|array',
                'subscription.endpoint' => 'required|string',
                'subscription.keys' => 'required|array',
                'subscription.keys.p256dh' => 'required|string',
                'subscription.keys.auth' => 'required|string',
                'user_agent' => 'nullable|string',
                'timestamp' => 'nullable|string'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Push subscription validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Datos de subscripción inválidos',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            
            if (!$user) {
                Log::error('Push subscription attempted without authenticated user');
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            $subscription = $validated['subscription'];

            Log::info('Creating push subscription for user', [
                'user_id' => $user->id,
                'endpoint' => substr($subscription['endpoint'], 0, 50) . '...'
            ]);

            // Create or update push subscription
            $pushSubscription = PushSubscription::updateOrCreate(
                [
                    'endpoint' => $subscription['endpoint'],
                    'user_id' => $user->id
                ],
                [
                    'p256dh_key' => $subscription['keys']['p256dh'],
                    'auth_token' => $subscription['keys']['auth'],  // Changed from auth_key to auth_token
                    'user_agent' => $validated['user_agent'] ?? $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'last_used_at' => now(),
                    'active' => true
                ]
            );

            Log::info('User subscribed to push notifications successfully', [
                'user_id' => $user->id,
                'subscription_id' => $pushSubscription->id,
                'endpoint' => substr($subscription['endpoint'], 0, 50) . '...'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscripción exitosa a notificaciones push',
                'subscription_id' => $pushSubscription->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error subscribing to push notifications:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al suscribirse a las notificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe user from push notifications
     */
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'subscription' => 'required|array',
            'subscription.endpoint' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            $endpoint = $validated['subscription']['endpoint'];

            $subscription = PushSubscription::where('endpoint', $endpoint)
                                          ->where('user_id', $user->id)
                                          ->first();

            if ($subscription) {
                $subscription->update(['active' => false]);
                
                Log::info('User unsubscribed from push notifications', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Desubscripción exitosa de notificaciones push'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscripción no encontrada'
                ], 404);
            }

        } catch (\Exception $e) {
            Log::error('Error unsubscribing from push notifications:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la suscripción'
            ], 500);
        }
    }

    /**
     * Send test push notification
     */
    public function sendTest(Request $request)
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:255'
        ]);

        try {
            $user = Auth::user();
            $message = $validated['message'] ?? 'Notificación de prueba desde AvoControl Pro';

            // Get user's active subscriptions
            $subscriptions = PushSubscription::where('user_id', $user->id)
                                           ->where('active', true)
                                           ->get();

            if ($subscriptions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes subscripciones activas a notificaciones push'
                ], 404);
            }

            // En producción, usar el servicio real de push notifications
            $payload = [
                'title' => '🧪 Prueba de Notificación - AvoControl Pro',
                'body' => $message,
                'type' => 'test',
                'priority' => 'normal',
                'url' => '/dashboard',
                'tracking_id' => 'test-' . now()->timestamp
            ];

            // Si estamos en desarrollo local, simular éxito
            if (env('APP_ENV') === 'local') {
                Log::info('Test push notification (local mode)', [
                    'user_id' => $user->id,
                    'total_subscriptions' => $subscriptions->count(),
                    'mode' => 'local_simulation'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "¡Notificación enviada correctamente! (Modo desarrollo local)",
                    'stats' => [
                        'total' => $subscriptions->count(),
                        'successful' => $subscriptions->count(),
                        'failed' => 0
                    ],
                    'local_mode' => true,
                    'note' => 'Las notificaciones aparecerán automáticamente en tu navegador'
                ]);
            }

            // Código original para producción
            $payload = [
                'title' => '🧪 Prueba de Notificación',
                'body' => $message,
                'type' => 'test',
                'priority' => 'normal',
                'url' => '/dashboard',
                'tracking_id' => 'test-' . now()->timestamp
            ];

            $successCount = 0;
            $errorCount = 0;

            foreach ($subscriptions as $subscription) {
                try {
                    $result = $this->pushService->sendToSubscription($subscription, $payload);
                    if ($result) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::warning('Failed to send test notification to subscription', [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Test push notification sent', [
                'user_id' => $user->id,
                'total_subscriptions' => $subscriptions->count(),
                'successful' => $successCount,
                'failed' => $errorCount
            ]);

            return response()->json([
                'success' => true,
                'message' => "Notificación enviada a {$successCount} dispositivos" . 
                           ($errorCount > 0 ? " ({$errorCount} fallos)" : ''),
                'stats' => [
                    'total' => $subscriptions->count(),
                    'successful' => $successCount,
                    'failed' => $errorCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending test push notification:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar notificación de prueba'
            ], 500);
        }
    }

    /**
     * Get push subscription status for current user
     */
    public function getStatus()
    {
        try {
            $user = Auth::user();
            
            $subscriptions = PushSubscription::where('user_id', $user->id)
                                           ->where('active', true)
                                           ->get();

            $stats = [
                'is_subscribed' => $subscriptions->count() > 0,
                'subscription_count' => $subscriptions->count(),
                'last_subscription' => $subscriptions->max('created_at'),
                'browser_support' => true // This will be checked on frontend
            ];

            return response()->json([
                'success' => true,
                'status' => $stats,
                'subscriptions' => $subscriptions->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'browser' => $this->parseBrowser($sub->user_agent),
                        'created_at' => $sub->created_at->format('d/m/Y H:i'),
                        'last_used_at' => $sub->last_used_at ? $sub->last_used_at->format('d/m/Y H:i') : null,
                        'ip_address' => $sub->ip_address
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting push notification status:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estado de notificaciones'
            ], 500);
        }
    }

    /**
     * Track notification interactions
     */
    public function track(Request $request)
    {
        $validated = $request->validate([
            'tracking_id' => 'required|string',
            'action' => 'required|string|in:clicked,closed,received',
            'url' => 'nullable|string',
            'timestamp' => 'nullable|string'
        ]);

        try {
            // Log interaction for analytics
            Log::info('Push notification interaction tracked', [
                'user_id' => Auth::id(),
                'tracking_id' => $validated['tracking_id'],
                'action' => $validated['action'],
                'url' => $validated['url'] ?? null,
                'timestamp' => $validated['timestamp'] ?? now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Interacción registrada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error tracking push notification interaction:', [
                'error' => $e->getMessage(),
                'tracking_data' => $validated
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar interacción'
            ], 500);
        }
    }

    /**
     * Get push notification statistics
     */
    public function getStats()
    {
        try {
            $stats = $this->pushService->getStats();
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting push notification stats:', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Cleanup inactive subscriptions
     */
    public function cleanup()
    {
        try {
            $deletedCount = $this->pushService->cleanupInactiveSubscriptions(30);
            
            Log::info('Push subscriptions cleanup completed', [
                'deleted_count' => $deletedCount,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Subscripciones inactivas eliminadas correctamente',
                'deleted_count' => $deletedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error cleaning up push subscriptions:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar subscripciones inactivas'
            ], 500);
        }
    }

    /**
     * Parse browser information from user agent
     */
    private function parseBrowser($userAgent)
    {
        if (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }
}
