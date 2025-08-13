<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;

class SuspensionWarningEmail extends Mailable
{
    use SerializesModels;

    public $subscription;
    public $daysLeft;

    public function __construct(Subscription $subscription, int $daysLeft)
    {
        $this->subscription = $subscription;
        $this->daysLeft = $daysLeft;
    }

    public function envelope(): Envelope
    {
        $urgency = $this->daysLeft <= 1 ? 'URGENTE: ' : '';
        
        return new Envelope(
            subject: $urgency . 'Advertencia de Suspensión - ' . config('app.name'),
            from: config('mail.from.address', 'noreply@avocontrol.pro'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.suspension-warning',
            with: [
                'subscription' => $this->subscription,
                'tenant' => $this->subscription->tenant,
                'daysLeft' => $this->daysLeft,
                'gracePeriodEnds' => $this->subscription->grace_period_ends_at,
                'failedPaymentCount' => $this->subscription->failed_payment_count,
                'isUrgent' => $this->daysLeft <= 1,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}