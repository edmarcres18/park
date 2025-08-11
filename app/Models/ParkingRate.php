<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ParkingRate extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rate_type',
        'rate_amount',
        'grace_period',
        'is_active',
        'name',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'grace_period' => 'integer',
    ];

    /**
     * The rate type enum values.
     */
    public const RATE_TYPES = [
        'hourly' => 'Hourly',
        'minutely' => 'Per Minute',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one rate can be active at a time
        static::saving(function ($rate) {
            if ($rate->is_active) {
                // Deactivate all other rates
                static::where('id', '!=', $rate->id)->update(['is_active' => false]);
            }
        });
    }

    /**
     * Scope to get only active rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only inactive rates.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get the currently active rate.
     */
    public static function getActiveRate()
    {
        return static::active()->first();
    }

    /**
     * Get formatted rate amount with currency.
     */
    public function getFormattedRateAmountAttribute()
    {
        return '₱' . number_format($this->rate_amount, 2);
    }

    /**
     * Get rate type label.
     */
    public function getRateTypeLabelAttribute()
    {
        return self::RATE_TYPES[$this->rate_type] ?? $this->rate_type;
    }

    /**
     * Get grace period formatted.
     */
    public function getFormattedGracePeriodAttribute()
    {
        if (!$this->grace_period) {
            return 'No grace period';
        }

        $minutes = $this->grace_period;
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;

            if ($remainingMinutes > 0) {
                return "{$hours}h {$remainingMinutes}m";
            }
            return "{$hours}h";
        }

        return "{$minutes}m";
    }

    /**
     * Calculate parking fee based on duration.
     *
     * @param int $durationMinutes
     * @return float
     */
    public function calculateFee(int $durationMinutes): float
    {
        // Apply grace period
        if ($this->grace_period && $durationMinutes <= $this->grace_period) {
            return 0.00;
        }

        $chargeableMinutes = $this->grace_period
            ? max(0, $durationMinutes - $this->grace_period)
            : $durationMinutes;

        if ($this->rate_type === 'hourly') {
            // Round up to the nearest hour
            $hours = ceil($chargeableMinutes / 60);
            return $hours * $this->rate_amount;
        } else {
            // Per minute billing
            return $chargeableMinutes * $this->rate_amount;
        }
    }
}
