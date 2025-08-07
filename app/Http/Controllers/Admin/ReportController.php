<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParkingSession;
use App\Models\User;
use App\Models\ParkingRate;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        // Get date range from request or default to current month
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        // Get daily sales data
        $dailySales = $this->getDailySalesData($from, $to);

        // Get monthly sales data
        $monthlySales = $this->getMonthlySalesData($from, $to);

        // Get sales by attendant
        $salesByAttendant = $this->getSalesByAttendant($from, $to);

        // Get sales by parking rate
        $salesByRate = $this->getSalesByRate($from, $to);

        // Get summary statistics
        $summaryStats = $this->getSummaryStats($from, $to);

        // Get chart data
        $chartData = $this->getChartData($from, $to);

        // Get top performing attendants
        $topAttendants = $this->getTopAttendants($from, $to);

        // Get recent sessions
        $recentSessions = ParkingSession::with(['creator', 'plate', 'parkingRate'])
            ->whereBetween('start_time', [$from, $to])
            ->orderBy('start_time', 'desc')
            ->limit(20)
            ->get();

        return view('admin.reports.sales', compact(
            'dailySales',
            'monthlySales',
            'salesByAttendant',
            'salesByRate',
            'summaryStats',
            'chartData',
            'topAttendants',
            'recentSessions',
            'from',
            'to'
        ));
    }

    /**
     * Export sales data as CSV
     */
    public function exportCsv(Request $request)
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $sessions = ParkingSession::with(['creator', 'plate', 'parkingRate'])
            ->whereBetween('start_time', [$from, $to])
            ->orderBy('start_time', 'desc')
            ->get();

        $filename = 'parking_sales_' . $from->format('Y-m-d') . '_to_' . $to->format('Y-m-d') . '.csv';

        $response = new StreamedResponse(function() use ($sessions) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'Session ID',
                'Plate Number',
                'Start Time',
                'End Time',
                'Duration (minutes)',
                'Amount Paid',
                'Attendant',
                'Parking Rate',
                'Status',
                'Created At'
            ]);

            foreach ($sessions as $session) {
                fputcsv($handle, [
                    $session->id,
                    $session->plate_number,
                    $session->start_time ? $session->start_time->format('Y-m-d H:i:s') : '',
                    $session->end_time ? $session->end_time->format('Y-m-d H:i:s') : '',
                    $session->duration_minutes ?? 0,
                    $session->amount_paid ?? 0,
                    optional($session->creator)->name ?? 'Unknown',
                    optional($session->parkingRate)->name ?? 'No Rate',
                    $session->isActive() ? 'Active' : 'Completed',
                    $session->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * Export detailed report as PDF
     */
    public function exportPdf(Request $request)
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $summaryStats = $this->getSummaryStats($from, $to);
        $salesByAttendant = $this->getSalesByAttendant($from, $to);
        $recentSessions = ParkingSession::with(['creator', 'plate', 'parkingRate'])
            ->whereBetween('start_time', [$from, $to])
            ->orderBy('start_time', 'desc')
            ->limit(50)
            ->get();

        // For now, return a view that can be converted to PDF
        // You can integrate with packages like DomPDF or Snappy
        return view('admin.reports.pdf.sales', compact(
            'summaryStats',
            'salesByAttendant',
            'recentSessions',
            'from',
            'to'
        ));
    }

    /**
     * Get daily sales data
     */
    private function getDailySalesData($from, $to)
    {
        return ParkingSession::whereBetween('start_time', [$from, $to])
            ->selectRaw('
                DATE(start_time) as date,
                COUNT(*) as session_count,
                SUM(amount_paid) as total_earnings,
                AVG(duration_minutes) as avg_duration,
                COUNT(CASE WHEN end_time IS NULL THEN 1 END) as active_sessions,
                COUNT(CASE WHEN end_time IS NOT NULL THEN 1 END) as completed_sessions
            ')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get monthly sales data
     */
    private function getMonthlySalesData($from, $to)
    {
        return ParkingSession::whereBetween('start_time', [$from, $to])
            ->selectRaw('
                YEAR(start_time) as year,
                MONTH(start_time) as month,
                COUNT(*) as session_count,
                SUM(amount_paid) as total_earnings,
                AVG(duration_minutes) as avg_duration
            ')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    /**
     * Get sales by attendant
     */
    private function getSalesByAttendant($from, $to)
    {
        return ParkingSession::whereBetween('start_time', [$from, $to])
            ->join('users', 'parking_sessions.created_by', '=', 'users.id')
            ->selectRaw('
                users.id as attendant_id,
                users.name as attendant_name,
                COUNT(*) as session_count,
                SUM(amount_paid) as total_earnings,
                AVG(duration_minutes) as avg_duration,
                COUNT(CASE WHEN end_time IS NULL THEN 1 END) as active_sessions,
                COUNT(CASE WHEN end_time IS NOT NULL THEN 1 END) as completed_sessions
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_earnings')
            ->get();
    }

    /**
     * Get sales by parking rate
     */
    private function getSalesByRate($from, $to)
    {
        return ParkingSession::whereBetween('start_time', [$from, $to])
            ->join('parking_rates', 'parking_sessions.parking_rate_id', '=', 'parking_rates.id')
            ->selectRaw('
                parking_rates.id as rate_id,
                parking_rates.name as rate_name,
                parking_rates.rate_type,
                parking_rates.rate_amount,
                COUNT(*) as session_count,
                SUM(amount_paid) as total_earnings,
                AVG(duration_minutes) as avg_duration
            ')
            ->groupBy('parking_rates.id', 'parking_rates.name', 'parking_rates.rate_type', 'parking_rates.rate_amount')
            ->orderByDesc('total_earnings')
            ->get();
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStats($from, $to)
    {
        $stats = ParkingSession::whereBetween('start_time', [$from, $to])
            ->selectRaw('
                COUNT(*) as total_sessions,
                SUM(amount_paid) as total_earnings,
                AVG(duration_minutes) as avg_duration,
                COUNT(CASE WHEN end_time IS NULL THEN 1 END) as active_sessions,
                COUNT(CASE WHEN end_time IS NOT NULL THEN 1 END) as completed_sessions,
                MIN(amount_paid) as min_amount,
                MAX(amount_paid) as max_amount
            ')
            ->first();

        // Calculate additional metrics
        $stats->avg_earnings_per_session = $stats->total_sessions > 0
            ? $stats->total_earnings / $stats->total_sessions
            : 0;

        $stats->completion_rate = $stats->total_sessions > 0
            ? ($stats->completed_sessions / $stats->total_sessions) * 100
            : 0;

        return $stats;
    }

    /**
     * Get chart data for visualization
     */
    private function getChartData($from, $to)
    {
        $dailyData = ParkingSession::whereBetween('start_time', [$from, $to])
            ->selectRaw('
                DATE(start_time) as date,
                SUM(amount_paid) as earnings,
                COUNT(*) as sessions
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $dailyData->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('M d');
            }),
            'earnings' => $dailyData->pluck('earnings'),
            'sessions' => $dailyData->pluck('sessions'),
        ];
    }

    /**
     * Get top performing attendants
     */
    private function getTopAttendants($from, $to)
    {
        return ParkingSession::whereBetween('start_time', [$from, $to])
            ->join('users', 'parking_sessions.created_by', '=', 'users.id')
            ->selectRaw('
                users.name as attendant_name,
                COUNT(*) as session_count,
                SUM(amount_paid) as total_earnings,
                AVG(duration_minutes) as avg_duration
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_earnings')
            ->limit(10)
            ->get();
    }
}

