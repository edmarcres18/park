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
     * Validate plate number format.
     *
     * @param string $number
     * @return bool
     */
    public static function isValidFormat(string $number): bool
    {
        return preg_match('/^([A-Z]{3}\s?\d{3,4}|[A-Z]{2}\s?\d{5})$/', $number) === 1;
    }

    /**
     * Format plate number consistently.
     *
     * @param string $number
     * @return string
     */
    public static function formatNumber(string $number): string
    {
        // Remove all spaces and convert to uppercase
        $cleaned = strtoupper(trim($number));

        // Remove any non-alphanumeric characters
        $cleaned = preg_replace('/[^A-Z0-9]/', '', $cleaned);

        // Apply formatting based on pattern
        if (preg_match('/^([A-Z]{3})(\d{3,4})$/', $cleaned, $matches)) {
            // Old format (AAA 123) or New format (AAA 1234)
            return $matches[1] . ' ' . $matches[2];
        } elseif (preg_match('/^([A-Z]{2})(\d{5})$/', $cleaned, $matches)) {
            // Motorcycle format (AA 12345)
            return $matches[1] . ' ' . $matches[2];
        }

        // If no pattern matches, return as is
        return $number;
    }

    /**
     * Mutator to format plate number before saving.
     *
     * @param string $value
     * @return void
     */
    public function setNumberAttribute($value): void
    {
        $this->attributes['number'] = self::formatNumber($value);
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
