<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;

class AccountSuspendedEmail extends Mailable
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
            subject: 'Cuenta Suspendida - ' . config('app.name'),
            from: config('mail.from.address', 'noreply@avocontrol.pro'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-suspended',
            with: [
                'subscription' => $this->subscription,
                'tenant' => $this->subscription->tenant,
                'suspensionReason' => $this->subscription->suspension_reason,
                'suspendedAt' => $this->subscription->suspended_at,
                'canReactivate' => $this->subscription->canBeReactivated(),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}