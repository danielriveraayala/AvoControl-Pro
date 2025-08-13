<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Subscription;

class TrialWelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public Tenant $tenant;
    public Subscription $subscription;
    
    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Tenant $tenant, Subscription $subscription)
    {
        $this->user = $user;
        $this->tenant = $tenant;
        $this->subscription = $subscription;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Bienvenido a AvoControl Pro! Tu trial de 7 días ha comenzado',
            from: config('mail.from.address', 'noreply@avocontrol.com'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-welcome',
            with: [
                'user' => $this->user,
                'tenant' => $this->tenant,
                'subscription' => $this->subscription,
                'trial_days' => $this->subscription->trial_ends_at?->diffInDays(now()) ?? 7,
                'dashboard_url' => route('dashboard'),
                'plans_url' => route('subscription.plans'),
                'support_email' => config('mail.support_email', 'soporte@avocontrol.com'),
            ]
        );
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
