<?php

namespace App\Http\Controllers;

use App\Models\ParkingSession;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|attendant']);
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return $this->adminDashboard();
        } else {
            return $this->attendantDashboard();
        }
    }

    private function adminDashboard()
    {
        // Total vehicles today
        $totalVehiclesToday = ParkingSession::whereDate('created_at', Carbon::today())
            ->distinct('vehicle_id')
            ->count();

        // Earnings today
        $earningsToday = ParkingSession::whereDate('created_at', Carbon::today())
            ->where('status', 'completed')
            ->sum('total_amount');

        // Active sessions
        $activeSessions = ParkingSession::where('status', 'active')->count();

        // Top 5 attendants by earnings (this month)
        $topAttendants = ParkingSession::select(
                'users.name as attendant_name',
                'users.id as attendant_id',
                DB::raw('COUNT(*) as session_count'),
                DB::raw('SUM(total_amount) as total_earnings')
            )
            ->join('users', 'parking_sessions.user_id', '=', 'users.id')
            ->whereMonth('parking_sessions.created_at', Carbon::now()->month)
            ->whereYear('parking_sessions.created_at', Carbon::now()->year)
            ->where('parking_sessions.status', 'completed')
            ->where('users.role', 'attendant')
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_earnings', 'desc')
            ->limit(5)
            ->get();

        // Recent sessions for activity feed
        $recentSessions = ParkingSession::with(['user', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Monthly earnings trend (last 6 months)
        $monthlyEarnings = ParkingSession::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total_earnings')
            )
            ->where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::createFromDate($item->year, $item->month, 1)->format('M Y'),
                    'earnings' => $item->total_earnings
                ];
            });

        return view('dashboard.admin', compact(
            'totalVehiclesToday',
            'earningsToday',
            'activeSessions',
            'topAttendants',
            'recentSessions',
            'monthlyEarnings'
        ));
    }

    private function attendantDashboard()
    {
        $user = Auth::user();

        // Active sessions for this attendant
        $activeSessions = ParkingSession::with('vehicle')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        // Today's stats for this attendant
        $todaySessionsCount = ParkingSession::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $todayEarnings = ParkingSession::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->where('status', 'completed')
            ->sum('total_amount');

        // This month's stats
        $monthlySessionsCount = ParkingSession::where('user_id', $user->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $monthlyEarnings = ParkingSession::where('user_id', $user->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Recent completed sessions
        $recentCompletedSessions = ParkingSession::with('vehicle')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.attendant', compact(
            'activeSessions',
            'todaySessionsCount',
            'todayEarnings',
            'monthlySessionsCount',
            'monthlyEarnings',
            'recentCompletedSessions'
        ));
    }
}
