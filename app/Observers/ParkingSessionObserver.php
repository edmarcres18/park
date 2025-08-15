<?php

namespace App\Observers;

use App\Models\ParkingSession;
use App\Models\Ticket;

class ParkingSessionObserver
{
    /**
     * Handle the ParkingSession "created" event.
     */
    public function created(ParkingSession $parkingSession): void
    {
        // Auto-generate ticket when parking session is created
        $this->generateTicket($parkingSession);
    }

    /**
     * Handle the ParkingSession "updated" event.
     */
    public function updated(ParkingSession $parkingSession): void
    {
        // Update ticket time_out when session ends
        if ($parkingSession->wasChanged('end_time') && $parkingSession->end_time && $parkingSession->ticket) {
            $parkingSession->ticket->update([
                'time_out' => $parkingSession->end_time,
            ]);
        }
    }

    /**
     * Generate a ticket for the parking session.
     */
    private function generateTicket(ParkingSession $parkingSession): void
    {
        $ticketNumber = Ticket::generateTicketNumber($parkingSession->id, $parkingSession->plate_number);
        $currentRate = $parkingSession->parkingRate ? $parkingSession->parkingRate->rate_amount : 0;

        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'parking_session_id' => $parkingSession->id,
            'plate_number' => $parkingSession->plate_number,
            'time_in' => $parkingSession->start_time,
            'rate' => $currentRate,
            'qr_data' => [],
            'barcode' => $ticketNumber,
        ]);

        // Generate QR data after ticket is created
        $qrData = $ticket->generateQrData();
        $ticket->update(['qr_data' => $qrData]);

        event(new \App\Events\ParkingEvent(
            action: 'ticket_generated',
            title: 'Ticket Generated',
            message: "Ticket {$ticketNumber} created for plate #{$parkingSession->plate_number}.",
            type: 'success',
            link: (auth()->user() && auth()->user()->hasRole('admin'))
                ? route('admin.tickets.show', $ticket)
                : route('attendant.tickets.show', $ticket),
            initiatorId: auth()->id(),
            targetRole: 'admin',
        ));
    }
}
