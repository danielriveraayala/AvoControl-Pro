<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Services\InvoiceService;

class WelcomeWithInvoiceEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $subscription;
    public $payment;
    public $tenant;
    public $invoiceService;
    public $invoiceData;

    /**
     * Create a new message instance.
     */
    public function __construct(
        User $user, 
        Subscription $subscription, 
        SubscriptionPayment $payment, 
        Tenant $tenant = null
    ) {
        $this->user = $user;
        $this->subscription = $subscription;
        $this->payment = $payment;
        $this->tenant = $tenant;
        $this->invoiceService = app(InvoiceService::class);
        
        // Prepare invoice data for attachment
        $this->invoiceData = $this->invoiceService->getInvoiceForEmail($payment);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            subject: "¡Bienvenido a AvoControl Pro! Factura y credenciales de acceso",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-with-invoice',
            with: [
                'user' => $this->user,
                'subscription' => $this->subscription,
                'payment' => $this->payment,
                'tenant' => $this->tenant,
                'loginUrl' => route('login'),
                'dashboardUrl' => route('dashboard'),
                'supportEmail' => 'avocontrol@kreativos.pro',
                'supportPhone' => '+52 443 123 4567',
                'companyName' => 'AvoControl Pro',
                'companyUrl' => 'https://dev.avocontrol.pro',
                'invoiceNumber' => $this->getInvoiceNumber(),
                'planFeatures' => $this->getPlanFeatures(),
                'quickStartSteps' => $this->getQuickStartSteps(),
                'paymentAmount' => $this->payment->amount,
                'paymentCurrency' => $this->payment->currency ?? 'USD',
                'paymentDate' => $this->payment->payment_date ?? $this->payment->created_at,
                'billingCycle' => $this->subscription->billing_cycle === 'yearly' ? 'anual' : 'mensual',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Add invoice PDF if available
        if ($this->invoiceData) {
            $attachments[] = Attachment::fromData(
                fn () => $this->invoiceData['content'],
                $this->invoiceData['filename']
            )->withMime($this->invoiceData['mime_type']);
        }

        return $attachments;
    }

    /**
     * Get invoice number for display
     */
    private function getInvoiceNumber(): string
    {
        if (!$this->payment) {
            return 'N/A';
        }

        $year = $this->payment->created_at->format('Y');
        $month = $this->payment->created_at->format('m');
        $subscriptionId = str_pad($this->subscription->id, 4, '0', STR_PAD_LEFT);
        $paymentId = str_pad($this->payment->id, 4, '0', STR_PAD_LEFT);
        
        return "AVO-{$year}{$month}-{$subscriptionId}-{$paymentId}";
    }

    /**
     * Get plan features based on subscription
     */
    private function getPlanFeatures(): array
    {
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
                'description' => 'Usa tu email registrado y la contraseña que creaste',
                'icon' => '🔐',
                'url' => route('login')
            ],
            [
                'title' => 'Revisa tu Factura',
                'description' => 'Tu factura está adjunta a este correo para tus registros contables',
                'icon' => '📄',
                'url' => null
            ],
            [
                'title' => 'Configura tu Empresa',
                'description' => 'Completa la información de tu centro de acopio',
                'icon' => '🏢',
                'url' => route('config.index')
            ],
            [
                'title' => 'Comienza a Usar el Sistema',
                'description' => 'Agrega proveedores y crea tu primer lote de aguacate',
                'icon' => '🥑',
                'url' => route('dashboard')
            ]
        ];

        // Add plan-specific steps
        if (in_array($this->subscription->plan, ['premium', 'enterprise'])) {
            $baseSteps[] = [
                'title' => 'Invita a tu Equipo',
                'description' => 'Agrega usuarios adicionales y asigna roles específicos',
                'icon' => '👥',
                'url' => route('users.index')
            ];
        }

        if ($this->subscription->plan === 'enterprise') {
            $baseSteps[] = [
                'title' => 'Configura Integraciones',
                'description' => 'Accede a nuestras APIs para integraciones personalizadas',
                'icon' => '⚙️',
                'url' => route('config.api')
            ];
        }

        return $baseSteps;
    }
}