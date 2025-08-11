<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;

class DynamicMailService
{
    /**
     * Configure mail settings dynamically from database
     */
    public static function configureFromDatabase()
    {
        // Get email configuration from database, fallback to .env
        $mailConfig = [
            'transport' => Setting::get('mail_mailer', env('MAIL_MAILER', 'smtp'), 'email'),
            'host' => Setting::get('mail_host', env('MAIL_HOST', 'smtp.hostinger.com'), 'email'),
            'port' => Setting::get('mail_port', env('MAIL_PORT', 465), 'email'),
            'encryption' => Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'ssl'), 'email'),
            'username' => Setting::get('mail_username', env('MAIL_USERNAME', ''), 'email'),
            'password' => Setting::get('mail_password', env('MAIL_PASSWORD', ''), 'email'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ];

        $fromConfig = [
            'address' => Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS', 'noreply@avocontrol.pro'), 'email'),
            'name' => Setting::get('mail_from_name', env('MAIL_FROM_NAME', 'AvoControl Pro'), 'email'),
        ];

        // Update Laravel mail configuration
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', $mailConfig);
        Config::set('mail.from', $fromConfig);

        // Also update the mail manager
        Mail::purge('smtp');
    }

    /**
     * Send email with dynamic configuration
     */
    public static function send($recipients, Mailable $mailable)
    {
        // Configure mail settings from database
        static::configureFromDatabase();

        // Send the email
        return Mail::to($recipients)->send($mailable);
    }

    /**
     * Queue email with dynamic configuration
     */
    public static function queue($recipients, Mailable $mailable)
    {
        // Configure mail settings from database
        static::configureFromDatabase();

        // Queue the email
        return Mail::to($recipients)->queue($mailable);
    }

    /**
     * Test email connection with current database settings
     */
    public static function testConnection($testEmail = null)
    {
        try {
            // Configure from database
            static::configureFromDatabase();

            $testEmail = $testEmail ?? Setting::get('mail_from_address', 'test@avocontrol.pro', 'email');

            // Send simple test email
            Mail::raw('Prueba de conexión SMTP exitosa desde AvoControl Pro - ' . now()->format('d/m/Y H:i:s'), function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('Prueba de Configuración SMTP - AvoControl Pro');
            });

            return [
                'success' => true,
                'message' => 'Email de prueba enviado correctamente',
                'config_source' => 'database'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage(),
                'config_source' => 'database'
            ];
        }
    }

    /**
     * Get current mail configuration source and values
     */
    public static function getCurrentConfig()
    {
        return [
            'source' => 'database_with_env_fallback',
            'config' => [
                'mail_mailer' => Setting::get('mail_mailer', env('MAIL_MAILER', 'smtp'), 'email'),
                'mail_host' => Setting::get('mail_host', env('MAIL_HOST', ''), 'email'),
                'mail_port' => Setting::get('mail_port', env('MAIL_PORT', 587), 'email'),
                'mail_encryption' => Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'tls'), 'email'),
                'mail_username' => Setting::get('mail_username', env('MAIL_USERNAME', ''), 'email'),
                'mail_from_address' => Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS', ''), 'email'),
                'mail_from_name' => Setting::get('mail_from_name', env('MAIL_FROM_NAME', ''), 'email'),
            ],
            'settings_in_database' => Setting::where('group', 'email')->count() > 0
        ];
    }

    /**
     * Check if email notifications are enabled
     */
    public static function isEnabled()
    {
        return Setting::get('notification_email_enabled', env('NOTIFICATION_EMAIL_ENABLED', true), 'email');
    }

    /**
     * Get notification settings
     */
    public static function getNotificationSettings()
    {
        return [
            'enabled' => static::isEnabled(),
            'daily_report_time' => Setting::get('notification_daily_report_time', env('NOTIFICATION_DAILY_REPORT_TIME', '08:00'), 'email'),
            'weekly_report_day' => Setting::get('notification_weekly_report_day', env('NOTIFICATION_WEEKLY_REPORT_DAY', 1), 'email'),
            'monthly_report_day' => Setting::get('notification_monthly_report_day', env('NOTIFICATION_MONTHLY_REPORT_DAY', 1), 'email'),
        ];
    }
}