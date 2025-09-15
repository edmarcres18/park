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
        'duration_minutes',
        'rate',
        'parking_slot',
        'is_printed',
        'qr_data',
        'barcode',
        'notes',
        'latitude',
        'longitude',
        'accuracy',
        'location_source',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'template_slug',
        'template_data',
        'branch_id',
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
            'duration_minutes' => 'integer',
            'is_printed' => 'boolean',
            'qr_data' => 'array',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'accuracy' => 'decimal:2',
            'template_data' => 'array',
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
     * Get the plate associated with this ticket.
     */
    public function plate(): BelongsTo
    {
        return $this->belongsTo(Plate::class, 'plate_number', 'number');
    }

    /**
     * Get the branch that owns this ticket.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Calculate duration in minutes between time_in and time_out.
     */
    public function calculateDuration(): ?int
    {
        if (!$this->time_in || !$this->time_out) {
            return null;
        }

        return $this->time_in->diffInMinutes($this->time_out);
    }

    /**
     * Update the duration field based on time_in and time_out.
     */
    public function updateDuration(): self
    {
        $this->duration_minutes = $this->calculateDuration();
        return $this;
    }

    /**
     * Get formatted duration as hours and minutes.
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
     * Get duration in hours as decimal.
     */
    public function getDurationHoursAttribute(): ?float
    {
        if (!$this->duration_minutes) {
            return null;
        }

        return round($this->duration_minutes / 60, 2);
    }

    /**
     * Check if ticket is completed (has time_out).
     */
    public function isCompleted(): bool
    {
        return !is_null($this->time_out);
    }


    /**
     * Generate a unique ticket number.
     * Format: PREFIX + PLATE_NUMBER + DDMMYY
     *
     * If called as an instance method, always use the related plate's number if available.
     * If called statically, use the provided plate number string.
     */
    /**
     * Generate a unique ticket number.
     * Format: PREFIX + PARKING_SESSION_ID + CLEAN_PLATE + mmddyy + SEQ
     *
     * @param int|null $parkingSessionId
     * @param string|null $plateNumber
     */
    public static function generateTicketNumber(int $parkingSessionId = null, string $plateNumber = null, self $ticket = null): string
    {
        $prefix = (string) config('app.ticket_prefix', 'MHRPS');
        if (empty($prefix)) {
            $prefix = 'MHRPS';
        }

        // If a Ticket instance is provided, use its related properties if available
        if ($ticket instanceof self && $ticket->relationLoaded('plate') && $ticket->plate) {
            $plateNumber = $ticket->plate->number;
        } elseif ($ticket instanceof self && $ticket->plate_number) {
            $plateNumber = $ticket->plate_number;
        }
        if ($ticket instanceof self && $parkingSessionId === null && $ticket->parking_session_id) {
            $parkingSessionId = (int) $ticket->parking_session_id;
        }

        // If plate number is missing or empty, try to fetch from Plate model
        if (!isset($plateNumber) || trim($plateNumber) === '') {
            $plate = \App\Models\Plate::first(); // fallback: get any plate (should not happen in real flow)
            if ($plate) {
                $plateNumber = $plate->number;
            }
        }

        // Ensure plate number is always a string and fallback to 'UNKNOWN' only if truly missing
        $plateNumber = isset($plateNumber) && trim($plateNumber) !== '' ? $plateNumber : 'UNKNOWN';

        // Clean plate number (remove spaces and special characters, only A-Z and 0-9)
        $cleanPlateNumber = preg_replace('/[^A-Z0-9]/', '', strtoupper($plateNumber));
        if (empty($cleanPlateNumber)) {
            $cleanPlateNumber = 'UNKNOWN';
        }

        // Session id segment
        $sessionSegment = (string) max(0, (int) ($parkingSessionId ?? 0));

        // Format: mmddyy as requested
        $date = now()->format('mdy');
        $base = $prefix . $sessionSegment . $cleanPlateNumber . $date;

        // Derive a sequence to ensure uniqueness for the same plate and day
        $sequence = (int) self::where('ticket_number', 'like', $base . '%')->count() + 1;
        $candidate = $base . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

        // In rare race conditions, increment until unique
        while (self::where('ticket_number', $candidate)->exists()) {
            $sequence++;
            $candidate = $base . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
        }

        return $candidate;
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

    /**
     * Get the ticket template associated with this ticket.
     */
    public function template()
    {
        return $this->belongsTo(TicketTemplate::class, 'template_slug', 'slug');
    }

    /**
     * Set location information for the ticket.
     */
    public function setLocation($latitude, $longitude, $accuracy = null, $source = 'gps', $address = null, $city = null, $state = null, $country = null, $postalCode = null): bool
    {
        return $this->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'location_source' => $source,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'postal_code' => $postalCode,
        ]);
    }

    /**
     * Get formatted location string.
     */
    public function getFormattedLocationAttribute(): string
    {
        if (!$this->latitude || !$this->longitude) {
            return 'Location not available';
        }

        $parts = [];

        if ($this->address) {
            $parts[] = $this->address;
        }

        if ($this->city) {
            $parts[] = $this->city;
        }

        if ($this->state) {
            $parts[] = $this->state;
        }

        if ($this->country) {
            $parts[] = $this->country;
        }

        if (empty($parts)) {
            return "Lat: {$this->latitude}, Lng: {$this->longitude}";
        }

        return implode(', ', $parts);
    }

    /**
     * Get location coordinates as array.
     */
    public function getLocationCoordinatesAttribute(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'accuracy' => $this->accuracy,
            'source' => $this->location_source,
        ];
    }

    /**
     * Check if ticket has location information.
     */
    public function hasLocation(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * Get template data for rendering.
     */
    public function getTemplateDataAttribute(): array
    {
        $defaultData = [
            'ticket_number' => $this->ticket_number,
            'plate_number' => $this->plate_number,
            'time_in' => $this->formatted_time_in,
            'time_out' => $this->formatted_time_out,
            'duration' => $this->duration,
            'rate' => $this->formatted_rate,
            'parking_slot' => $this->parking_slot ?? 'N/A',
            'location' => $this->formatted_location,
            'attendant' => $this->parkingSession->creator->name ?? 'N/A',
            'qr_code' => $this->ticket_number,
            'barcode' => $this->barcode,
        ];

        return array_merge($defaultData, $this->template_data ?? []);
    }

    /**
     * Render ticket with template.
     */
    public function renderWithTemplate($templateSlug = null)
    {
        $template = null;

        if ($templateSlug) {
            $template = TicketTemplate::where('slug', $templateSlug)->where('is_active', true)->first();
        }

        if (!$template) {
            $template = TicketTemplate::getDefault();
        }

        if (!$template) {
            // Fallback to default template
            return $this->renderDefaultTemplate();
        }

        $data = $this->template_data;
        return $template->render($data);
    }

    /**
     * Render default template.
     */
    private function renderDefaultTemplate()
    {
        // Return default template HTML
        return [
            'html' => view('tickets.templates.default', ['ticket' => $this])->render(),
            'css' => '',
        ];
    }

    /**
     * Get the total fee for this ticket, using the associated session's logic.
     */
    public function getTotalFeeAttribute(): float
    {
        $session = $this->parkingSession;
        if (!$session) {
            return 0;
        }
        // Use the session's calculateTotalFee method
        return $session->calculateTotalFee();
    }

    /**
     * Get the fee breakdown for this ticket, using the associated session's logic.
     */
    public function getFeeBreakdownAttribute(): array
    {
        $session = $this->parkingSession;
        if (!$session) {
            return [
                'total_minutes' => 0,
                'grace_period_minutes' => 0,
                'chargeable_minutes' => 0,
                'total_fee' => 0,
                'breakdown' => ['No session associated with this ticket']
            ];
        }
        return $session->getFeeBreakdown();
    }

    /**
     * Boot the model and set up event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            // Auto-assign branch_id if user is an attendant and has a branch
            if (!$ticket->branch_id && auth()->check()) {
                $user = auth()->user();
                if ($user->hasRole('attendant') && $user->branch_id) {
                    $ticket->branch_id = $user->branch_id;
                }
            }
        });

        // Automatically calculate duration when saving
        static::saving(function ($ticket) {
            if ($ticket->time_in && $ticket->time_out) {
                $ticket->duration_minutes = $ticket->calculateDuration();
            }
        });
    }
}
