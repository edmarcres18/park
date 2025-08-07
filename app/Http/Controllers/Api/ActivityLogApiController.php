<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Http\Resources\ActivityLogResource;
use App\Models\User;

/**
 * Activity Log API Controller
 * 
 *Provides JSON API endpoints for activity logs
 * with filtering and pagination capabilities.
 */
class ActivityLogApiController extends Controller
{
    /**
     * Display a paginated listing of activity logs with filters
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        // Check if user has admin role
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc');

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply user filter
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', User::class);
        }

        // Apply model filter
        if ($request->filled('model')) {
            $modelMap = [
                'user' => 'App\\Models\\User',
                'plate' => 'App\\Models\\Plate',
                'rate' => 'App\\Models\\Rate',
                'session' => 'App\\Models\\Session',
                'ticket' => 'App\\Models\\Ticket',
            ];
            
            if ($request->model === 'authentication') {
                $query->where('log_name', 'authentication');
            } elseif (isset($modelMap[$request->model])) {
                $query->where('subject_type', $modelMap[$request->model]);
            }
        }

        // Apply log name filter
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $perPage = min($request->get('per_page', 20), 100); // Max 100 per page
        $activities = $query->paginate($perPage)->withQueryString();

        return ActivityLogResource::collection($activities);
    }
}
