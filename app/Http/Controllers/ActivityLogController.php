<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs
     */
    public function index(Request $request)
    {
        // Check if user has admin role
        if (!Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access');
        }

        $query = Activity::with('causer', 'subject')
            ->latest('created_at');

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', 'App\\Models\\User');
        }

        if ($request->filled('model')) {
            $modelMap = [
                'user' => 'App\\Models\\User',
                'plate' => 'App\\Models\\Plate',
                'rate' => 'App\\Models\\Rate',
                'session' => 'App\\Models\\Session',
                'ticket' => 'App\\Models\\Ticket',
            ];
            
            if (isset($modelMap[$request->model])) {
                $query->where('subject_type', $modelMap[$request->model]);
            }
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $activities = $query->paginate(20);

        // Get filter options
        $users = \App\Models\User::select('id', 'name')->get();
        $logNames = Activity::distinct()->pluck('log_name')->filter()->sort();

        return view('admin.activity-logs.index', compact('activities', 'users', 'logNames'));
    }

    /**
     * Get model name from subject type
     */
    private function getModelName($subjectType)
    {
        if (!$subjectType) {
            return null;
        }

        $parts = explode('\\', $subjectType);
        return end($parts);
    }
}
