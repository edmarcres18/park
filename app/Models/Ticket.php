<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;

class Ticket extends Model
{
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_number',
        'parking_session_id',
        'plate_number',
        'time_in',
        'time_out',
        'rate',
        'parking_slot',
        'is_printed',
        'qr_data',
        'barcode',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'time_in' => 'datetime',
            'time_out' => 'datetime',
            'is_printed' => 'boolean',
            'qr_data' => 'array',
        ];
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('ticket')
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Ticket {$this->ticket_number} has been {$eventName}");
    }

    /**
     * Get the parking session that owns the ticket.
     */
    public function parkingSession(): BelongsTo
    {
        return $this->belongsTo(ParkingSession::class);
    }

    /**
     * Generate a unique ticket number.
     * Format: PREFIX + PLATE_NUMBER + DDMMYY
     */
    public static function generateTicketNumber(string $plateNumber = null): string
    {
        $prefix = config('app.ticket_prefix', 'MHRPS');
        $plateNumber = $plateNumber ?: 'UNKNOWN';
        $date = now()->format('dmy'); // Day, Month, Year (last 2 digits)
        
        // Clean plate number (remove spaces and special characters)
        $cleanPlateNumber = preg_replace('/[^A-Z0-9]/', '', strtoupper($plateNumber));
        
        return $prefix . $cleanPlateNumber . $date;
    }

    /**
     * Get formatted rate in Philippine Peso.
     */
    public function getFormattedRateAttribute(): string
    {
        return '₱' . number_format($this->rate, 2);
    }

    /**
     * Get formatted time in attribute.
     */
    public function getFormattedTimeInAttribute(): string
    {
        return $this->time_in ? $this->time_in->format('M j, Y g:i A') : 'N/A';
    }

    /**
     * Get formatted time out attribute.
     */
    public function getFormattedTimeOutAttribute(): string
    {
        return $this->time_out ? $this->time_out->format('M j, Y g:i A') : 'N/A';
    }

    /**
     * Get duration in human readable format.
     */
    public function getDurationAttribute(): string
    {
        if (!$this->time_out) {
            return 'Ongoing';
        }

        $duration = $this->time_in->diffInMinutes($this->time_out);
        $hours = floor($duration / 60);
        $minutes = $duration % 60;

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
        }

        return "{$minutes}m";
    }

    /**
     * Generate QR code data for the ticket.
     */
    public function generateQrData(): array
    {
        return [
            'ticket_number' => $this->ticket_number,
            'plate_number' => $this->plate_number,
            'time_in' => $this->time_in->toISOString(),
            'rate' => $this->rate,
            'parking_slot' => $this->parking_slot,
            'url' => route('tickets.verify', $this->ticket_number)
        ];
    }

    /**
     * Generate barcode for the ticket.
     */
    public function generateBarcode(): string
    {
        return $this->ticket_number;
    }

    /**
     * Scope to get unprinted tickets.
     */
    public function scopeUnprinted($query)
    {
        return $query->where('is_printed', false);
    }

    /**
     * Scope to get printed tickets.
     */
    public function scopePrinted($query)
    {
        return $query->where('is_printed', true);
    }

    /**
     * Scope to filter by plate number.
     */
    public function scopeByPlateNumber($query, $plateNumber)
    {
        return $query->where('plate_number', 'LIKE', '%' . $plateNumber . '%');
    }

    /**
     * Mark ticket as printed.
     */
    public function markAsPrinted(): bool
    {
        return $this->update(['is_printed' => true]);
    }
}
