<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\ParkingSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Services\TicketTemplateConfigService;

class TicketApiController extends Controller
{
    protected $ticketConfigService;

    public function __construct(TicketTemplateConfigService $ticketConfigService)
    {
        $this->ticketConfigService = $ticketConfigService;
        $this->middleware(['auth:sanctum', 'role:attendant']);
    }

    /**
     * List tickets for the authenticated attendant, limited to their sessions (ended only).
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $printStatus = $request->get('print_status'); // 'printed' | 'unprinted' | null
        $plate = $request->get('plate_number');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Ticket::query()
            ->with(['parkingSession.creator', 'parkingSession.parkingRate'])
            ->whereHas('parkingSession', function ($q) {
                $q->where('created_by', auth()->id())
                  ->whereNotNull('end_time');
            })
            ->orderByDesc('created_at');

        if ($printStatus === 'printed') {
            $query->printed();
        } elseif ($printStatus === 'unprinted') {
            $query->unprinted();
        }

        if (!empty($plate)) {
            $query->byPlateNumber($plate);
        }

        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $tickets = $query->paginate($perPage);

        $data = $tickets->getCollection()->map(function (Ticket $t) {
            return [
                'id' => $t->id,
                'ticket_number' => $t->ticket_number,
                'plate_number' => $t->plate_number,
                'time_in' => optional($t->time_in)->format('F d, Y h:i:s A'),
                'time_out' => optional($t->time_out)->format('F d, Y h:i:s A'),
                'rate' => (float) $t->rate,
                'parking_slot' => $t->parking_slot,
                'is_printed' => (bool) $t->is_printed,
                'created_at' => $t->created_at?->format('F d, Y h:i:s A'),
                'session' => [
                    'id' => $t->parkingSession?->id,
                    'status' => $t->parkingSession?->status,
                ],
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'last_page' => $tickets->lastPage(),
                'from' => $tickets->firstItem(),
                'to' => $tickets->lastItem(),
            ],
        ]);
    }

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

        // Ensure the authenticated attendant only accesses their own session's tickets
        if (!$ticket->parkingSession || $ticket->parkingSession->created_by !== auth()->id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $ticketConfig = $this->ticketConfigService->getConfig();
        return response()->json([
            'ticket' => $ticket,
            'ticket_config' => $ticketConfig,
        ]);
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

        // Ensure the session belongs to the authenticated attendant
        if ($parkingSession->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to generate a ticket for this session.'
            ], 403);
        }

        // Prevent ticket creation if plate_number is missing or empty (parity with web controller)
        if (!isset($parkingSession->plate_number) || trim($parkingSession->plate_number) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot generate ticket: Parking session has no valid plate number.'
            ], 422);
        }

        // Check if ticket already exists
        if ($parkingSession->ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket already exists for this session',
                'ticket_id' => $parkingSession->ticket->id
            ]);
        }

        // Generate ticket number using: PREFIX + session_id + plate + mmddyy
        $ticketNumber = Ticket::generateTicketNumber($parkingSession->id, $parkingSession->plate_number);
        $currentRate = $parkingSession->parkingRate ? $parkingSession->parkingRate->rate_amount : 0;

        // Prefer auto-generation via ParkingSessionObserver. If ticket does not exist (fallback), create it.
        $ticket = $parkingSession->ticket;
        if (!$ticket) {
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
        }

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

        $ticketConfig = $this->ticketConfigService->getConfig();
        return response()->json([
            'success' => true,
            'message' => 'Ticket generated successfully',
            'ticket' => $ticket->load(['parkingSession.creator', 'parkingSession.parkingRate']),
            'ticket_config' => $ticketConfig,
        ]);
    }

    /**
     * Mark ticket as printed (attendant-only, own session).
     */
    public function markPrinted(Ticket $ticket)
    {
        // Ownership check
        if (!$ticket->parkingSession || $ticket->parkingSession->created_by !== auth()->id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $ticket->markAsPrinted();

        return response()->json([
            'success' => true,
            'message' => 'Ticket marked as printed successfully.',
            'ticket' => $ticket->fresh(['parkingSession'])
        ]);
    }
}
