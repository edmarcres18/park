<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity, HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'branch_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get count of active attendant users
     *
     * @return int
     */
    public static function getActiveAttendantsCount(): int
    {
        return self::whereHas('roles', function ($query) {
            $query->where('name', 'attendant');
        })->where('status', 'active')->count();
    }

    /**
     * Get count of pending attendant users
     *
     * @return int
     */
    public static function getPendingAttendantsCount(): int
    {
        return self::whereHas('roles', function ($query) {
            $query->where('name', 'attendant');
        })->where('status', 'pending')->count();
    }

    /**
     * Get count of rejected attendant users
     *
     * @return int
     */
    public static function getRejectedAttendantsCount(): int
    {
        return self::whereHas('roles', function ($query) {
            $query->where('name', 'attendant');
        })->where('status', 'rejected')->count();
    }

    /**
     * Get all user counts by status
     *
     * @return array
     */
    public static function getUserCountsByStatus(): array
    {
        return [
            'active' => self::getActiveAttendantsCount(),
            'pending' => self::getPendingAttendantsCount(),
            'rejected' => self::getRejectedAttendantsCount(),
        ];
    }
    /**
     * Get all locations for this user
     */
    public function locations(): HasMany
    {
        return $this->hasMany(UserLocation::class);
    }

    /**
     * Get current active location for this user
     */
    public function currentLocation(): HasOne
    {
        return $this->hasOne(UserLocation::class)
                    ->where('is_active', true)
                    ->latest('location_timestamp');
    }

    /**
     * Get location history for this user
     */
    public function locationHistory($hours = 24)
    {
        return $this->locations()
                    ->where('location_timestamp', '>=', now()->subHours($hours))
                    ->orderBy('location_timestamp', 'desc');
    }

    /**
     * Check if user has location tracking enabled
     */
    public function hasLocationTracking(): bool
    {
        return $this->locations()->exists();
    }

    /**
     * Get the last known location timestamp
     */
    public function getLastLocationTimestampAttribute()
    {
        $location = $this->currentLocation;
        return $location ? $location->location_timestamp : null;
    }

    /**
     * Check if user is currently online (location updated within last 5 minutes)
     */
    public function isOnline(): bool
    {
        $location = $this->currentLocation;
        if (!$location) return false;
        
        return $location->location_timestamp->gt(now()->subMinutes(5));
    }

    /**
     * Get the branch that this user belongs to (for attendant users)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()  // Log all fillable attributes
            ->logOnlyDirty() // Only log when attributes actually change
            ->setDescriptionForEvent(fn(string $eventName) => "User has been {$eventName}");
    }
}
