<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\ParkingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TicketTemplateConfigService;
use Spatie\Activitylog\Models\Activity;

class TicketApiController extends Controller
{
    protected $ticketConfigService;

    public function __construct(TicketTemplateConfigService $ticketConfigService)
    {
        $this->middleware(['auth:sanctum', 'role:attendant']);
        $this->ticketConfigService = $ticketConfigService;
    }

    /**
     * Get tickets for the authenticated attendant.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = Ticket::query()
            ->with(['parkingSession.creator', 'parkingSession.parkingRate'])
            ->orderBy('created_at', 'desc');

        // Filter by branch for attendant users
        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } else {
            // Fallback to user-created sessions if no branch assigned
            $query->whereHas('parkingSession', function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->whereNotNull('end_time');
            });
        }

        // Filter by plate number
        if ($request->filled('plate_number')) {
            $query->where('plate_number', 'like', '%' . $request->plate_number . '%');
        }

        // Filter by print status
        if ($request->filled('print_status')) {
            if ($request->print_status === 'printed') {
                $query->where('is_printed', true);
            } elseif ($request->print_status === 'unprinted') {
                $query->where('is_printed', false);
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tickets = $query->paginate($perPage);

        $data = $tickets->getCollection()->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'plate_number' => $ticket->plate_number,
                'time_in' => $ticket->time_in ? $ticket->time_in->format('Y-m-d H:i:s') : null,
                'time_out' => $ticket->time_out ? $ticket->time_out->format('Y-m-d H:i:s') : null,
                'rate' => (float) $ticket->rate,
                'formatted_rate' => '₱' . number_format($ticket->rate, 2),
                'parking_slot' => $ticket->parking_slot,
                'notes' => $ticket->notes,
                'is_printed' => (bool) $ticket->is_printed,
                'barcode' => $ticket->barcode,
                'qr_data' => $ticket->qr_data,
                'template_slug' => $ticket->template_slug,
                'parking_session' => [
                    'id' => $ticket->parkingSession->id,
                    'plate_number' => $ticket->parkingSession->plate_number,
                    'customer_name' => $ticket->parkingSession->customer_name,
                    'customer_contact' => $ticket->parkingSession->customer_contact,
                    'start_time' => $ticket->parkingSession->start_time->format('Y-m-d H:i:s'),
                    'end_time' => $ticket->parkingSession->end_time ? $ticket->parkingSession->end_time->format('Y-m-d H:i:s') : null,
                    'status' => $ticket->parkingSession->status,
                    'amount_paid' => $ticket->parkingSession->amount_paid,
                    'formatted_amount_paid' => $ticket->parkingSession->formatted_amount,
                ],
                'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $ticket->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        $response = [
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
        ];

        activity('ticket_api')
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'index',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'filters' => $request->only(['plate_number','print_status','date_from','date_to']),
            ])
            ->log('Listed tickets via API');

        return response()->json($response);
    }

    /**
     * Get a specific ticket by ID.
     */
    public function show($id): JsonResponse
    {
        $ticket = Ticket::with(['parkingSession.creator', 'parkingSession.parkingRate'])
            ->whereHas('parkingSession', function ($q) {
                $q->where('created_by', auth()->id());
            })
            ->find($id);

        if (!$ticket) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket not found.',
            ], 404);
        }

        $ticketData = [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'plate_number' => $ticket->plate_number,
            'time_in' => $ticket->time_in ? $ticket->time_in->format('Y-m-d H:i:s') : null,
            'time_out' => $ticket->time_out ? $ticket->time_out->format('Y-m-d H:i:s') : null,
            'rate' => (float) $ticket->rate,
            'formatted_rate' => '₱' . number_format($ticket->rate, 2),
            'parking_slot' => $ticket->parking_slot,
            'notes' => $ticket->notes,
            'is_printed' => (bool) $ticket->is_printed,
            'barcode' => $ticket->barcode,
            'qr_data' => $ticket->qr_data,
            'template_slug' => $ticket->template_slug,
            'location' => [
                'latitude' => $ticket->latitude,
                'longitude' => $ticket->longitude,
                'accuracy' => $ticket->accuracy,
                'location_source' => $ticket->location_source,
                'address' => $ticket->address,
                'city' => $ticket->city,
                'state' => $ticket->state,
                'country' => $ticket->country,
                'postal_code' => $ticket->postal_code,
            ],
            'parking_session' => [
                'id' => $ticket->parkingSession->id,
                'plate_number' => $ticket->parkingSession->plate_number,
                'customer_name' => $ticket->parkingSession->customer_name,
                'customer_contact' => $ticket->parkingSession->customer_contact,
                'start_time' => $ticket->parkingSession->start_time->format('Y-m-d H:i:s'),
                'end_time' => $ticket->parkingSession->end_time ? $ticket->parkingSession->end_time->format('Y-m-d H:i:s') : null,
                'status' => $ticket->parkingSession->status,
                'amount_paid' => $ticket->parkingSession->amount_paid,
                'formatted_amount_paid' => $ticket->parkingSession->formatted_amount,
                'duration_minutes' => $ticket->parkingSession->duration_minutes,
                'formatted_duration' => $ticket->parkingSession->formatted_duration,
            ],
            'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $ticket->updated_at->format('Y-m-d H:i:s'),
        ];

        activity('ticket_api')
            ->performedOn($ticket)
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'show',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'ticket_id' => $ticket->id,
            ])
            ->log("Viewed ticket {$ticket->ticket_number} via API");

        return response()->json([
            'status' => 'success',
            'data' => $ticketData,
        ]);
    }

    /**
     * Generate a new ticket for a parking session.
     */
    public function generate(Request $request): JsonResponse
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

        try {
            $parkingSession = ParkingSession::with('parkingRate')
                ->where('created_by', auth()->id())
                ->findOrFail($request->parking_session_id);

            // Prevent ticket creation if plate_number is missing or empty
            if (!isset($parkingSession->plate_number) || trim($parkingSession->plate_number) === '') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot generate ticket: Parking session has no valid plate number.',
                ], 400);
            }

            // Check if ticket already exists for this session
            if ($parkingSession->ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket already exists for this parking session.',
                    'ticket_id' => $parkingSession->ticket->id,
                ], 409);
            }

            $ticketNumber = Ticket::generateTicketNumber($parkingSession->id, $parkingSession->plate_number);
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
                link: route('attendant.tickets.show', $ticket),
                initiatorId: auth()->id(),
                targetRole: 'admin',
            ));

            activity('ticket_api')
                ->performedOn($ticket)
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'generate',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'parking_session_id' => $parkingSession->id,
                ])
                ->log("Generated ticket {$ticket->ticket_number} via API");

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket generated successfully.',
                'data' => [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'plate_number' => $ticket->plate_number,
                    'time_in' => $ticket->time_in->format('Y-m-d H:i:s'),
                    'rate' => (float) $ticket->rate,
                    'formatted_rate' => '₱' . number_format($ticket->rate, 2),
                    'parking_slot' => $ticket->parking_slot,
                    'notes' => $ticket->notes,
                    'barcode' => $ticket->barcode,
                    'qr_data' => $ticket->qr_data,
                    'template_slug' => $ticket->template_slug,
                    'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                ],
            ], 201);
        } catch (\Exception $e) {
            activity('ticket_api')
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'generate_failed',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'error' => $e->getMessage(),
                ])
                ->log('Failed to generate ticket via API');

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate ticket.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark ticket as printed.
     */
    public function markPrinted(Ticket $ticket): JsonResponse
    {
        // Ensure the ticket belongs to the authenticated attendant
        if (!$ticket->parkingSession || $ticket->parkingSession->created_by !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden. You can only mark your own tickets as printed.',
            ], 403);
        }

        try {
            $ticket->markAsPrinted();

            activity('ticket_api')
                ->performedOn($ticket)
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'mark_printed',
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log("Marked ticket {$ticket->ticket_number} as printed via API");

            return response()->json([
                'status' => 'success',
                'message' => 'Ticket marked as printed successfully.',
                'data' => [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'is_printed' => true,
                    'updated_at' => $ticket->updated_at->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            activity('ticket_api')
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'mark_printed_failed',
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'error' => $e->getMessage(),
                ])
                ->log('Failed to mark ticket as printed via API');

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark ticket as printed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get ticket statistics for the authenticated attendant.
     */
    public function statistics(): JsonResponse
    {
        $userId = auth()->id();

        $stats = [
            'total_tickets' => Ticket::whereHas('parkingSession', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            })->count(),
            'printed_tickets' => Ticket::whereHas('parkingSession', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            })->where('is_printed', true)->count(),
            'unprinted_tickets' => Ticket::whereHas('parkingSession', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            })->where('is_printed', false)->count(),
            'today_tickets' => Ticket::whereHas('parkingSession', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            })->whereDate('created_at', today())->count(),
            'this_month_tickets' => Ticket::whereHas('parkingSession', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            })->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $response = [
            'status' => 'success',
            'data' => $stats,
        ];

        activity('ticket_api')
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'statistics',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Fetched ticket statistics via API');

        return response()->json($response);
    }
}
