<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Plate extends Model
{
    use HasFactory, LogsActivity;
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
     * Check if a plate number already exists.
     *
     * @param string $number
     * @param int|null $excludeId
     * @return bool
     */
    public static function numberExists(string $number, ?int $excludeId = null): bool
    {
        $query = static::where('number', $number);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Find a plate by number.
     *
     * @param string $number
     * @return static|null
     */
    public static function findByNumber(string $number): ?static
    {
        return static::where('number', $number)->first();
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
