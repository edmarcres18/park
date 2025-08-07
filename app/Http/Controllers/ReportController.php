<?php

namespace App\Http\Controllers;

use App\Models\ParkingSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());
        $attendantId = $request->get('attendant_id');

        // Validate dates
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'attendant_id' => 'nullable|exists:users,id'
        ]);

        // Build base query
        $sessionsQuery = ParkingSession::with(['user', 'vehicle'])
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('status', 'completed');

        if ($attendantId) {
            $sessionsQuery->where('user_id', $attendantId);
        }

        // Get summary data
        $totalSessions = $sessionsQuery->count();
        $totalEarnings = $sessionsQuery->sum('total_amount');

        // Get daily breakdown
        $dailySales = ParkingSession::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as session_count'),
                DB::raw('SUM(total_amount) as total_earnings')
            )
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('status', 'completed')
            ->when($attendantId, function($query) use ($attendantId) {
                return $query->where('user_id', $attendantId);
            })
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get();

        // Get earnings by attendant
        $attendantEarnings = ParkingSession::select(
                'users.name as attendant_name',
                DB::raw('COUNT(*) as session_count'),
                DB::raw('SUM(total_amount) as total_earnings')
            )
            ->join('users', 'parking_sessions.user_id', '=', 'users.id')
            ->whereBetween('parking_sessions.created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('parking_sessions.status', 'completed')
            ->when($attendantId, function($query) use ($attendantId) {
                return $query->where('users.id', $attendantId);
            })
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_earnings', 'desc')
            ->get();

        // Get all attendants for filter dropdown
        $attendants = User::where('role', 'attendant')->get();

        return view('reports.sales', compact(
            'totalSessions',
            'totalEarnings',
            'dailySales',
            'attendantEarnings',
            'attendants',
            'startDate',
            'endDate',
            'attendantId'
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->toDateString());
        $endDate = $request->get('end_date', Carbon::today()->toDateString());
        $attendantId = $request->get('attendant_id');

        // Validate dates
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'attendant_id' => 'nullable|exists:users,id'
        ]);

        // Get detailed sessions data
        $sessions = ParkingSession::with(['user', 'vehicle'])
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->where('status', 'completed')
            ->when($attendantId, function($query) use ($attendantId) {
                return $query->where('user_id', $attendantId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Generate CSV content
        $csvContent = $this->generateCsvContent($sessions, $startDate, $endDate);
        
        $filename = 'sales_report_' . $startDate . '_to_' . $endDate . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function generateCsvContent($sessions, $startDate, $endDate)
    {
        $csv = [];
        
        // Add header
        $csv[] = "Sales Report - {$startDate} to {$endDate}";
        $csv[] = "Generated on: " . Carbon::now()->format('Y-m-d H:i:s');
        $csv[] = ""; // Empty line
        
        // Add column headers
        $csv[] = "Date,Time,Attendant,Vehicle Plate,Vehicle Type,Duration (Hours),Amount";
        
        // Add data rows
        foreach ($sessions as $session) {
            $csv[] = implode(',', [
                $session->created_at->format('Y-m-d'),
                $session->created_at->format('H:i:s'),
                '"' . ($session->user->name ?? 'N/A') . '"',
                '"' . ($session->vehicle->plate_number ?? 'N/A') . '"',
                '"' . ($session->vehicle->type ?? 'N/A') . '"',
                number_format($session->duration_hours, 2),
                number_format($session->total_amount, 2)
            ]);
        }
        
        // Add summary
        $csv[] = ""; // Empty line
        $csv[] = "Summary";
        $csv[] = "Total Sessions," . $sessions->count();
        $csv[] = "Total Earnings," . number_format($sessions->sum('total_amount'), 2);
        
        return implode(PHP_EOL, $csv);
    }
}
