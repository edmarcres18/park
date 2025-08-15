<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

class ActivityLogApiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * List activity logs with optional filters: date_from, date_to, user_id, log_name.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Activity::query()->latest('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->input('date_from'))->toDateString());
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->input('date_to'))->toDateString());
        }
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->integer('user_id'));
        }
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->input('log_name'));
        }

        $perPage = (int) $request->input('per_page', 15);
        $paginator = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'data' => collect($paginator->items())->map(function (Activity $activity) {
                return [
                    'id' => $activity->id,
                    'log_name' => $activity->log_name,
                    'description' => $activity->description,
                    'subject_type' => $activity->subject_type,
                    'subject_id' => $activity->subject_id,
                    'causer_type' => $activity->causer_type,
                    'causer_id' => $activity->causer_id,
                    'properties' => $activity->properties,
                    'created_at' => optional($activity->created_at)->toDateTimeString(),
                ];
            }),
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}

