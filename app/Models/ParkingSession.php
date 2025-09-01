<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;

class ParkingSession extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'parking_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'plate_number',
        'start_time',
        'end_time',
        'duration_minutes',
        'amount_paid',
        'printed',
        'created_by',
        'parking_rate_id',
        'branch_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'amount_paid' => 'decimal:2',
        'printed' => 'boolean',
        'duration_minutes' => 'integer',
    ];

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('parking_session')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Parking session for {$this->plate_number} has been {$eventName}");
    }

    /**
     * Get the user who created this session.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the plate associated with this session.
     */
    public function plate(): BelongsTo
    {
        return $this->belongsTo(Plate::class, 'plate_number', 'number');
    }

    /**
     * Get the parking rate used for this session.
     */
    public function parkingRate(): BelongsTo
    {
        return $this->belongsTo(ParkingRate::class);
    }

    /**
     * Get the branch that owns this parking session.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
    
    /**
     * Get the ticket for this session.
     */
    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }

    /**
     * Scope to get active sessions (not ended yet).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('end_time');
    }

    /**
     * Scope to get completed sessions.
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('end_time');
    }

    /**
     * Scope to get sessions by plate number.
     */
    public function scopeByPlateNumber($query, $plateNumber)
    {
        return $query->where('plate_number', $plateNumber);
    }

    /**
     * Check if session is active.
     */
    public function isActive(): bool
    {
        return is_null($this->end_time);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_minutes) {
            return 'N/A';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
        }

        return "{$minutes}m";
    }

    /**
     * Get formatted amount paid.
     */
    public function getFormattedAmountAttribute(): string
    {
        return $this->amount_paid ? '₱' . number_format($this->amount_paid, 2) : 'Free';
    }

    /**
     * Get status label.
     */
    public function getStatusAttribute(): string
    {
        return $this->isActive() ? 'Active' : 'Completed';
    }

    /**
     * Calculate and update session details when ending.
     */
    public function endSession($endTime = null): self
    {
        $endTime = $endTime ? Carbon::parse($endTime) : now();
        $startTime = Carbon::parse($this->start_time);

        // Calculate duration in minutes
        $durationMinutes = $startTime->diffInMinutes($endTime);

        // Get the parking rate used for this session, or fall back to active rate
        $parkingRate = $this->parkingRate ?: ParkingRate::getActiveRate();
        $amountPaid = 0;

        if ($parkingRate) {
            $amountPaid = $parkingRate->calculateFee($durationMinutes);
        }

        // Update session
        $this->update([
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'amount_paid' => $amountPaid,
        ]);

        return $this->fresh();
    }

    /**
     * Get current duration in minutes for active sessions.
     */
    public function getCurrentDurationMinutes(): int
    {
        if (!$this->isActive()) {
            return $this->duration_minutes ?? 0;
        }

        return Carbon::parse($this->start_time)->diffInMinutes(now());
    }

    /**
     * Get estimated current fee for active sessions.
     */
    public function getEstimatedCurrentFee(): float
    {
        if (!$this->isActive()) {
            return $this->amount_paid ?? 0;
        }

        // Use the parking rate associated with this session, or fall back to active rate
        $parkingRate = $this->parkingRate ?: ParkingRate::getActiveRate();
        if (!$parkingRate) {
            return 0;
        }

        $currentDurationMinutes = $this->getCurrentDurationMinutes();
        return $parkingRate->calculateFee($currentDurationMinutes);
    }

    /**
     * Calculate total fee based on session's parking rate and duration.
     */
    public function calculateTotalFee(?int $durationMinutes = null): float
    {
        $duration = $durationMinutes ?? $this->getCurrentDurationMinutes();
        
        // Use the parking rate associated with this session
        $parkingRate = $this->parkingRate;
        if (!$parkingRate) {
            return 0;
        }

        return $parkingRate->calculateFee($duration);
    }

    /**
     * Get the parking rate details used for this session.
     */
    public function getRateDetails(): array
    {
        $parkingRate = $this->parkingRate;
        if (!$parkingRate) {
            return [
                'rate_name' => 'No rate assigned',
                'rate_type' => null,
                'rate_amount' => 0,
                'grace_period' => 0,
                'formatted_rate_amount' => '₱0.00',
                'rate_type_label' => 'N/A'
            ];
        }

        return [
            'rate_name' => $parkingRate->name ?: 'Rate #' . $parkingRate->id,
            'rate_type' => $parkingRate->rate_type,
            'rate_amount' => $parkingRate->rate_amount,
            'grace_period' => $parkingRate->grace_period,
            'formatted_rate_amount' => $parkingRate->formatted_rate_amount,
            'rate_type_label' => $parkingRate->rate_type_label,
            'formatted_grace_period' => $parkingRate->formatted_grace_period
        ];
    }

    /**
     * Get fee breakdown for current session.
     */
    public function getFeeBreakdown(?int $durationMinutes = null): array
    {
        $duration = $durationMinutes ?? $this->getCurrentDurationMinutes();
        $parkingRate = $this->parkingRate;
        
        if (!$parkingRate || $duration <= 0) {
            return [
                'total_minutes' => $duration,
                'grace_period_minutes' => 0,
                'chargeable_minutes' => 0,
                'total_fee' => 0,
                'breakdown' => ['No rate assigned or invalid duration']
            ];
        }

        $gracePeriod = $parkingRate->grace_period ?? 0;
        $chargeableMinutes = max(0, $duration - $gracePeriod);
        $totalFee = $parkingRate->calculateFee($duration);
        
        $breakdown = [];
        $breakdown[] = "Total parking time: {$duration} minutes";
        
        if ($gracePeriod > 0) {
            $graceUsed = min($duration, $gracePeriod);
            $breakdown[] = "Grace period: {$graceUsed} minutes (free)";
        }
        
        if ($chargeableMinutes > 0) {
            if ($parkingRate->rate_type === 'hourly') {
                $chargeableHours = ceil($chargeableMinutes / 60);
                $breakdown[] = "Chargeable time: {$chargeableHours} hour(s) × {$parkingRate->formatted_rate_amount}";
            } else {
                $breakdown[] = "Chargeable time: {$chargeableMinutes} minutes × {$parkingRate->formatted_rate_amount}";
            }
        } else {
            $breakdown[] = "Within grace period - no charge";
        }
        
        return [
            'total_minutes' => $duration,
            'grace_period_minutes' => $gracePeriod,
            'chargeable_minutes' => $chargeableMinutes,
            'total_fee' => $totalFee,
            'breakdown' => $breakdown
        ];
    }

    /**
     * Boot the model and set up event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            // Auto-assign branch_id if user is an attendant and has a branch
            if (!$session->branch_id && auth()->check()) {
                $user = auth()->user();
                if ($user->hasRole('attendant') && $user->branch_id) {
                    $session->branch_id = $user->branch_id;
                }
            }
        });
    }
}
