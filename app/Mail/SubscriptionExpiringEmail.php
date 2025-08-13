<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;

class SubscriptionExpiringEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public int $daysLeft
    ) {}

    public function build()
    {
        return $this->subject("⚠️ Tu suscripción expira en {$this->daysLeft} día(s)")
                    ->view('emails.subscription-expiring')
                    ->with([
                        'tenantName' => $this->subscription->tenant->name,
                        'plan' => ucfirst($this->subscription->plan),
                        'amount' => $this->subscription->amount,
                        'currency' => $this->subscription->currency,
                        'expirationDate' => $this->subscription->next_billing_date->format('d/m/Y'),
                        'daysLeft' => $this->daysLeft,
                        'renewalUrl' => route('subscription.renew', $this->subscription),
                        'supportUrl' => route('contact.support'),
                    ]);
    }
}