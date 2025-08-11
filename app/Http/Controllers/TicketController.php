<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\ParkingSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\TicketTemplateConfigService;

class TicketController extends Controller
{
    protected $ticketConfigService;

    public function __construct(TicketTemplateConfigService $ticketConfigService)
    {
        $this->ticketConfigService = $ticketConfigService;
    }

    /**
     * Display a listing of the tickets.
     */
    public function index(Request $request)
    {
        // Start with simple query
        $query = Ticket::query()->orderBy('created_at', 'desc');

        // Apply role-based filtering
        $query = $this->filterTicketsByRole($query);

        // Filter by plate number
        if ($request->filled('plate_number')) {
            $query->byPlateNumber($request->plate_number);
        }

        // Filter by print status
        if ($request->filled('print_status')) {
            if ($request->print_status === 'printed') {
                $query->printed();
            } elseif ($request->print_status === 'unprinted') {
                $query->unprinted();
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $tickets = $query->paginate(15);

        // Load relationships after pagination to avoid issues
        $tickets->load(['parkingSession.creator', 'parkingSession.parkingRate']);

        if (auth()->user()->hasRole('admin')) {
            return view('tickets.index_admin', compact('tickets'));
        } else {
            return view('tickets.index_attendant', compact('tickets'));
        }
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        $activeSessions = ParkingSession::with(['parkingRate'])
            ->active()
            ->orderBy('start_time', 'desc')
            ->get();

        return view('tickets.create', compact('activeSessions'));
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'parking_session_id' => 'required|exists:parking_sessions,id',
            'parking_slot' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'location_source' => 'nullable|string|in:gps,network,passive,fused',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'template_slug' => 'nullable|string|exists:ticket_templates,slug',
        ]);

        $parkingSession = ParkingSession::with('parkingRate')->findOrFail($request->parking_session_id);

        // Prevent ticket creation if plate_number is missing or empty
        if (!isset($parkingSession->plate_number) || trim($parkingSession->plate_number) === '') {
            return redirect()->back()->with('error', 'Cannot generate ticket: Parking session has no valid plate number.');
        }

        // Check if ticket already exists for this session
        if ($parkingSession->ticket) {
            return redirect()->back()->with('error', 'Ticket already exists for this parking session.');
        }

        $ticketNumber = Ticket::generateTicketNumber($parkingSession->plate_number);
        $currentRate = $parkingSession->parkingRate ? $parkingSession->parkingRate->rate_amount : 0;

        // Get default template if none specified
        $templateSlug = $request->template_slug;
        if (!$templateSlug) {
            $defaultTemplate = \App\Models\TicketTemplate::getDefault();
            $templateSlug = $defaultTemplate ? $defaultTemplate->slug : null;
        }

        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'parking_session_id' => $parkingSession->id,
            'plate_number' => $parkingSession->plate_number,
            'time_in' => $parkingSession->start_time,
            'rate' => $currentRate,
            'parking_slot' => $request->parking_slot,
            'notes' => $request->notes,
            'qr_data' => [],
            'barcode' => $ticketNumber,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'location_source' => $request->location_source ?? 'gps',
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'template_slug' => $templateSlug,
        ]);

        // Generate QR data
        $ticket->update(['qr_data' => $ticket->generateQrData()]);

        event(new \App\Events\ParkingEvent(
            action: 'ticket_generated',
            title: 'Ticket Generated',
            message: "Ticket {$ticketNumber} created for plate #{$parkingSession->plate_number}.",
            type: 'success',
            link: auth()->user() && auth()->user()->hasRole('admin')
                ? route('admin.tickets.show', $ticket)
                : route('attendant.tickets.show', $ticket),
            initiatorId: auth()->id(),
            targetRole: 'admin',
        ));

        return redirect()
            ->route(auth()->user() && auth()->user()->hasRole('admin') ? 'admin.tickets.show' : 'attendant.tickets.show', $ticket)
            ->with('success', 'Ticket generated successfully.');
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['parkingSession.creator', 'parkingSession.parkingRate']);
        $ticketConfig = $this->ticketConfigService->getConfig();
        if (auth()->user()->hasRole('admin')) {
            return view('tickets.show_admin', compact('ticket', 'ticketConfig'));
        } else {
            return view('tickets.show_attendant', compact('ticket', 'ticketConfig'));
        }
    }

    /**
     * Show the ticket print view.
     */
    public function print(Ticket $ticket)
    {
        $ticket->load(['parkingSession.creator', 'parkingSession.parkingRate', 'template']);
        $template = null;
        if ($ticket->template_slug) {
            $template = \App\Models\TicketTemplate::where('slug', $ticket->template_slug)
                                                   ->where('is_active', true)
                                                   ->first();
        }
        if (!$template) {
            $template = \App\Models\TicketTemplate::getDefault();
        }
        $ticketConfig = $this->ticketConfigService->getConfig();
        return view('tickets.print', compact('ticket', 'template', 'ticketConfig'));
    }

    /**
     * Mark ticket as printed.
     */
    public function markPrinted(Ticket $ticket)
    {
        $ticket->markAsPrinted();

        return response()->json([
            'success' => true,
            'message' => 'Ticket marked as printed successfully.'
        ]);
    }

    /**
     * Verify ticket by ticket number.
     */
    public function verify($ticketNumber)
    {
        $ticket = Ticket::with(['parkingSession.creator', 'parkingSession.parkingRate'])
            ->where('ticket_number', $ticketNumber)
            ->first();

        if (!$ticket) {
            abort(404, 'Ticket not found');
        }

        return view('tickets.verify', compact('ticket'));
    }

    /**
     * Generate a new ticket for a parking session.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'parking_session_id' => 'required|exists:parking_sessions,id',
            'parking_slot' => 'nullable|string|max:20',
        ]);

        $parkingSession = ParkingSession::with('parkingRate')->findOrFail($request->parking_session_id);

        // Check if ticket already exists
        if ($parkingSession->ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket already exists for this session',
                'ticket_id' => $parkingSession->ticket->id
            ]);
        }

        $ticketNumber = Ticket::generateTicketNumber($parkingSession->plate_number);
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
            'print_url' => (auth()->user() && auth()->user()->hasRole('admin'))
                ? route('admin.tickets.print', $ticket)
                : route('attendant.tickets.print', $ticket)
        ]);
    }

    /**
     * Bulk print tickets.
     */
    public function bulkPrint(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id'
        ]);

        $tickets = Ticket::with(['parkingSession.creator', 'parkingSession.parkingRate'])
            ->whereIn('id', $request->ticket_ids)
            ->get();

        return view('tickets.bulk-print', compact('tickets'));
    }

    /**
     * Get tickets statistics (Admin only).
     */
    public function statistics()
    {
        // Check if user is admin
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        $stats = [
            'total_tickets' => Ticket::count(),
            'printed_tickets' => Ticket::printed()->count(),
            'unprinted_tickets' => Ticket::unprinted()->count(),
            'today_tickets' => Ticket::whereDate('created_at', today())->count(),
            'this_month_tickets' => Ticket::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Filter tickets based on user role.
     */
    private function filterTicketsByRole($query)
    {
        $user = Auth::user();

        // Both admin and attendant can see all tickets
        // No filtering needed - attendants have same data access as admins
        return $query;
    }
}
