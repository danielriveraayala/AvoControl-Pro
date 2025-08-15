<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // ===============================
        // SISTEMA DE NOTIFICACIONES AUTOMÁTICAS
        // ===============================
        
        // Verificar niveles de inventario cada 4 horas (horario laboral)
        $schedule->command('notifications:check-inventory')
                 ->cron('0 */4 * * *')
                 ->between('8:00', '18:00')
                 ->weekdays()
                 ->description('Verificar inventario bajo y enviar alertas (email + push)');

        // Reporte diario de ventas (8:00 AM)
        $schedule->command('notifications:daily-report')
                 ->dailyAt('08:00')
                 ->description('Enviar reporte diario de ventas (email + push)');

        // Verificar pagos vencidos (9:00 AM diario)
        $schedule->command('notifications:check-overdue-payments')
                 ->dailyAt('09:00')
                 ->description('Verificar pagos vencidos y enviar recordatorios (email + push)');

        // Resumen semanal (Lunes 6:00 AM)
        $schedule->command('notifications:weekly-report')
                 ->weeklyOn(1, '06:00')
                 ->description('Enviar resumen semanal de actividades (email + push)');

        // Estado financiero mensual (1er día del mes, 7:00 AM)
        $schedule->command('notifications:monthly-report')
                 ->monthlyOn(1, '07:00')
                 ->description('Enviar estado financiero mensual (email + push)');

        // Procesar notificaciones programadas (cada 5 minutos)
        $schedule->command('notifications:process-scheduled')
                 ->everyFiveMinutes()
                 ->description('Procesar notificaciones programadas (email + push)');

        // Limpiar notificaciones antiguas (semanal - Domingos 2:00 AM)
        $schedule->command('notifications:cleanup')
                 ->weeklyOn(0, '02:00')
                 ->description('Limpiar notificaciones antiguas');

        // Enviar estadísticas del sistema (Viernes 17:00)
        $schedule->command('notifications:system-stats')
                 ->weeklyOn(5, '17:00')
                 ->description('Enviar estadísticas del sistema (email + push)');

        // ===============================
        // NOTIFICACIONES DE PRUEBA DIARIAS
        // ===============================
        
        // Notificación de prueba matutina (8:00 AM diario)
        $schedule->command('notifications:test-daily --type=all')
                 ->dailyAt('08:00')
                 ->description('Notificación de prueba matutina (email + push)');

        // Notificación de prueba vespertina (5:30 PM diario)
        $schedule->command('notifications:test-daily --type=all')
                 ->dailyAt('17:30')
                 ->description('Notificación de prueba vespertina (email + push)');

        // ===============================
        // SISTEMA DE RESPALDOS AUTOMÁTICOS
        // ===============================
        
        // Respaldo diario completo (2:00 AM)
        $schedule->command('backup:daily --type=full --cleanup=true')
                 ->dailyAt('02:00')
                 ->description('Crear respaldo diario completo y eliminar el anterior');

        // Respaldo de solo base de datos (cada 6 horas)
        $schedule->command('backup:daily --type=database --cleanup=true')
                 ->cron('0 */6 * * *')
                 ->description('Crear respaldo de BD cada 6 horas y eliminar el anterior');

        // ===============================
        // SISTEMA DE MONITOREO DE SUSCRIPCIONES
        // ===============================
        
        // Monitoreo de suscripciones (cada 4 horas durante horario laboral)
        $schedule->command('subscriptions:monitor')
                 ->cron('0 */4 * * *')
                 ->between('6:00', '22:00')
                 ->description('Monitorear estados de suscripción y enviar alertas');
                 
        // Verificación intensiva de suscripciones (diario 7:00 AM)
        $schedule->command('subscriptions:monitor')
                 ->dailyAt('07:00')
                 ->description('Verificación diaria completa de suscripciones');
                 
        // Reintentos automáticos de pagos fallidos (diario 10:00 AM)
        $schedule->command('subscriptions:retry-payments')
                 ->dailyAt('10:00')
                 ->description('Reintentar pagos fallidos automáticamente');
                 
        // Reportes de suscripciones para super admin
        $schedule->command('subscriptions:generate-reports --period=daily --email')
                 ->dailyAt('08:30')
                 ->description('Generar reporte diario de suscripciones');
                 
        $schedule->command('subscriptions:generate-reports --period=weekly --email')
                 ->weeklyOn(1, '09:00')
                 ->description('Generar reporte semanal de suscripciones');
                 
        $schedule->command('subscriptions:generate-reports --period=monthly --email')
                 ->monthlyOn(1, '08:00')
                 ->description('Generar reporte mensual de suscripciones');

        // ===============================
        // SISTEMA DE SUSPENSIÓN AUTOMÁTICA DE CUENTAS
        // ===============================
        
        // Suspensión automática de cuentas (cada 6 horas durante horario laboral)
        $schedule->command('accounts:auto-suspend')
                 ->cron('0 */6 * * *')
                 ->between('6:00', '22:00')
                 ->description('Suspender automáticamente cuentas con pagos vencidos');
                 
        // Verificación nocturna de suspensiones (2:30 AM)
        $schedule->command('accounts:auto-suspend')
                 ->dailyAt('02:30')
                 ->description('Verificación nocturna de cuentas para suspensión');

        // ===============================
        // LIMPIEZA DE USUARIOS FANTASMA
        // ===============================
        
        // Limpiar usuarios fantasma cada 6 horas
        $schedule->command('users:cleanup-phantom')
                 ->cron('0 */6 * * *')
                 ->description('Limpiar usuarios que no completaron el pago en 24h');

        // ===============================
        // COMANDOS EXISTENTES
        // ===============================
        
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
