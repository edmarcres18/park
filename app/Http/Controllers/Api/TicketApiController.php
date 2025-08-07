<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\ParkingSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TicketApiController extends Controller
{
    /**
     * Retrieve ticket by ID.
     */
    public function show($id)
    {
        $ticket = Ticket::with(['parkingSession.creator', 'parkingSession.parkingRate'])
            ->find($id);

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        return response()->json($ticket);
    }

    /**
     * Generate a new ticket for a parking session via API.
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parking_session_id' => 'required|exists:parking_sessions,id',
            'parking_slot' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $parkingSession = ParkingSession::with('parkingRate')->findOrFail($request->parking_session_id);

        // Check if ticket already exists
        if ($parkingSession->ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket already exists for this session',
                'ticket_id' => $parkingSession->ticket->id
            ]);
        }

        $ticketNumber = Ticket::generateTicketNumber();
        $currentRate = $parkingSession->parkingRate ? $parkingSession->parkingRate->rate_amount : 0;

        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'parking_session_id' => $parkingSession->id,
            'plate_number' => $parkingSession->plate_number,
            'time_in' => $parkingSession->start_time,
            'rate' => $currentRate,
            'parking_slot' => $request->parking_slot,
            'qr_data' => [],
            'barcode' => $ticketNumber,
        ]);

        // Generate QR data
        $ticket->update(['qr_data' => $ticket->generateQrData()]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket generated successfully',
            'ticket' => $ticket->load(['parkingSession.creator', 'parkingSession.parkingRate']),
        ]);
    }
}
