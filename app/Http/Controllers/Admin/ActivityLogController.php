<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * ActivityLogController handles the activity logs display for admin users.
 *
 * This controller provides functionality to view and filter activity logs
 * with pagination, date range filtering, user filtering, and model filtering.
 */
class ActivityLogController extends Controller
{
    /**
     * Display a paginated list of activity logs with optional filters.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // Validate request parameters
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'user_id' => 'nullable|exists:users,id',
            'model' => 'nullable|string|in:user,plate,rate,session,ticket,auth',
            'per_page' => 'nullable|integer|min:10|max:100'
        ]);

        // Build the query with eager loading
        $query = Activity::with(['causer', 'subject'])
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->filled('user_id'), function ($q) use ($request) {
                $q->where('causer_id', $request->user_id)->where('causer_type', User::class);
            })
            ->when($request->filled('model'), function ($q) use ($request) {
                $modelMap = [
                    'user' => 'App\\Models\\User',
                    'plate' => 'App\\Models\\Plate',
                    'rate' => 'App\\Models\\Rate',
                    'session' => 'App\\Models\\Session',
                    'ticket' => 'App\\Models\\Ticket',
                    'auth' => 'auth'
                ];

                if ($request->model === 'auth') {
                    $q->where('log_name', 'auth');
                } elseif (isset($modelMap[$request->model])) {
                    $q->where('subject_type', $modelMap[$request->model]);
                }
            })
            ->latest();

        // Get paginated results
        $perPage = $request->get('per_page', 15);
        $activities = $query->paginate($perPage)->withQueryString();

        // Get users for filter dropdown
        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        // Available models for filter
        $models = [
            'user' => 'Users',
            'plate' => 'Plates',
            'rate' => 'Rates',
            'session' => 'Sessions',
            'ticket' => 'Tickets',
            'auth' => 'Authentication'
        ];

        return view('activity_logs.index', compact('activities', 'users', 'models'));
    }
}
