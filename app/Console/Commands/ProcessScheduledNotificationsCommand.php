<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NotificationSchedule;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class ProcessScheduledNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send scheduled notifications via email, push and database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $now = Carbon::now();
            $this->info("📅 Processing scheduled notifications at {$now->format('Y-m-d H:i:s')}");
            
            // Get notifications that should be sent now
            $scheduledNotifications = NotificationSchedule::with(['template'])
                ->where('is_active', true)
                ->where('scheduled_at', '<=', $now)
                ->where(function($query) use ($now) {
                    $query->where('sent_at', null)
                          ->orWhere('is_recurring', true);
                })
                ->get();
            
            if ($scheduledNotifications->isEmpty()) {
                $this->info('✅ No scheduled notifications to process');
                return 0;
            }

            $notificationsSent = 0;
            
            foreach ($scheduledNotifications as $scheduled) {
                // Skip if already sent today (for recurring notifications)
                if ($scheduled->is_recurring && $scheduled->sent_at && 
                    $scheduled->sent_at->isToday()) {
                    continue;
                }
                
                // Get target users
                $users = $this->getTargetUsers($scheduled);
                
                if ($users->isEmpty()) {
                    $templateName = $scheduled->template ? $scheduled->template->name : $scheduled->id;
                    $this->warn("⚠️  No users found for scheduled notification: {$templateName}");
                    continue;
                }
                
                // Send notification to each user
                foreach ($users as $user) {
                    $notification = Notification::create([
                        'type' => $scheduled->template ? $scheduled->template->type : 'system',
                        'notifiable_type' => User::class,
                        'notifiable_id' => $user->id,
                        'data' => [
                            'title' => $scheduled->template ? $scheduled->template->subject : 'Notificación Programada',
                            'message' => $this->processTemplate($scheduled->template ? $scheduled->template->body : '', $user),
                            'action_url' => $scheduled->template->action_url,
                            'action_text' => $scheduled->template && $scheduled->template->action_text ? $scheduled->template->action_text : 'Ver más'
                        ],
                        'priority' => $scheduled->priority,
                        'channels' => $scheduled->channels,
                        'category' => $scheduled->template && $scheduled->template->category ? $scheduled->template->category : 'system',
                        'metadata' => [
                            'scheduled_notification_id' => $scheduled->id,
                            'template_id' => $scheduled->template_id,
                            'processed_at' => $now->toISOString()
                        ]
                    ]);
                    
                    $notificationsSent++;
                }
                
                // Update the scheduled notification
                $scheduled->update([
                    'sent_at' => $now,
                    'last_processed_at' => $now
                ]);
                
                // Handle recurring notifications
                if ($scheduled->is_recurring) {
                    $this->scheduleNext($scheduled);
                }
                
                $templateName = $scheduled->template ? $scheduled->template->name : $scheduled->id;
                $this->info("✅ Processed scheduled notification: {$templateName}");
            }

            $this->info("✅ Sent {$notificationsSent} scheduled notifications via email, push and database");
            
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error processing scheduled notifications: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get target users for a scheduled notification
     */
    private function getTargetUsers($scheduled)
    {
        $query = User::query();
        
        // Filter by roles if specified
        if (!empty($scheduled->target_roles)) {
            $roles = is_array($scheduled->target_roles) ? 
                     $scheduled->target_roles : 
                     json_decode($scheduled->target_roles, true);
            $query->whereIn('role', $roles);
        }
        
        // Filter by specific users if specified
        if (!empty($scheduled->target_users)) {
            $userIds = is_array($scheduled->target_users) ? 
                       $scheduled->target_users : 
                       json_decode($scheduled->target_users, true);
            $query->whereIn('id', $userIds);
        }
        
        return $query->get();
    }

    /**
     * Process template variables
     */
    private function processTemplate($template, $user)
    {
        $now = Carbon::now();
        
        $variables = [
            '{user_name}' => $user->name,
            '{user_email}' => $user->email,
            '{user_role}' => ucfirst($user->role),
            '{current_date}' => $now->format('d/m/Y'),
            '{current_time}' => $now->format('H:i'),
            '{current_datetime}' => $now->format('d/m/Y H:i'),
            '{system_name}' => 'AvoControl Pro'
        ];
        
        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    /**
     * Schedule next occurrence for recurring notifications
     */
    private function scheduleNext($scheduled)
    {
        if (!$scheduled->is_recurring || !$scheduled->recurring_pattern) {
            return;
        }
        
        $pattern = $scheduled->recurring_pattern;
        $nextScheduled = null;
        
        switch ($pattern) {
            case 'daily':
                $nextScheduled = $scheduled->scheduled_at->addDay();
                break;
            case 'weekly':
                $nextScheduled = $scheduled->scheduled_at->addWeek();
                break;
            case 'monthly':
                $nextScheduled = $scheduled->scheduled_at->addMonth();
                break;
        }
        
        if ($nextScheduled) {
            $scheduled->update(['scheduled_at' => $nextScheduled]);
        }
    }
}