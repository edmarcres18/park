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
     * Validate Philippine LTO plate number format (2025).
     * Supports all valid formats: cars, motorcycles, tricycles, EVs, hybrids, vintage.
     *
     * @param string $number
     * @return bool
     */
    public static function isValidFormat(string $number): bool
    {
        // Clean and normalize the input
        $cleaned = strtoupper(trim($number));

        // Remove spaces and dashes for validation
        $normalized = preg_replace('/[\s\-]/', '', $cleaned);

        // Comprehensive regex for all Philippine plate formats (2025)
        $patterns = [
            // Standard vehicles (Cars, SUVs, Trucks, Buses, PUVs): LLL-DDDD
            '/^[A-Z]{3}\d{4}$/',

            // Motorcycles: LL-DDD-L, D-LLL-DD, L-D-L-DDD, LL-DDDD, D-LL-DDD
            '/^[A-Z]{2}\d{3}[A-Z]$/',
            '/^[A-Z]\d{3}[A-Z]{2}$/',
            '/^[A-Z]\d{1}[A-Z]\d{3}$/',
            '/^[A-Z]{2}\d{4}$/',
            '/^[A-Z]\d{2}[A-Z]\d{3}$/',

            // Tricycles (Yellow plates): LL-DDDD
            '/^[A-Z]{2}\d{4}$/',

            // Electric Vehicles (EV): E-LLL-DDD
            '/^E[A-Z]{3}\d{3}$/',

            // Hybrid Vehicles: H-LLL-DDD
            '/^H[A-Z]{3}\d{3}$/',

            // Vintage/Classic: V-LLL-DDD
            '/^V[A-Z]{3}\d{3}$/',

            // Government: G-LLL-DDD
            '/^G[A-Z]{3}\d{3}$/',

            // Diplomatic: D-LLL-DDD
            '/^D[A-Z]{3}\d{3}$/',

            // Temporary/Conduction: T-LLL-DDD
            '/^T[A-Z]{3}\d{3}$/',

            // Special formats for specific vehicle types
            '/^[A-Z]{3}\d{3}$/', // Some older formats
            '/^[A-Z]{2}\d{5}$/', // Extended motorcycle format
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $normalized)) {
                return true;
            }
        }

        return false;
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
        $normalized = preg_replace('/[\s\-]/', '', $cleaned);

        // Apply formatting based on pattern
        if (preg_match('/^([A-Z]{3})(\d{4})$/', $normalized, $matches)) {
            // Standard vehicles: LLL-DDDD
            return $matches[1] . '-' . $matches[2];
        } elseif (preg_match('/^([A-Z]{2})(\d{3})([A-Z])$/', $normalized, $matches)) {
            // Motorcycles: LL-DDD-L
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        } elseif (preg_match('/^([A-Z])(\d{3})([A-Z]{2})$/', $normalized, $matches)) {
            // Motorcycles: D-LLL-DD
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        } elseif (preg_match('/^([A-Z])(\d{1})([A-Z])(\d{3})$/', $normalized, $matches)) {
            // Motorcycles: L-D-L-DDD
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3] . '-' . $matches[4];
        } elseif (preg_match('/^([A-Z]{2})(\d{4})$/', $normalized, $matches)) {
            // Motorcycles/Tricycles: LL-DDDD
            return $matches[1] . '-' . $matches[2];
        } elseif (preg_match('/^([A-Z])(\d{2})([A-Z])(\d{3})$/', $normalized, $matches)) {
            // Motorcycles: D-LL-DDD
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3] . '-' . $matches[4];
        } elseif (preg_match('/^([EVHGD])([A-Z]{3})(\d{3})$/', $normalized, $matches)) {
            // Special categories: E/H/V/G/D-LLL-DDD
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        } elseif (preg_match('/^([A-Z]{3})(\d{3})$/', $normalized, $matches)) {
            // Older formats: LLL-DDD
            return $matches[1] . '-' . $matches[2];
        } elseif (preg_match('/^([A-Z]{2})(\d{5})$/', $normalized, $matches)) {
            // Extended motorcycle: LL-DDDDD
            return $matches[1] . '-' . $matches[2];
        }

        // If no pattern matches, return as is
        return $number;
    }

    /**
     * Detect vehicle category from plate number.
     *
     * @param string $number
     * @return string
     */
    public static function detectVehicleCategory(string $number): string
    {
        $cleaned = strtoupper(trim($number));
        $normalized = preg_replace('/[\s\-]/', '', $cleaned);

        // Electric Vehicle
        if (preg_match('/^E[A-Z]{3}\d{3}$/', $normalized)) {
            return 'Electric Vehicle';
        }

        // Hybrid Vehicle
        if (preg_match('/^H[A-Z]{3}\d{3}$/', $normalized)) {
            return 'Hybrid Vehicle';
        }

        // Vintage/Classic
        if (preg_match('/^V[A-Z]{3}\d{3}$/', $normalized)) {
            return 'Vintage/Classic';
        }

        // Government
        if (preg_match('/^G[A-Z]{3}\d{3}$/', $normalized)) {
            return 'Government';
        }

        // Diplomatic
        if (preg_match('/^D[A-Z]{3}\d{3}$/', $normalized)) {
            return 'Diplomatic';
        }

        // Temporary/Conduction
        if (preg_match('/^T[A-Z]{3}\d{3}$/', $normalized)) {
            return 'Temporary/Conduction';
        }

        // Motorcycle patterns
        if (preg_match('/^[A-Z]{2}\d{3}[A-Z]$|^[A-Z]\d{3}[A-Z]{2}$|^[A-Z]\d{1}[A-Z]\d{3}$|^[A-Z]{2}\d{4}$|^[A-Z]\d{2}[A-Z]\d{3}$/', $normalized)) {
            return 'Motorcycle';
        }

        // Standard vehicle (default)
        return 'Standard Vehicle';
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
