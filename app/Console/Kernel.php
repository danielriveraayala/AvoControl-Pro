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
