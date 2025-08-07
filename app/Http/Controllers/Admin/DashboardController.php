<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ParkingSession;
use App\Models\User;
use App\Models\ParkingRate;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $notifications = Auth::user()->unreadNotifications;

        // Get today's statistics with eager loading
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Today's statistics
        $todayStats = $this->getDailyStats($today);

        // Yesterday's statistics for comparison
        $yesterdayStats = $this->getDailyStats($yesterday);

        // Monthly statistics
        $monthlyStats = $this->getMonthlyStats($thisMonth);

        // Active sessions with eager loading
        $activeSessions = ParkingSession::active()
            ->with(['creator', 'plate', 'parkingRate'])
            ->orderBy('start_time', 'desc')
            ->limit(10)
            ->get();

        // Top attendants by earnings today
        $topAttendants = $this->getTopAttendants($today);

        // Recent completed sessions
        $recentSessions = ParkingSession::completed()
            ->with(['creator', 'plate', 'parkingRate'])
            ->orderBy('end_time', 'desc')
            ->limit(5)
            ->get();

        // Parking rate statistics
        $rateStats = $this->getRateStatistics();

        // User statistics
        $userStats = $this->getUserStatistics();

        // Chart data for the last 7 days
        $weeklyChartData = $this->getWeeklyChartData();

        return view('admin.dashboard', compact(
            'notifications',
            'todayStats',
            'yesterdayStats',
            'monthlyStats',
            'activeSessions',
            'topAttendants',
            'recentSessions',
            'rateStats',
            'userStats',
            'weeklyChartData'
        ));
    }

    /**
     * Get daily statistics
     */
    private function getDailyStats($date)
    {
        return [
            'total_vehicles' => ParkingSession::whereDate('start_time', $date)->count(),
            'total_earnings' => ParkingSession::whereDate('start_time', $date)->sum('amount_paid'),
            'active_sessions' => ParkingSession::active()->whereDate('start_time', $date)->count(),
            'completed_sessions' => ParkingSession::completed()->whereDate('start_time', $date)->count(),
            'avg_duration' => ParkingSession::completed()
                ->whereDate('start_time', $date)
                ->avg('duration_minutes') ?? 0,
        ];
    }

    /**
     * Get monthly statistics
     */
    private function getMonthlyStats($month)
    {
        return [
            'total_vehicles' => ParkingSession::whereYear('start_time', $month->year)
                ->whereMonth('start_time', $month->month)
                ->count(),
            'total_earnings' => ParkingSession::whereYear('start_time', $month->year)
                ->whereMonth('start_time', $month->month)
                ->sum('amount_paid'),
            'avg_daily_earnings' => ParkingSession::whereYear('start_time', $month->year)
                ->whereMonth('start_time', $month->month)
                ->sum('amount_paid') / $month->daysInMonth,
        ];
    }

    /**
     * Get top attendants by earnings
     */
    private function getTopAttendants($date)
    {
        return ParkingSession::whereDate('start_time', $date)
            ->join('users', 'parking_sessions.created_by', '=', 'users.id')
            ->selectRaw('users.name as attendant_name,
                        users.id as attendant_id,
                        SUM(amount_paid) as total_earnings,
                        COUNT(*) as session_count,
                        AVG(duration_minutes) as avg_duration')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_earnings')
            ->limit(5)
            ->get();
    }

    /**
     * Get parking rate statistics
     */
    private function getRateStatistics()
    {
        $activeRate = ParkingRate::getActiveRate();

        return [
            'active_rate' => $activeRate,
            'total_rates' => ParkingRate::count(),
            'active_sessions_with_rate' => ParkingSession::active()
                ->whereNotNull('parking_rate_id')
                ->count(),
        ];
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics()
    {
        return [
            'total_attendants' => User::getActiveAttendantsCount(),
            'pending_attendants' => User::getPendingAttendantsCount(),
            'rejected_attendants' => User::getRejectedAttendantsCount(),
            'online_attendants' => User::whereHas('roles', function ($query) {
                $query->where('name', 'attendant');
            })->where('status', 'active')->get()->filter(function ($user) {
                return $user->isOnline();
            })->count(),
        ];
    }

    /**
     * Get weekly chart data for the last 7 days
     */
    private function getWeeklyChartData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayStats = $this->getDailyStats($date);

            $data[] = [
                'date' => $date->format('M d'),
                'earnings' => $dayStats['total_earnings'],
                'vehicles' => $dayStats['total_vehicles'],
                'sessions' => $dayStats['completed_sessions'],
            ];
        }

        return $data;
    }
}
