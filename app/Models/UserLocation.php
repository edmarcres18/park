<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserLocation extends Model
{
    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'accuracy',
        'altitude',
        'speed',
        'heading',
        'location_source',
        'address',
        'city',
        'country',
        'session_id',
        'is_active',
        'location_timestamp',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'altitude' => 'decimal:2',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'is_active' => 'boolean',
        'location_timestamp' => 'datetime',
    ];

    /**
     * Get the user that owns the location
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get current active location for a user
     */
    public static function getCurrentLocation($userId)
    {
        return self::where('user_id', $userId)
                   ->where('is_active', true)
                   ->latest('location_timestamp')
                   ->first();
    }

    /**
     * Get location history for a user
     */
    public static function getLocationHistory($userId, $hours = 24)
    {
        return self::where('user_id', $userId)
                   ->where('location_timestamp', '>=', Carbon::now()->subHours($hours))
                   ->orderBy('location_timestamp', 'desc')
                   ->get();
    }

    /**
     * Get users within a radius (in kilometers)
     */
    public static function getUsersWithinRadius($latitude, $longitude, $radiusKm = 1)
    {
        // Using Haversine formula for distance calculation
        $earthRadius = 6371; // Earth's radius in kilometers
        
        return self::selectRaw(
            "*, 
            ( {$earthRadius} * acos( cos( radians(?) ) * 
            cos( radians( latitude ) ) * 
            cos( radians( longitude ) - radians(?) ) + 
            sin( radians(?) ) * 
            sin( radians( latitude ) ) ) ) AS distance",
            [$latitude, $longitude, $latitude]
        )
        ->where('is_active', true)
        ->havingRaw('distance < ?', [$radiusKm])
        ->orderBy('distance')
        ->with('user')
        ->get();
    }

    /**
     * Update user location and deactivate previous locations
     */
    public static function updateUserLocation($userId, $locationData)
    {
        // Deactivate previous active locations
        self::where('user_id', $userId)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Create new active location
        return self::create(array_merge($locationData, [
            'user_id' => $userId,
            'is_active' => true,
            'location_timestamp' => now(),
        ]));
    }

    /**
     * Get location accuracy label
     */
    public function getAccuracyLabelAttribute()
    {
        if (!$this->accuracy) return 'Unknown';
        
        if ($this->accuracy <= 5) return 'Excellent';
        if ($this->accuracy <= 10) return 'Good';
        if ($this->accuracy <= 50) return 'Fair';
        return 'Poor';
    }

    /**
     * Get formatted coordinates
     */
    public function getFormattedCoordinatesAttribute()
    {
        return number_format($this->latitude, 6) . ', ' . number_format($this->longitude, 6);
    }

    /**
     * Get time since location was recorded
     */
    public function getTimeSinceAttribute()
    {
        return $this->location_timestamp->diffForHumans();
    }
}
