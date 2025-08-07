<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Plate extends Model
{
    use LogsActivity;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'number',
        'owner_name',
        'vehicle_type',
    ];

    /**
     * Get the parking sessions for this plate.
     */
    public function parkingSessions(): HasMany
    {
        return $this->hasMany(ParkingSession::class, 'plate_number', 'number');
    }

    /**
     * Get the tickets for this plate.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'plate_number', 'number');
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('plate')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Plate {$this->number} has been {$eventName}");
    }
}
