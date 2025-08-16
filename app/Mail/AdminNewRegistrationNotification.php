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

class AdminNewRegistrationNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $subscription;
    public $tenant;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Subscription $subscription, Tenant $tenant = null)
    {
        $this->user = $user;
        $this->subscription = $subscription;
        $this->tenant = $tenant;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            subject: "[AvoControl Pro] Nuevo registro: {$this->user->name} - Plan {$this->subscription->plan}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-new-registration',
            with: [
                'user' => $this->user,
                'subscription' => $this->subscription,
                'tenant' => $this->tenant,
                'registrationDate' => $this->user->created_at,
                'dashboardUrl' => route('developer.subscriptions.index'),
                'userManagementUrl' => route('developer.users.index'),
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
        return [];
    }
}