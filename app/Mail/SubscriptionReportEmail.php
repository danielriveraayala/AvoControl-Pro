<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionReportEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $reportData,
        public string $period,
        public ?string $reportFile = null
    ) {}

    public function build()
    {
        $subject = "📊 Reporte de Suscripciones " . ucfirst($this->period) . " - " . now()->format('d/m/Y');
        
        $mail = $this->subject($subject)
                     ->view('emails.subscription-report')
                     ->with([
                         'period' => $this->period,
                         'overview' => $this->reportData['overview'],
                         'revenue' => $this->reportData['revenue'],
                         'plans' => $this->reportData['plans'],
                         'periodMetrics' => $this->reportData['period_metrics'],
                         'payments' => $this->reportData['payments'],
                         'tenants' => $this->reportData['tenants'],
                         'reportDate' => now()->format('d/m/Y H:i:s'),
                     ]);

        // Attach report file if available
        if ($this->reportFile && storage_path("app/reports/{$this->reportFile}")) {
            $mail->attach(storage_path("app/reports/{$this->reportFile}"), [
                'as' => "reporte_suscripciones_{$this->period}.json",
                'mime' => 'application/json',
            ]);
        }

        return $mail;
    }
}