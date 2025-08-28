<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Notification extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'message',
        'type',
        'data',
        'scheduled_at',
        'sent_at',
        'status',
        'target_audience',
        'target_users',
        'priority',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'target_users' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the user who created this notification
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for pending notifications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for scheduled notifications that are ready to be sent
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', 'pending')
                    ->where(function ($q) {
                        $q->whereNull('scheduled_at')
                          ->orWhere('scheduled_at', '<=', now());
                    });
    }

    /**
     * Scope for notifications by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for notifications by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark notification as failed
     */
    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }

    /**
     * Cancel notification
     */
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Get target users for this notification
     */
    public function getTargetUsers()
    {
        switch ($this->target_audience) {
            case 'all':
                return User::where('status', 'active')->get();
            
            case 'attendants':
                return User::whereHas('roles', function ($query) {
                    $query->where('name', 'attendant');
                })->where('status', 'active')->get();
            
            case 'customers':
                return User::whereHas('roles', function ($query) {
                    $query->where('name', 'customer');
                })->where('status', 'active')->get();
            
            case 'specific':
                if ($this->target_users) {
                    return User::whereIn('id', $this->target_users)
                              ->where('status', 'active')
                              ->get();
                }
                return collect();
            
            default:
                return collect();
        }
    }

    /**
     * Check if notification is scheduled for future
     */
    public function isScheduled(): bool
    {
        return $this->scheduled_at && $this->scheduled_at->isFuture();
    }

    /**
     * Get activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Notification has been {$eventName}");
    }
}
