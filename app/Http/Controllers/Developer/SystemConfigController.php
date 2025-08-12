<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class SystemConfigController extends Controller
{
    /**
     * Display system configuration overview.
     */
    public function index()
    {
        $configs = [
            'smtp' => $this->getSmtpConfig(),
            'push' => $this->getPushConfig(),
            'system' => $this->getSystemConfig(),
        ];
        
        return view('developer.config.index', compact('configs'));
    }

    /**
     * Show SMTP configuration page.
     */
    public function smtp()
    {
        $smtpConfig = $this->getSmtpConfig();
        return view('developer.config.smtp', compact('smtpConfig'));
    }

    /**
     * Update SMTP configuration.
     */
    public function updateSmtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mail_mailer' => 'required|in:smtp,sendmail,mailgun,ses,postmark,log',
            'mail_host' => 'required_if:mail_mailer,smtp|string|max:255',
            'mail_port' => 'required_if:mail_mailer,smtp|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update .env file
            $this->updateEnvFile([
                'MAIL_MAILER' => $request->mail_mailer,
                'MAIL_HOST' => $request->mail_host,
                'MAIL_PORT' => $request->mail_port,
                'MAIL_USERNAME' => $request->mail_username,
                'MAIL_PASSWORD' => '"' . $request->mail_password . '"',
                'MAIL_ENCRYPTION' => $request->mail_encryption,
                'MAIL_FROM_ADDRESS' => $request->mail_from_address,
                'MAIL_FROM_NAME' => '"' . $request->mail_from_name . '"',
            ]);

            // Clear config cache
            Artisan::call('config:clear');
            Cache::forget('system_smtp_config');

            return redirect()->route('developer.config.smtp')
                ->with('success', 'Configuración SMTP actualizada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar configuración SMTP: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Test SMTP configuration.
     */
    public function testSmtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Email inválido']);
        }

        try {
            // Clear config cache to get latest settings
            Artisan::call('config:clear');
            
            // Get SMTP configuration
            $host = config('mail.mailers.smtp.host');
            $port = config('mail.mailers.smtp.port');
            $encryption = config('mail.mailers.smtp.encryption');
            $username = config('mail.mailers.smtp.username');
            $password = config('mail.mailers.smtp.password');
            
            // Create transport based on encryption type
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                $host,
                $port,
                $encryption === 'ssl' // true for SSL (465), false for TLS/STARTTLS (587)
            );
            
            // Set authentication credentials
            if ($username) {
                $transport->setUsername($username);
            }
            if ($password) {
                $transport->setPassword($password);
            }

            // For Hostinger and other shared hosting providers
            // Note: setStreamContextOptions might not be available in all versions
            // We'll use the transport directly with appropriate settings

            $mailer = new \Symfony\Component\Mailer\Mailer($transport);
            
            // Create test email with proper from format
            $fromAddress = config('mail.from.address');
            $fromName = config('mail.from.name');
            
            $email = (new \Symfony\Component\Mime\Email())
                ->from(new \Symfony\Component\Mime\Address($fromAddress, $fromName))
                ->to($request->test_email)
                ->subject('Prueba de Configuración SMTP - AvoControl Pro')
                ->text('Este es un email de prueba desde AvoControl Pro. Si recibes este mensaje, la configuración SMTP está funcionando correctamente.')
                ->html('<p>Este es un email de prueba desde <strong>AvoControl Pro</strong>.</p><p>Si recibes este mensaje, la configuración SMTP está funcionando correctamente.</p>');

            // Send email
            $mailer->send($email);

            return response()->json([
                'success' => true, 
                'message' => 'Email de prueba enviado exitosamente a ' . $request->test_email
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Provide specific help for common issues
            if (strpos($errorMessage, 'authentication failed') !== false || 
                strpos($errorMessage, 'AUTH') !== false) {
                $errorMessage = "Error de autenticación SMTP.\n\n" .
                    "💡 Sugerencias para Hostinger:\n" .
                    "• Verifica que el email existe en tu panel de Hostinger\n" .
                    "• Usa la contraseña exacta del email (no la de cPanel)\n" .
                    "• Puerto 587 con TLS es recomendado\n" .
                    "• Asegúrate de que el email no esté bloqueado";
            } elseif (strpos($errorMessage, 'Connection could not be established') !== false ||
                     strpos($errorMessage, 'Connection refused') !== false ||
                     strpos($errorMessage, 'Connection timed out') !== false) {
                $errorMessage = "No se pudo conectar con el servidor SMTP.\n\n" .
                    "💡 Posibles soluciones:\n" .
                    "• Verifica el servidor: smtp.hostinger.com\n" .
                    "• Puerto 587 para TLS, 465 para SSL\n" .
                    "• Si usas TLS, asegúrate de que el puerto sea 587\n" .
                    "• Verifica que tu firewall no bloquee la conexión\n" .
                    "• Contacta a Hostinger si el problema persiste";
            } elseif (strpos($errorMessage, 'SSL') !== false || 
                     strpos($errorMessage, 'TLS') !== false) {
                $errorMessage = "Error de encriptación SSL/TLS.\n\n" .
                    "💡 Sugerencias:\n" .
                    "• Para TLS usa puerto 587\n" .
                    "• Para SSL usa puerto 465\n" .
                    "• Verifica que la encriptación coincida con el puerto";
            }
            
            \Log::error('SMTP Test Failed', [
                'error' => $e->getMessage(),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'username' => config('mail.mailers.smtp.username')
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => $errorMessage
            ]);
        }
    }

    /**
     * Show push notifications configuration page.
     */
    public function notifications()
    {
        $pushConfig = $this->getPushConfig();
        return view('developer.config.notifications', compact('pushConfig'));
    }

    /**
     * Update push notifications configuration.
     */
    public function updateNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vapid_public_key' => 'required|string',
            'vapid_private_key' => 'required|string',
            'vapid_subject' => 'required|string|max:255',
            'push_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update .env file
            $this->updateEnvFile([
                'VAPID_PUBLIC_KEY' => $request->vapid_public_key,
                'VAPID_PRIVATE_KEY' => $request->vapid_private_key,
                'VAPID_SUBJECT' => $request->vapid_subject,
                'PUSH_NOTIFICATIONS_ENABLED' => $request->boolean('push_enabled') ? 'true' : 'false',
            ]);

            // Clear config cache
            Artisan::call('config:clear');
            Cache::forget('system_push_config');

            return redirect()->route('developer.config.notifications')
                ->with('success', 'Configuración de notificaciones push actualizada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar configuración: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Test push notifications.
     */
    public function testNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_title' => 'required|string|max:255',
            'test_message' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos']);
        }

        try {
            // Clear config cache
            Artisan::call('config:clear');

            // Get current user's push subscriptions
            $user = auth()->user();
            $subscriptions = \App\Models\PushSubscription::where('user_id', $user->id)
                                                         ->where('active', true)
                                                         ->get();

            if ($subscriptions->isEmpty()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No tienes suscripciones push activas. Activa las notificaciones en tu navegador primero.'
                ]);
            }

            // Send test notification using PushNotificationService
            $pushService = new \App\Services\PushNotificationService();
            $payload = [
                'title' => $request->test_title,
                'body' => $request->test_message,
                'type' => 'test',
                'priority' => 'normal',
                'url' => '/developer',
                'tracking_id' => 'test-' . now()->timestamp
            ];

            $results = $pushService->sendToUser($user, $payload);
            
            return response()->json([
                'success' => true, 
                'message' => 'Notificación de prueba enviada a ' . $results['sent'] . ' dispositivos' . 
                           ($results['failed'] > 0 ? ' (' . $results['failed'] . ' fallos)' : '')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error al enviar notificación: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show VAPID keys management page.
     */
    public function vapid()
    {
        $vapidConfig = [
            'public_key' => env('VAPID_PUBLIC_KEY'),
            'private_key' => env('VAPID_PRIVATE_KEY'),
            'subject' => env('VAPID_SUBJECT'),
        ];
        
        return view('developer.config.vapid', compact('vapidConfig'));
    }

    /**
     * Generate new VAPID keys.
     */
    public function generateVapid()
    {
        try {
            // Generate VAPID keys using Web Push library directly
            $keys = \Minishlink\WebPush\VAPID::createVapidKeys();
            
            $publicKey = $keys['publicKey'];
            $privateKey = $keys['privateKey'];
            
            if (empty($publicKey) || empty($privateKey)) {
                throw new \Exception('No se pudieron generar las llaves VAPID');
            }
            
            // Update .env file
            $this->updateEnvFile([
                'VAPID_PUBLIC_KEY' => $publicKey,
                'VAPID_PRIVATE_KEY' => $privateKey,
                'VAPID_SUBJECT' => 'mailto:' . config('mail.from.address', 'admin@avocontrol.com'),
            ]);

            // Clear config cache
            Artisan::call('config:clear');
            Cache::forget('system_push_config');

            return response()->json([
                'success' => true,
                'message' => 'Nuevas llaves VAPID generadas exitosamente',
                'public_key' => $publicKey,
                'private_key' => $privateKey,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar llaves VAPID: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show notifications manager page.
     */
    public function notificationsManager()
    {
        return view('developer.config.notifications-manager');
    }

    /**
     * Get notifications data for DataTable.
     */
    public function getNotificationsData(Request $request)
    {
        $query = \App\Models\Notification::query()->with(['user', 'notifiable']);
        
        // Search functionality
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('channels', 'like', "%{$search}%")
                  ->orWhere('priority', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        // Filter by channels
        if ($request->has('channels') && $request->channels !== '') {
            $query->where('channels', 'like', "%{$request->channels}%");
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $totalRecords = $query->count();

        // Ordering
        $columns = ['id', 'type', 'title', 'priority', 'channels', 'status', 'created_at'];
        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $columnName = $columns[$columnIndex] ?? 'created_at';
            $direction = $request->order[0]['dir'] ?? 'desc';
            $query->orderBy($columnName, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        if ($request->has('start') && $request->has('length')) {
            $query->skip($request->start)->take($request->length);
        }

        $notifications = $query->get();

        $data = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">' . ucfirst($notification->type) . '</span>',
                'title' => $notification->title,
                'message' => \Str::limit($notification->message, 50),
                'user' => $notification->user ? $notification->user->name : 'Sistema',
                'priority' => $this->getPriorityBadge($notification->priority),
                'channels' => $this->getChannelsBadges($notification->channels),
                'status' => $this->getStatusBadge($notification->status),
                'created_at' => $notification->created_at->format('d/m/Y H:i'),
                'actions' => $this->getNotificationActions($notification)
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => \App\Models\Notification::count(),
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    /**
     * Delete notification.
     */
    public function deleteNotification(\App\Models\Notification $notification)
    {
        try {
            $notification->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Notificación eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la notificación: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get priority badge.
     */
    private function getPriorityBadge($priority)
    {
        $badges = [
            'low' => 'bg-gray-100 text-gray-800',
            'normal' => 'bg-blue-100 text-blue-800',
            'high' => 'bg-yellow-100 text-yellow-800',
            'critical' => 'bg-red-100 text-red-800'
        ];

        $class = $badges[$priority] ?? 'bg-gray-100 text-gray-800';
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $class . '">' . ucfirst($priority) . '</span>';
    }

    /**
     * Get channels badges.
     */
    private function getChannelsBadges($channels)
    {
        if (is_string($channels)) {
            $channelsArray = explode(',', $channels);
        } else {
            $channelsArray = (array) $channels;
        }

        $badges = [];
        foreach ($channelsArray as $channel) {
            $channel = trim($channel);
            $badgeClass = match($channel) {
                'email' => 'bg-green-100 text-green-800',
                'push' => 'bg-blue-100 text-blue-800',
                'database' => 'bg-gray-100 text-gray-800',
                default => 'bg-gray-100 text-gray-800'
            };
            $badges[] = '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium ' . $badgeClass . '">' . ucfirst($channel) . '</span>';
        }

        return implode(' ', $badges);
    }

    /**
     * Get status badge.
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'sent' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'scheduled' => 'bg-blue-100 text-blue-800'
        ];

        $class = $badges[$status] ?? 'bg-gray-100 text-gray-800';
        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $class . '">' . ucfirst($status) . '</span>';
    }

    /**
     * Get notification actions.
     */
    private function getNotificationActions($notification)
    {
        $actions = '<div class="flex items-center space-x-2">';
        
        // View details button
        $actions .= '<button type="button" class="text-blue-600 hover:text-blue-800 font-medium" onclick="viewNotification(\'' . $notification->id . '\')" title="Ver detalles">';
        $actions .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
        $actions .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
        $actions .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
        $actions .= '</svg>';
        $actions .= '</button>';
        
        // Delete button
        $actions .= '<button type="button" class="text-red-600 hover:text-red-800 font-medium" onclick="deleteNotification(\'' . $notification->id . '\')" title="Eliminar">';
        $actions .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
        $actions .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>';
        $actions .= '</svg>';
        $actions .= '</button>';
        
        $actions .= '</div>';
        
        return $actions;
    }

    /**
     * Get SMTP configuration.
     */
    private function getSmtpConfig()
    {
        return Cache::remember('system_smtp_config', 300, function () {
            return [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'password' => config('mail.mailers.smtp.password') ? '••••••••' : null,
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
                'configured' => !empty(config('mail.mailers.smtp.host')),
            ];
        });
    }

    /**
     * Get push notifications configuration.
     */
    private function getPushConfig()
    {
        return Cache::remember('system_push_config', 300, function () {
            return [
                'public_key' => env('VAPID_PUBLIC_KEY'),
                'private_key' => env('VAPID_PRIVATE_KEY') ? '••••••••' : null,
                'subject' => env('VAPID_SUBJECT'),
                'enabled' => env('PUSH_NOTIFICATIONS_ENABLED', false),
                'configured' => !empty(env('VAPID_PUBLIC_KEY')) && !empty(env('VAPID_PRIVATE_KEY')),
            ];
        });
    }

    /**
     * Get general system configuration.
     */
    private function getSystemConfig()
    {
        return [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'app_url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ];
    }

    /**
     * Update .env file with new values.
     */
    private function updateEnvFile(array $data)
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            throw new \Exception('Archivo .env no encontrado');
        }

        $envContent = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $value = $value ?? '';
            
            // Escape special characters for regex
            $escapedKey = preg_quote($key, '/');
            
            // Check if key exists
            if (preg_match("/^{$escapedKey}=.*$/m", $envContent)) {
                // Update existing key
                $envContent = preg_replace(
                    "/^{$escapedKey}=.*$/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // Add new key
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}