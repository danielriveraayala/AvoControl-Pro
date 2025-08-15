<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;

class AccountReactivatedEmail extends Mailable
{
    use SerializesModels;

    public $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Cuenta Reactivada! - ' . config('app.name'),
            from: config('mail.from.address', 'noreply@avocontrol.pro'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-reactivated',
            with: [
                'subscription' => $this->subscription,
                'tenant' => $this->subscription->tenant,
                'reactivationReason' => $this->subscription->reactivation_reason,
                'reactivatedAt' => $this->subscription->reactivated_at,
                'nextBillingDate' => $this->subscription->next_billing_date,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}