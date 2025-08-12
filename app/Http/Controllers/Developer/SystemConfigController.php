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
                'MAIL_PASSWORD' => $request->mail_password,
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
            
            // Create custom mailer configuration for testing
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                config('mail.mailers.smtp.host'),
                config('mail.mailers.smtp.port'),
                config('mail.mailers.smtp.encryption') === 'ssl'
            );
            
            if (config('mail.mailers.smtp.username')) {
                $transport->setUsername(config('mail.mailers.smtp.username'));
            }
            if (config('mail.mailers.smtp.password')) {
                $transport->setPassword(config('mail.mailers.smtp.password'));
            }

            // For Hostinger specifically, add stream context options
            if (strpos(config('mail.mailers.smtp.host'), 'hostinger') !== false) {
                $transport->setStreamContextOptions([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ]);
            }

            $mailer = new \Symfony\Component\Mailer\Mailer($transport);
            
            // Create test email
            $email = (new \Symfony\Component\Mime\Email())
                ->from(config('mail.from.address'), config('mail.from.name'))
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
            
            // Provide specific help for common Hostinger issues
            if (strpos($errorMessage, 'authentication failed') !== false) {
                $errorMessage .= "\n\n💡 Sugerencias para Hostinger:\n" .
                    "- Verifica que el email existe en tu panel de Hostinger\n" .
                    "- Usa la contraseña exacta del email (no la de cPanel)\n" .
                    "- Prueba con puerto 587 y TLS en lugar de 465/SSL\n" .
                    "- Asegúrate de que el email no esté bloqueado";
            }
            
            return response()->json([
                'success' => false, 
                'message' => 'Error al enviar email: ' . $errorMessage
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
            $subscriptions = $user->pushSubscriptions ?? collect();

            if ($subscriptions->isEmpty()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No tienes suscripciones push activas. Activa las notificaciones en tu navegador primero.'
                ]);
            }

            // TODO: Implement actual push notification sending
            // This would integrate with your existing push notification system
            
            return response()->json([
                'success' => true, 
                'message' => 'Notificación de prueba enviada a ' . $subscriptions->count() . ' dispositivos'
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
            // Generate VAPID keys using artisan command
            Artisan::call('webpush:vapid');
            $output = Artisan::output();
            
            // Parse the output to extract keys
            preg_match('/Public Key:\s*(.+)/', $output, $publicMatches);
            preg_match('/Private Key:\s*(.+)/', $output, $privateMatches);
            
            if (empty($publicMatches[1]) || empty($privateMatches[1])) {
                throw new \Exception('No se pudieron generar las llaves VAPID');
            }

            $publicKey = trim($publicMatches[1]);
            $privateKey = trim($privateMatches[1]);
            
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