<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\ParkingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Exception;

class TicketApiController extends Controller
{
    /**
     * Create a new controller instance.
     * Apply authentication and role middleware.
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:attendant']);
    }

    /**
     * Display a listing of auto-generated tickets.
     *
     * This endpoint returns all tickets that were automatically generated
     * by the ParkingSessionObserver when parking sessions are created.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'plate_number' => 'nullable|string|max:20',
                'print_status' => 'nullable|string|in:printed,unprinted',
                'date_from' => 'nullable|date|before_or_equal:today',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'per_page' => 'nullable|integer|min:1|max:100',
                'sort_by' => 'nullable|string|in:created_at,time_in,plate_number,ticket_number',
                'sort_direction' => 'nullable|string|in:asc,desc',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Get authenticated user
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized access',
                ], 401);
            }

            // Start building the query for auto-generated tickets
            // These are tickets created by ParkingSessionObserver when sessions are created
            $query = Ticket::query()
                ->with([
                    'parkingSession.creator:id,name,email',
                    'parkingSession.parkingRate:id,name,rate_amount,description',
                    'plate:id,number,owner_name,vehicle_type'
                ])
                ->whereHas('parkingSession', function ($subQuery) use ($user) {
                    // Filter tickets based on user's created sessions
                    $subQuery->where('created_by', $user->id);
                });

            // Apply filters
            $this->applyFilters($query, $request);

            // Apply sorting
            $this->applySorting($query, $request);

            // Get pagination parameter
            $perPage = $request->get('per_page', 15);
            $perPage = min(max($perPage, 1), 100); // Ensure per_page is between 1 and 100

            // Execute query with pagination
            $tickets = $query->paginate($perPage);

            // Transform tickets for API response
            $transformedTickets = $tickets->getCollection()->map(function ($ticket) {
                return $this->transformTicket($ticket);
            });

            // Build pagination metadata
            $paginationData = [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'from' => $tickets->firstItem(),
                'to' => $tickets->lastItem(),
                'has_more_pages' => $tickets->hasMorePages(),
            ];

            // Get summary statistics for the authenticated user
            $summaryStats = $this->getUserTicketSummary($user->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Tickets retrieved successfully',
                'data' => [
                    'tickets' => $transformedTickets,
                    'pagination' => $paginationData,
                    'summary' => $summaryStats,
                ],
                'meta' => [
                    'requested_at' => now()->toISOString(),
                    'user_id' => $user->id,
                    'user_role' => $user->roles->pluck('name')->first(),
                    'filters_applied' => $this->getAppliedFilters($request),
                ],
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Ticket API validation error', [
                'user_id' => Auth::id(),
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            Log::error('Ticket API error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while retrieving tickets',
                'error_code' => 'TICKET_RETRIEVAL_ERROR',
            ], 500);
        }
    }

    /**
     * Apply filters to the ticket query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    private function applyFilters($query, Request $request): void
    {
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

        // Additional filters for better search
        if ($request->filled('ticket_number')) {
            $query->where('ticket_number', 'LIKE', '%' . $request->ticket_number . '%');
        }

        // Filter by parking slot if provided
        if ($request->filled('parking_slot')) {
            $query->where('parking_slot', 'LIKE', '%' . $request->parking_slot . '%');
        }
    }

    /**
     * Apply sorting to the ticket query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validate sort direction
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

        // Apply sorting based on field
        switch ($sortBy) {
            case 'time_in':
                $query->orderBy('time_in', $sortDirection);
                break;
            case 'plate_number':
                $query->orderBy('plate_number', $sortDirection);
                break;
            case 'ticket_number':
                $query->orderBy('ticket_number', $sortDirection);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }

        // Always add a secondary sort for consistency
        if ($sortBy !== 'id') {
            $query->orderBy('id', 'desc');
        }
    }

    /**
     * Transform ticket data for API response.
     *
     * @param Ticket $ticket
     * @return array
     */
    private function transformTicket(Ticket $ticket): array
    {
        $parkingSession = $ticket->parkingSession;
        $parkingRate = $parkingSession?->parkingRate;
        $creator = $parkingSession?->creator;
        $plate = $ticket->plate;

        return [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'plate_number' => $ticket->plate_number,
            'time_in' => $ticket->time_in?->toISOString(),
            'time_out' => $ticket->time_out?->toISOString(),
            'rate' => (float) $ticket->rate,
            'formatted_rate' => $ticket->formatted_rate,
            'parking_slot' => $ticket->parking_slot,
            'is_printed' => (bool) $ticket->is_printed,
            'print_status' => $ticket->is_printed ? 'printed' : 'unprinted',
            'notes' => $ticket->notes,
            'qr_data' => $ticket->qr_data ?? [],
            'barcode' => $ticket->barcode,
            'duration' => $ticket->duration,
            'total_fee' => (float) $ticket->total_fee,
            'fee_breakdown' => $ticket->fee_breakdown,
            'location' => [
                'latitude' => $ticket->latitude,
                'longitude' => $ticket->longitude,
                'accuracy' => $ticket->accuracy,
                'source' => $ticket->location_source,
                'address' => $ticket->address,
                'city' => $ticket->city,
                'state' => $ticket->state,
                'country' => $ticket->country,
                'postal_code' => $ticket->postal_code,
                'formatted_location' => $ticket->formatted_location,
                'has_location' => $ticket->hasLocation(),
            ],
            'parking_session' => $parkingSession ? [
                'id' => $parkingSession->id,
                'status' => $parkingSession->status,
                'start_time' => $parkingSession->start_time?->toISOString(),
                'end_time' => $parkingSession->end_time?->toISOString(),
                'duration_minutes' => $parkingSession->duration_minutes,
                'formatted_duration' => $parkingSession->formatted_duration,
                'amount_paid' => (float) ($parkingSession->amount_paid ?? 0),
                'formatted_amount' => $parkingSession->formatted_amount,
                'is_active' => $parkingSession->isActive(),
                'printed' => (bool) $parkingSession->printed,
            ] : null,
            'parking_rate' => $parkingRate ? [
                'id' => $parkingRate->id,
                'name' => $parkingRate->name,
                'rate_amount' => (float) $parkingRate->rate_amount,
                'description' => $parkingRate->description,
            ] : null,
            'creator' => $creator ? [
                'id' => $creator->id,
                'name' => $creator->name,
                'email' => $creator->email,
            ] : null,
            'plate_info' => $plate ? [
                'id' => $plate->id,
                'number' => $plate->number,
                'owner_name' => $plate->owner_name,
                'vehicle_type' => $plate->vehicle_type,
            ] : null,
            'template' => [
                'slug' => $ticket->template_slug,
                'template_data' => $ticket->template_data,
            ],
            'created_at' => $ticket->created_at?->toISOString(),
            'updated_at' => $ticket->updated_at?->toISOString(),
            'time_ago' => $ticket->created_at?->diffForHumans(),
        ];
    }

    /**
     * Get summary statistics for the authenticated user's tickets.
     *
     * @param int $userId
     * @return array
     */
    private function getUserTicketSummary(int $userId): array
    {
        try {
            $today = Carbon::today();
            $thisMonth = Carbon::now()->startOfMonth();
            $thisYear = Carbon::now()->startOfYear();

            // Get tickets for this user (through parking sessions)
            $userTicketsQuery = Ticket::whereHas('parkingSession', function ($query) use ($userId) {
                $query->where('created_by', $userId);
            });

            $totalTickets = $userTicketsQuery->count();
            $printedTickets = $userTicketsQuery->clone()->printed()->count();
            $unprintedTickets = $userTicketsQuery->clone()->unprinted()->count();
            $todayTickets = $userTicketsQuery->clone()->whereDate('created_at', $today)->count();
            $thisMonthTickets = $userTicketsQuery->clone()->whereDate('created_at', '>=', $thisMonth)->count();
            $thisYearTickets = $userTicketsQuery->clone()->whereDate('created_at', '>=', $thisYear)->count();

            // Get recent activity (last 7 days)
            $recentActivity = $userTicketsQuery->clone()
                ->whereDate('created_at', '>=', Carbon::now()->subDays(7))
                ->count();

            return [
                'total_tickets' => $totalTickets,
                'printed_tickets' => $printedTickets,
                'unprinted_tickets' => $unprintedTickets,
                'today_tickets' => $todayTickets,
                'this_month_tickets' => $thisMonthTickets,
                'this_year_tickets' => $thisYearTickets,
                'recent_activity_7_days' => $recentActivity,
                'print_rate_percentage' => $totalTickets > 0 ? round(($printedTickets / $totalTickets) * 100, 2) : 0,
            ];

        } catch (Exception $e) {
            Log::error('Error getting user ticket summary', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'total_tickets' => 0,
                'printed_tickets' => 0,
                'unprinted_tickets' => 0,
                'today_tickets' => 0,
                'this_month_tickets' => 0,
                'this_year_tickets' => 0,
                'recent_activity_7_days' => 0,
                'print_rate_percentage' => 0,
            ];
        }
    }

    /**
     * Get information about applied filters for meta data.
     *
     * @param Request $request
     * @return array
     */
    private function getAppliedFilters(Request $request): array
    {
        $filters = [];

        if ($request->filled('plate_number')) {
            $filters['plate_number'] = $request->plate_number;
        }

        if ($request->filled('print_status')) {
            $filters['print_status'] = $request->print_status;
        }

        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->date_from;
        }

        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->date_to;
        }

        if ($request->filled('ticket_number')) {
            $filters['ticket_number'] = $request->ticket_number;
        }

        if ($request->filled('parking_slot')) {
            $filters['parking_slot'] = $request->parking_slot;
        }

        if ($request->filled('per_page')) {
            $filters['per_page'] = $request->per_page;
        }

        if ($request->filled('sort_by')) {
            $filters['sort_by'] = $request->sort_by;
        }

        if ($request->filled('sort_direction')) {
            $filters['sort_direction'] = $request->sort_direction;
        }

        return $filters;
    }
}
