<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'channel',
        'priority',
        'title_template',
        'message_template',
        'email_subject_template',
        'email_body_template',
        'push_title_template',
        'push_body_template',
        'variables',
        'conditions',
        'settings',
        'is_active',
        'is_system',
        'usage_count',
        'created_by',
        'updated_by',
        'last_used_at'
    ];

    protected $casts = [
        'variables' => 'array',
        'conditions' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime'
    ];

    protected static function booted()
    {
        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
            $template->created_by = auth()->id();
        });

        static::updating(function ($template) {
            $template->updated_by = auth()->id();
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    public function renderTemplate($templateField, $variables = [])
    {
        $template = $this->{$templateField};
        
        if (empty($template)) {
            return '';
        }

        // Replace variables in template
        foreach ($variables as $key => $value) {
            $placeholder = '{' . $key . '}';
            $template = str_replace($placeholder, $value, $template);
        }

        return $template;
    }

    public function render($variables = [])
    {
        return [
            'title' => $this->renderTemplate('title_template', $variables),
            'message' => $this->renderTemplate('message_template', $variables),
            'email_subject' => $this->renderTemplate('email_subject_template', $variables),
            'email_body' => $this->renderTemplate('email_body_template', $variables),
            'push_title' => $this->renderTemplate('push_title_template', $variables),
            'push_body' => $this->renderTemplate('push_body_template', $variables),
        ];
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function getAvailableVariables()
    {
        return $this->variables ?? [];
    }

    public function validateVariables($variables)
    {
        $required = collect($this->getAvailableVariables())
            ->filter(fn($var) => isset($var['required']) && $var['required'])
            ->keys()
            ->toArray();

        $missing = array_diff($required, array_keys($variables));

        return empty($missing) ? [] : $missing;
    }
}
