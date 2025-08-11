<?php

namespace App\Mail\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SystemAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $alert;

    /**
     * Create a new message instance.
     */
    public function __construct($alertData = [])
    {
        $this->alert = array_merge([
            'priority' => 'normal',
            'title' => 'Notificación del Sistema',
            'timestamp' => now()->format('d/m/Y H:i:s'),
            'tracking_id' => 'SYS-' . now()->format('Ymd-His') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)
        ], $alertData);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $priorityEmoji = '📢';
        $priorityText = 'Información';
        
        switch ($this->alert['priority']) {
            case 'critical':
                $priorityEmoji = '🚨';
                $priorityText = 'CRÍTICO';
                break;
            case 'high':
                $priorityEmoji = '⚠️';
                $priorityText = 'URGENTE';
                break;
            case 'normal':
                $priorityEmoji = '📢';
                $priorityText = 'IMPORTANTE';
                break;
            case 'low':
                $priorityEmoji = 'ℹ️';
                $priorityText = 'Información';
                break;
        }

        return $this->subject($priorityEmoji . ' Alerta del Sistema - ' . $priorityText . ' - AvoControl Pro')
                    ->view('emails.notifications.system-alert')
                    ->with([
                        'alert' => $this->alert,
                        'title' => 'Alerta del Sistema'
                    ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray()
    {
        return [
            'type' => 'system_alert',
            'priority' => $this->alert['priority'] ?? 'normal',
            'title' => $this->alert['title'] ?? 'Notificación del Sistema',
            'tracking_id' => $this->alert['tracking_id'] ?? null,
            'affected_users_count' => isset($this->alert['affected_users']) ? count($this->alert['affected_users']) : 0,
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * Create a critical system alert
     */
    public static function critical($title, $description, $additionalData = [])
    {
        return new static(array_merge([
            'priority' => 'critical',
            'title' => $title,
            'description' => $description,
            'required_actions' => [
                [
                    'title' => 'Revisar estado del sistema inmediatamente',
                    'description' => 'Verificar que todos los servicios estén funcionando correctamente',
                    'urgency' => 'immediate'
                ],
                [
                    'title' => 'Contactar al administrador técnico',
                    'description' => 'Notificar al equipo técnico sobre el problema crítico',
                    'urgency' => 'immediate'
                ]
            ],
            'impact' => [
                'El sistema puede estar experimentando problemas de funcionamiento',
                'Los usuarios pueden tener dificultades para acceder al sistema',
                'Es posible que se requiera intervención técnica inmediata'
            ],
            'prevention_tips' => [
                'Realizar respaldos regulares de la base de datos',
                'Monitorear el uso de recursos del servidor',
                'Mantener el software actualizado con las últimas versiones de seguridad'
            ]
        ], $additionalData));
    }

    /**
     * Create a high priority alert
     */
    public static function high($title, $description, $additionalData = [])
    {
        return new static(array_merge([
            'priority' => 'high',
            'title' => $title,
            'description' => $description,
            'required_actions' => [
                [
                    'title' => 'Revisar la situación dentro de 30 minutos',
                    'description' => 'Evaluar el impacto y tomar medidas correctivas',
                    'urgency' => 'high'
                ]
            ]
        ], $additionalData));
    }

    /**
     * Create a normal priority alert
     */
    public static function normal($title, $description, $additionalData = [])
    {
        return new static(array_merge([
            'priority' => 'normal',
            'title' => $title,
            'description' => $description,
            'required_actions' => [
                [
                    'title' => 'Revisar cuando sea conveniente',
                    'description' => 'Esta es una notificación informativa que no requiere acción inmediata',
                    'urgency' => 'normal'
                ]
            ]
        ], $additionalData));
    }

    /**
     * Create an informational alert
     */
    public static function info($title, $description, $additionalData = [])
    {
        return new static(array_merge([
            'priority' => 'low',
            'title' => $title,
            'description' => $description
        ], $additionalData));
    }

    /**
     * Prepare sample data for testing
     */
    public static function prepareSampleData($priority = 'normal')
    {
        $sampleData = [
            'critical' => [
                'title' => 'Base de Datos No Responde',
                'description' => 'Se ha detectado que la base de datos principal no está respondiendo a las consultas. Esto está afectando a todos los usuarios del sistema.',
                'technical_details' => [
                    'error_code' => 'DB_CONNECTION_FAILED',
                    'affected_service' => 'MySQL Database',
                    'server_response_time' => 'Timeout (30s)',
                    'last_successful_connection' => now()->subMinutes(5)->format('d/m/Y H:i:s')
                ],
                'metrics' => [
                    ['label' => 'Tiempo sin servicio', 'value' => '5 min', 'color' => '#dc3545'],
                    ['label' => 'Usuarios afectados', 'value' => '24', 'color' => '#fd7e14'],
                    ['label' => 'Intentos fallidos', 'value' => '156', 'color' => '#ffc107']
                ],
                'affected_users' => [
                    ['name' => 'Juan Carlos Pérez', 'role' => 'Administrador', 'status' => 'Activo'],
                    ['name' => 'María González', 'role' => 'Vendedor', 'status' => 'Activo'],
                    ['name' => 'Roberto Silva', 'role' => 'Contador', 'status' => 'Activo']
                ],
                'actions_taken' => [
                    'Notificación automática enviada al equipo técnico',
                    'Servicios de respaldo activados automáticamente',
                    'Log de errores generado para análisis posterior'
                ],
                'direct_link' => url('/configuration')
            ],
            'high' => [
                'title' => 'Inventario Crítico Detectado',
                'description' => 'Se han detectado múltiples calidades con inventario por debajo del 10% del límite mínimo establecido.',
                'metrics' => [
                    ['label' => 'Calidades Afectadas', 'value' => '5', 'color' => '#fd7e14'],
                    ['label' => 'Déficit Total', 'value' => '850 kg', 'color' => '#dc3545']
                ]
            ],
            'normal' => [
                'title' => 'Actualización del Sistema Completada',
                'description' => 'El sistema se ha actualizado exitosamente a la versión 2.1.3. Se han implementado mejoras de seguridad y nuevas funcionalidades.',
                'technical_details' => [
                    'version_anterior' => '2.1.2',
                    'version_actual' => '2.1.3',
                    'tiempo_actualizacion' => '15 minutos',
                    'archivos_modificados' => '47'
                ]
            ]
        ];

        return $sampleData[$priority] ?? $sampleData['normal'];
    }
}