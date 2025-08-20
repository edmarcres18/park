<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class DashboardApiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:attendant']);
    }

    /**
     * Get dashboard metrics and recent data for the authenticated attendant.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        // Active sessions for this attendant
        $activeSessions = ParkingSession::where('created_by', $user->id)
            ->active()
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function (ParkingSession $session) {
                return [
                    'id' => $session->id,
                    'plate_number' => $session->plate_number,
                    'start_time' => optional($session->start_time)->format('Y-m-d H:i:s'),
                    'current_duration_minutes' => $session->getCurrentDurationMinutes(),
                    'formatted_duration' => $session->formatted_duration,
                    'estimated_current_fee' => $session->getEstimatedCurrentFee(),
                    'formatted_estimated_fee' => '₱' . number_format($session->getEstimatedCurrentFee(), 2),
                    'printed' => (bool) $session->printed,
                    'status' => $session->status,
                    'created_at' => $session->created_at?->format('Y-m-d H:i:s'),
                ];
            });

        // Today's stats for this attendant
        $todaySessionsCount = ParkingSession::where('created_by', $user->id)
            ->whereDate('start_time', Carbon::today())
            ->count();

        $todayEarnings = (float) ParkingSession::where('created_by', $user->id)
            ->whereDate('end_time', Carbon::today())
            ->whereNotNull('end_time')
            ->sum('amount_paid');

        // This month's stats
        $monthlySessionsCount = ParkingSession::where('created_by', $user->id)
            ->whereMonth('start_time', Carbon::now()->month)
            ->whereYear('start_time', Carbon::now()->year)
            ->count();

        $monthlyEarnings = (float) ParkingSession::where('created_by', $user->id)
            ->whereMonth('end_time', Carbon::now()->month)
            ->whereYear('end_time', Carbon::now()->year)
            ->whereNotNull('end_time')
            ->sum('amount_paid');

        // Recent completed sessions
        $recentCompletedSessions = ParkingSession::where('created_by', $user->id)
            ->completed()
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function (ParkingSession $session) {
                return [
                    'id' => $session->id,
                    'plate_number' => $session->plate_number,
                    'start_time' => optional($session->start_time)->format('Y-m-d H:i:s'),
                    'end_time' => optional($session->end_time)->format('Y-m-d H:i:s'),
                    'duration_minutes' => $session->duration_minutes,
                    'formatted_duration' => $session->formatted_duration,
                    'amount_paid' => (float) $session->amount_paid,
                    'formatted_amount' => $session->formatted_amount,
                    'printed' => (bool) $session->printed,
                    'status' => $session->status,
                    'updated_at' => $session->updated_at?->format('Y-m-d H:i:s'),
                ];
            });

        $payload = [
            'status' => 'success',
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
            'data' => [
                'active_sessions' => $activeSessions->values(),
                'active_sessions_count' => $activeSessions->count(),
                'today' => [
                    'sessions_count' => (int) $todaySessionsCount,
                    'earnings' => (float) $todayEarnings,
                    'formatted_earnings' => '₱' . number_format($todayEarnings, 2),
                ],
                'monthly' => [
                    'sessions_count' => (int) $monthlySessionsCount,
                    'earnings' => (float) $monthlyEarnings,
                    'formatted_earnings' => '₱' . number_format($monthlyEarnings, 2),
                ],
                'recent_completed_sessions' => $recentCompletedSessions->values(),
            ],
        ];

        activity('dashboard_api')
            ->causedBy($user)
            ->withProperties([
                'action' => 'index',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'active_sessions' => $activeSessions->count(),
            ])
            ->log('Fetched dashboard data via API');

        return response()->json($payload);
    }
}


