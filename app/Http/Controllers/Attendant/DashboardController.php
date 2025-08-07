<?php

namespace App\Http\Controllers\Attendant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ParkingSession;
use App\Models\ParkingRate;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Get active sessions for the current attendant with eager loading
        $activeSessions = ParkingSession::active()
            ->where('created_by', $user->id)
            ->with(['plate', 'parkingRate'])
            ->orderBy('start_time', 'desc')
            ->get();

        // Get today's statistics for this attendant
        $todayStats = $this->getAttendantDailyStats($user->id, $today);

        // Get yesterday's statistics for comparison
        $yesterdayStats = $this->getAttendantDailyStats($user->id, $yesterday);

        // Get recent completed sessions
        $recentSessions = ParkingSession::completed()
            ->where('created_by', $user->id)
            ->with(['plate', 'parkingRate'])
            ->orderBy('end_time', 'desc')
            ->limit(10)
            ->get();

        // Get current parking rate
        $currentRate = ParkingRate::getActiveRate();

        // Get monthly statistics
        $monthlyStats = $this->getAttendantMonthlyStats($user->id);

        // Get session statistics
        $sessionStats = $this->getSessionStatistics($user->id);

        return view('attendant.dashboard', compact(
            'activeSessions',
            'todayStats',
            'yesterdayStats',
            'recentSessions',
            'currentRate',
            'monthlyStats',
            'sessionStats'
        ));
    }

    /**
     * Get daily statistics for a specific attendant
     */
    private function getAttendantDailyStats($userId, $date)
    {
        return [
            'total_sessions' => ParkingSession::where('created_by', $userId)
                ->whereDate('start_time', $date)
                ->count(),
            'total_earnings' => ParkingSession::where('created_by', $userId)
                ->whereDate('start_time', $date)
                ->sum('amount_paid'),
            'active_sessions' => ParkingSession::active()
                ->where('created_by', $userId)
                ->whereDate('start_time', $date)
                ->count(),
            'completed_sessions' => ParkingSession::completed()
                ->where('created_by', $userId)
                ->whereDate('start_time', $date)
                ->count(),
            'avg_duration' => ParkingSession::completed()
                ->where('created_by', $userId)
                ->whereDate('start_time', $date)
                ->avg('duration_minutes') ?? 0,
        ];
    }

    /**
     * Get monthly statistics for a specific attendant
     */
    private function getAttendantMonthlyStats($userId)
    {
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total_sessions' => ParkingSession::where('created_by', $userId)
                ->whereYear('start_time', $thisMonth->year)
                ->whereMonth('start_time', $thisMonth->month)
                ->count(),
            'total_earnings' => ParkingSession::where('created_by', $userId)
                ->whereYear('start_time', $thisMonth->year)
                ->whereMonth('start_time', $thisMonth->month)
                ->sum('amount_paid'),
            'avg_daily_earnings' => ParkingSession::where('created_by', $userId)
                ->whereYear('start_time', $thisMonth->year)
                ->whereMonth('start_time', $thisMonth->month)
                ->sum('amount_paid') / $thisMonth->daysInMonth,
        ];
    }

    /**
     * Get session statistics for a specific attendant
     */
    private function getSessionStatistics($userId)
    {
        return [
            'total_sessions_all_time' => ParkingSession::where('created_by', $userId)->count(),
            'total_earnings_all_time' => ParkingSession::where('created_by', $userId)->sum('amount_paid'),
            'avg_session_duration' => ParkingSession::completed()
                ->where('created_by', $userId)
                ->avg('duration_minutes') ?? 0,
            'best_day_earnings' => ParkingSession::where('created_by', $userId)
                ->selectRaw('DATE(start_time) as date, SUM(amount_paid) as daily_earnings')
                ->groupBy('date')
                ->orderByDesc('daily_earnings')
                ->first(),
        ];
    }
}
