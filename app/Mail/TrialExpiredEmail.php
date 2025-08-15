<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;

class TrialExpiredEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscription $subscription
    ) {}

    public function build()
    {
        return $this->subject("🚨 Tu período de prueba ha expirado")
                    ->view('emails.trial-expired')
                    ->with([
                        'tenantName' => $this->subscription->tenant->name,
                        'trialEndDate' => $this->subscription->trial_ends_at->format('d/m/Y'),
                        'subscribeUrl' => route('subscription.plans'),
                        'supportUrl' => route('contact.support'),
                        'dataRetentionDays' => 30, // Configurable
                    ]);
    }
}