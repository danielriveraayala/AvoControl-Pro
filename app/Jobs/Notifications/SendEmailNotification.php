<?php

namespace App\Jobs\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Services\DynamicMailService;

class SendEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipients;
    protected $mailable;
    protected $notificationId;
    protected $priority;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1 min, 5 min, 15 min

    /**
     * Create a new job instance.
     */
    public function __construct($recipients, Mailable $mailable, $notificationId = null, $priority = 'normal')
    {
        $this->recipients = is_array($recipients) ? $recipients : [$recipients];
        $this->mailable = $mailable;
        $this->notificationId = $notificationId;
        $this->priority = $priority;
        
        // Set queue priority based on notification priority
        match ($priority) {
            'critical' => $this->onQueue('critical'),
            'high' => $this->onQueue('high'),
            'normal' => $this->onQueue('emails'),
            default => $this->onQueue('low')
        };
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            foreach ($this->recipients as $recipient) {
                Log::info('Sending email notification', [
                    'recipient' => $recipient,
                    'mailable_class' => get_class($this->mailable),
                    'notification_id' => $this->notificationId,
                    'priority' => $this->priority
                ]);

                DynamicMailService::send($recipient, $this->mailable);

                Log::info('Email notification sent successfully', [
                    'recipient' => $recipient,
                    'notification_id' => $this->notificationId
                ]);
            }

            // Update notification status if notification ID is provided
            if ($this->notificationId) {
                $notification = Notification::find($this->notificationId);
                if ($notification) {
                    $data = $notification->data;
                    $data['email_sent'] = true;
                    $data['email_sent_at'] = now()->toDateTimeString();
                    $data['recipients_count'] = count($this->recipients);
                    $notification->update(['data' => $data]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'recipients' => $this->recipients,
                'mailable_class' => get_class($this->mailable),
                'notification_id' => $this->notificationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update notification with error status
            if ($this->notificationId) {
                $notification = Notification::find($this->notificationId);
                if ($notification) {
                    $data = $notification->data;
                    $data['email_failed'] = true;
                    $data['email_error'] = $e->getMessage();
                    $data['email_failed_at'] = now()->toDateTimeString();
                    $notification->update(['data' => $data]);
                }
            }

            throw $e; // Re-throw to trigger job failure and retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Email notification job failed permanently', [
            'recipients' => $this->recipients,
            'mailable_class' => get_class($this->mailable),
            'notification_id' => $this->notificationId,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage()
        ]);

        // Mark notification as permanently failed
        if ($this->notificationId) {
            $notification = Notification::find($this->notificationId);
            if ($notification) {
                $data = $notification->data;
                $data['email_permanently_failed'] = true;
                $data['email_final_error'] = $exception->getMessage();
                $data['email_failed_permanently_at'] = now()->toDateTimeString();
                $notification->update(['data' => $data]);
            }
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'email',
            'notification',
            'priority:' . $this->priority,
            'recipients:' . count($this->recipients)
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return $this->backoff;
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(30);
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [];
    }

    /**
     * Static method to dispatch email with proper priority handling
     */
    public static function dispatch($recipients, Mailable $mailable, $notificationId = null, $priority = 'normal')
    {
        $job = new static($recipients, $mailable, $notificationId, $priority);
        
        // Dispatch immediately for critical notifications
        if ($priority === 'critical') {
            return dispatch($job)->delay(0);
        }
        
        // Slight delay for other priorities to batch processing
        $delay = match ($priority) {
            'high' => 30, // 30 seconds
            'normal' => 60, // 1 minute
            default => 300 // 5 minutes for low priority
        };
        
        return dispatch($job)->delay($delay);
    }
}