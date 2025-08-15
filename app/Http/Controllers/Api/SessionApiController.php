<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StartSessionRequest;
use App\Http\Requests\EndSessionRequest;
use App\Models\ParkingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ParkingRate;

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
        $sessions = ParkingSession::with('creator')
            ->active()
            ->where('created_by', auth()->id())
            ->orderBy('start_time', 'desc')
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

        return response()->json([
            'status' => 'success',
            'data' => $sessions,
            'total' => $sessions->count(),
        ]);
    }

    /**
     * Get parking sessions history (completed sessions).
     */
    public function history(Request $request): JsonResponse
    {
        $query = ParkingSession::with('creator')
            ->where('created_by', auth()->id())
            ->orderBy('created_at', 'desc');

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

        return response()->json([
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
        ]);
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

        return response()->json([
            'status' => 'success',
            'data' => $rates,
        ]);
    }
}
