<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Tenant;

class RegistrationConfirmationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $subscription;
    public $tenant;
    public $temporaryPassword;
    public $loginUrl;
    public $dashboardUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Subscription $subscription = null, Tenant $tenant = null, string $temporaryPassword = null)
    {
        $this->user = $user;
        $this->subscription = $subscription;
        $this->tenant = $tenant;
        $this->temporaryPassword = $temporaryPassword;
        $this->loginUrl = route('login');
        $this->dashboardUrl = route('dashboard');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjectLine = $this->subscription 
            ? "¡Bienvenido a AvoControl Pro! Tu cuenta {$this->subscription->plan} está lista"
            : "¡Bienvenido a AvoControl Pro! Tu cuenta ha sido creada exitosamente";

        return new Envelope(
            from: config('mail.from.address'),
            subject: $subjectLine,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-confirmation',
            with: [
                'user' => $this->user,
                'subscription' => $this->subscription,
                'tenant' => $this->tenant,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => $this->loginUrl,
                'dashboardUrl' => $this->dashboardUrl,
                'supportEmail' => 'avocontrol@kreativos.pro',
                'supportPhone' => '+52 443 123 4567',
                'companyName' => 'AvoControl Pro',
                'companyUrl' => 'https://dev.avocontrol.pro',
                'planFeatures' => $this->getPlanFeatures(),
                'quickStartSteps' => $this->getQuickStartSteps(),
            ],
        );
    }

    /**
     * Get plan features based on subscription
     */
    private function getPlanFeatures(): array
    {
        if (!$this->subscription) {
            return [];
        }

        $features = [
            'trial' => [
                '7 días de prueba gratis',
                '1 usuario incluido',
                '50 lotes máximo',
                'Reportes básicos',
                'Soporte por email'
            ],
            'basic' => [
                '5 usuarios incluidos',
                '500 lotes por mes',
                'Todos los reportes',
                '2GB de almacenamiento',
                'Notificaciones por email',
                'Soporte por email'
            ],
            'premium' => [
                '25 usuarios incluidos',
                '2,000 lotes por mes',
                'Reportes avanzados + exportación',
                '10GB de almacenamiento',
                'Notificaciones push + SMS',
                'API access',
                'Backup automático',
                'Soporte prioritario'
            ],
            'enterprise' => [
                '100 usuarios incluidos',
                'Lotes ilimitados',
                'Reportes personalizados',
                '50GB de almacenamiento',
                'Multi-ubicación',
                'API completo',
                'Marca personalizada',
                'Soporte 24/7'
            ]
        ];

        return $features[$this->subscription->plan] ?? [];
    }

    /**
     * Get quick start steps based on subscription plan
     */
    private function getQuickStartSteps(): array
    {
        $baseSteps = [
            [
                'title' => 'Inicia Sesión',
                'description' => 'Usa tu email y la contraseña que creaste para acceder al sistema',
                'icon' => 'fas fa-sign-in-alt',
                'url' => $this->loginUrl
            ],
            [
                'title' => 'Configura tu Empresa',
                'description' => 'Completa la información de tu centro de acopio en configuración',
                'icon' => 'fas fa-building',
                'url' => route('config.index')
            ],
            [
                'title' => 'Agrega Proveedores',
                'description' => 'Registra a tus proveedores de aguacate para comenzar a crear lotes',
                'icon' => 'fas fa-truck',
                'url' => route('suppliers.index')
            ],
            [
                'title' => 'Crea tu Primer Lote',
                'description' => 'Registra tu primera compra de aguacate y comienza a usar el sistema',
                'icon' => 'fas fa-boxes',
                'url' => route('lots.create')
            ]
        ];

        // Add plan-specific steps
        if ($this->subscription && in_array($this->subscription->plan, ['premium', 'enterprise'])) {
            $baseSteps[] = [
                'title' => 'Invita a tu Equipo',
                'description' => 'Agrega usuarios adicionales y asigna roles específicos',
                'icon' => 'fas fa-users',
                'url' => route('users.index')
            ];
        }

        if ($this->subscription && $this->subscription->plan === 'enterprise') {
            $baseSteps[] = [
                'title' => 'Configura API',
                'description' => 'Accede a nuestras APIs para integraciones personalizadas',
                'icon' => 'fas fa-code',
                'url' => route('config.api')
            ];
        }

        return $baseSteps;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}