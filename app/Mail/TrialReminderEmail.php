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

class TrialReminderEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public Tenant $tenant;
    public Subscription $subscription;
    public int $daysLeft;
    
    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Tenant $tenant, Subscription $subscription, int $daysLeft)
    {
        $this->user = $user;
        $this->tenant = $tenant;
        $this->subscription = $subscription;
        $this->daysLeft = $daysLeft;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->daysLeft <= 1 
            ? 'Tu trial expira hoy - Actualiza tu plan ahora'
            : "Solo {$this->daysLeft} días restantes en tu trial de AvoControl Pro";

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address', 'noreply@avocontrol.com'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-reminder',
            with: [
                'user' => $this->user,
                'tenant' => $this->tenant,
                'subscription' => $this->subscription,
                'days_left' => $this->daysLeft,
                'is_last_day' => $this->daysLeft <= 1,
                'dashboard_url' => route('dashboard'),
                'plans_url' => route('subscription.plans'),
                'support_email' => config('mail.support_email', 'soporte@avocontrol.com'),
                'trial_end_date' => $this->subscription->trial_ends_at?->format('d/m/Y H:i'),
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
