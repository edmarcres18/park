<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'plate_number' => $this->plate_number,
            'time_in' => $this->time_in ? $this->time_in->toISOString() : null,
            'time_out' => $this->time_out ? $this->time_out->toISOString() : null,
            'formatted_time_in' => $this->formatted_time_in,
            'formatted_time_out' => $this->formatted_time_out,
            'duration' => $this->duration,
            'rate' => (float) $this->rate,
            'formatted_rate' => $this->formatted_rate, // Already includes ₱ symbol
            'currency' => 'PHP',
            'parking_slot' => $this->parking_slot,
            'is_printed' => (bool) $this->is_printed,
            'barcode' => $this->barcode,
            'qr_data' => $this->qr_data,
            'notes' => $this->notes,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'accuracy' => $this->accuracy,
                'source' => $this->location_source,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
                'formatted_location' => $this->formatted_location,
            ],
            'template_slug' => $this->template_slug,
            'total_fee' => (float) $this->total_fee,
            'fee_breakdown' => $this->fee_breakdown,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'parking_session' => [
                'id' => $this->whenLoaded('parkingSession', function () {
                    return $this->parkingSession->id;
                }),
                'start_time' => $this->whenLoaded('parkingSession', function () {
                    return $this->parkingSession->start_time ? $this->parkingSession->start_time->toISOString() : null;
                }),
                'end_time' => $this->whenLoaded('parkingSession', function () {
                    return $this->parkingSession->end_time ? $this->parkingSession->end_time->toISOString() : null;
                }),
                'duration_minutes' => $this->whenLoaded('parkingSession', function () {
                    return $this->parkingSession->duration_minutes;
                }),
                'amount_paid' => $this->whenLoaded('parkingSession', function () {
                    return (float) $this->parkingSession->amount_paid;
                }),
                'is_active' => $this->whenLoaded('parkingSession', function () {
                    return is_null($this->parkingSession->end_time);
                }),
            ],

            'creator' => $this->whenLoaded('parkingSession.creator', function () {
                return [
                    'id' => $this->parkingSession->creator->id,
                    'name' => $this->parkingSession->creator->name,
                    'email' => $this->parkingSession->creator->email,
                ];
            }),

            'parking_rate' => $this->whenLoaded('parkingSession.parkingRate', function () {
                return [
                    'id' => $this->parkingSession->parkingRate->id,
                    'name' => $this->parkingSession->parkingRate->name,
                    'rate_amount' => (float) $this->parkingSession->parkingRate->rate_amount,
                    'formatted_rate' => '₱' . number_format($this->parkingSession->parkingRate->rate_amount, 2),
                    'currency' => 'PHP',
                    'time_unit' => $this->parkingSession->parkingRate->time_unit,
                    'grace_period_minutes' => $this->parkingSession->parkingRate->grace_period_minutes,
                ];
            }),

            'template' => $this->whenLoaded('template', function () {
                return [
                    'slug' => $this->template->slug,
                    'name' => $this->template->name,
                    'is_active' => (bool) $this->template->is_active,
                ];
            }),
        ];
    }
}
