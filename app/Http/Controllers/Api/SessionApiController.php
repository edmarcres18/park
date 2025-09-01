<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StartSessionRequest;
use App\Http\Requests\EndSessionRequest;
use App\Models\ParkingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ParkingRate;
use Spatie\Activitylog\Models\Activity;

class SessionApiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:attendant']);
    }

    /**
     * Get all active parking sessions.
     */
    public function active(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = ParkingSession::with('creator')
            ->active()
            ->orderBy('start_time', 'desc');

        // Filter by branch for attendant users
        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } else {
            // Fallback to user-created sessions if no branch assigned
            $query->where('created_by', $user->id);
        }

        $sessions = $query
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'plate_number' => $session->plate_number,
                    'customer_name' => $session->customer_name,
                    'customer_contact' => $session->customer_contact,
                    'start_time' => $session->start_time->format('Y-m-d H:i:s'),
                    'current_duration_minutes' => $session->getCurrentDurationMinutes(),
                    'formatted_duration' => $session->formatted_duration,
                    'estimated_current_fee' => $session->getEstimatedCurrentFee(),
                    'formatted_estimated_fee' => '₱' . number_format($session->getEstimatedCurrentFee(), 2),
                    'printed' => $session->printed,
                    'status' => $session->status,
                    'created_by' => [
                        'id' => $session->creator->id,
                        'name' => $session->creator->name,
                    ],
                    'created_at' => $session->created_at->format('Y-m-d H:i:s'),
                ];
            });

        $response = [
            'status' => 'success',
            'data' => $sessions,
            'total' => $sessions->count(),
        ];

        activity('session_api')
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'active_list',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'total' => $sessions->count(),
            ])
            ->log('Listed active sessions via API');

        return response()->json($response);
    }

    /**
     * Get parking sessions history (completed sessions).
     */
    public function history(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = ParkingSession::with('creator')
            ->orderBy('created_at', 'desc');

        // Filter by branch for attendant users
        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } else {
            // Fallback to user-created sessions if no branch assigned
            $query->where('created_by', $user->id);
        }

        // Optional pagination
        $perPage = $request->get('per_page', 15);
        $sessions = $query->paginate($perPage);

        $data = $sessions->getCollection()->map(function ($session) {
            return [
                'id' => $session->id,
                'plate_number' => $session->plate_number,
                'customer_name' => $session->customer_name,
                'customer_contact' => $session->customer_contact,
                'start_time' => $session->start_time->format('Y-m-d H:i:s'),
                'end_time' => $session->end_time ? $session->end_time->format('Y-m-d H:i:s') : null,
                'duration_minutes' => $session->duration_minutes,
                'formatted_duration' => $session->formatted_duration,
                'amount_paid' => $session->amount_paid,
                'formatted_amount' => $session->formatted_amount,
                'printed' => $session->printed,
                'status' => $session->status,
                'created_by' => [
                    'id' => $session->creator->id,
                    'name' => $session->creator->name,
                ],
                'created_at' => $session->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $session->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        $response = [
            'status' => 'success',
            'data' => $data,
            'pagination' => [
                'current_page' => $sessions->currentPage(),
                'per_page' => $sessions->perPage(),
                'total' => $sessions->total(),
                'last_page' => $sessions->lastPage(),
                'from' => $sessions->firstItem(),
                'to' => $sessions->lastItem(),
            ],
        ];

        activity('session_api')
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'history_list',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'page' => $sessions->currentPage(),
                'per_page' => $sessions->perPage(),
            ])
            ->log('Listed session history via API');

        return response()->json($response);
    }

    /**
     * Start a new parking session.
     */
    public function start(StartSessionRequest $request): JsonResponse
    {
        try {
            $session = ParkingSession::create($request->validated());
            $session->load('creator');

            event(new \App\Events\ParkingEvent(
                action: 'session_started',
                title: 'New Parking Session',
                message: "Plate #{$session->plate_number} session started.",
                type: 'success',
                link: route('attendant.sessions.index'),
                initiatorId: auth()->id(),
                targetRole: 'admin',
            ));

            activity('session_api')
                ->performedOn($session)
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'start',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'payload' => $request->safe()->all(),
                ])
                ->log("Started session for plate {$session->plate_number} via API");

            return response()->json([
                'status' => 'success',
                'message' => 'Parking session started successfully!',
                'data' => [
                    'id' => $session->id,
                    'plate_number' => $session->plate_number,
                    'customer_name' => $session->customer_name,
                    'customer_contact' => $session->customer_contact,
                    'start_time' => $session->start_time->format('Y-m-d H:i:s'),
                    'status' => $session->status,
                    'created_by' => [
                        'id' => $session->creator->id,
                        'name' => $session->creator->name,
                    ],
                    'created_at' => $session->created_at->format('Y-m-d H:i:s'),
                ],
            ], 201);
        } catch (\Exception $e) {
            activity('session_api')
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'start_failed',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'error' => $e->getMessage(),
                ])
                ->log('Failed to start parking session via API');

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to start parking session.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * End a parking session.
     */
    public function end(EndSessionRequest $request, ParkingSession $session): JsonResponse
    {
        try {
            // Check if session is active
            if (!$session->isActive()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This parking session has already been ended.',
                ], 400);
            }

            // End the session with calculated fees
            $session->endSession($request->end_time);

            // Update printed status if provided
            if ($request->has('printed')) {
                $session->update(['printed' => $request->printed]);
            }

            $session->load('creator');

            event(new \App\Events\ParkingEvent(
                action: 'session_ended',
                title: 'Parking Session Ended',
                message: "Plate #{$session->plate_number} session ended.",
                type: 'info',
                link: route('attendant.sessions.index'),
                initiatorId: auth()->id(),
                targetRole: 'admin',
            ));

            activity('session_api')
                ->performedOn($session)
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'end',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log("Ended session for plate {$session->plate_number} via API");

            return response()->json([
                'status' => 'success',
                'message' => 'Parking session ended successfully!',
                'data' => [
                    'id' => $session->id,
                    'plate_number' => $session->plate_number,
                    'customer_name' => $session->customer_name,
                    'customer_contact' => $session->customer_contact,
                    'start_time' => $session->start_time->format('Y-m-d H:i:s'),
                    'end_time' => $session->end_time->format('Y-m-d H:i:s'),
                    'duration_minutes' => $session->duration_minutes,
                    'formatted_duration' => $session->formatted_duration,
                    'amount_paid' => $session->amount_paid,
                    'formatted_amount' => $session->formatted_amount,
                    'printed' => $session->printed,
                    'status' => $session->status,
                    'created_by' => [
                        'id' => $session->creator->id,
                        'name' => $session->creator->name,
                    ],
                    'updated_at' => $session->updated_at->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            activity('session_api')
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'end_failed',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'error' => $e->getMessage(),
                ])
                ->log('Failed to end parking session via API');

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to end parking session.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List available parking rates (active first).
     */
    public function rates(): JsonResponse
    {
        $rates = ParkingRate::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(function (ParkingRate $rate) {
                return [
                    'id' => $rate->id,
                    'name' => $rate->name ?: 'Rate #' . $rate->id,
                    'rate_type' => $rate->rate_type,
                    'rate_amount' => (float) $rate->rate_amount,
                    'formatted_rate_amount' => $rate->formatted_rate_amount,
                    'grace_period' => (int) ($rate->grace_period ?? 0),
                    'formatted_grace_period' => $rate->formatted_grace_period,
                    'is_active' => (bool) $rate->is_active,
                ];
            });

        $response = [
            'status' => 'success',
            'data' => $rates,
        ];

        activity('session_api')
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'rates',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'count' => $rates->count(),
            ])
            ->log('Fetched parking rates via API');

        return response()->json($response);
    }

    /**
     * Get session print data for starting sessions.
     */
    public function getSessionPrintData(ParkingSession $session): JsonResponse
    {
        try {
            // Get app settings
            $appName = config('app.name', 'Parking System');
            $locationName = config('parking.location_name', 'Parking Location');

            // Get the parking rate
            $rate = $session->parkingRate;

            // Format the start time
            $startTime = $session->start_time->format('M d, Y g:i A');

            // Generate session number (similar to ticket number)
            $sessionNumber = config('parking.session_number_prefix', 'SES') . str_pad($session->id, 6, '0', STR_PAD_LEFT);

            $printData = [
                'session' => [
                    'id' => $session->id,
                    'number' => $sessionNumber,
                    'plate_number' => $session->plate_number,
                    'customer_name' => $session->customer_name,
                    'customer_contact' => $session->customer_contact,
                    'start_time' => $startTime,
                    'rate_name' => $rate ? $rate->name : 'Standard Rate',
                    'rate_amount' => $rate ? (float) $rate->rate_amount : 0.0,
                    'formatted_rate_amount' => $rate ? $rate->formatted_rate_amount : '₱0.00',
                    'rate_type' => $rate ? $rate->rate_type : 'hourly',
                    'grace_period' => $rate ? (int) ($rate->grace_period ?? 0) : 0,
                    'formatted_grace_period' => $rate ? $rate->formatted_grace_period : '0 minutes',
                    'qr_data' => [
                        'session_id' => $session->id,
                        'plate_number' => $session->plate_number,
                        'start_time' => $session->start_time->toISOString(),
                    ],
                ],
                'app_name' => $appName,
                'location_name' => $locationName,
                'print_time' => now()->format('M d, Y g:i A'),
                'receipt_type' => 'session_start',
            ];

            activity('session_api')
                ->performedOn($session)
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'get_print_data',
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log("Fetched print data for session {$session->id} via API");

            return response()->json([
                'status' => 'success',
                'data' => $printData,
            ]);
        } catch (\Exception $e) {
            activity('session_api')
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'get_print_data_failed',
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'error' => $e->getMessage(),
                ])
                ->log('Failed to get session print data via API');

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get session print data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
