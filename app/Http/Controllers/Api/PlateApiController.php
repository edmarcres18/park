<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plate;
use App\Http\Requests\StorePlateRequest;
use App\Http\Requests\UpdatePlateRequest;
use App\Http\Resources\PlateResource;
use Spatie\Activitylog\Models\Activity;

class PlateApiController extends Controller
{
    public function __construct()
    {
        // Allow attendants to list/create/show; restrict update/delete to admin only via routes/middleware
        $this->middleware(['auth:sanctum']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Plate::query();

        if (request()->filled('q')) {
            $q = request('q');
            $query->where(function ($inner) use ($q) {
                $inner->where('number', 'like', "%{$q}%")
                      ->orWhere('owner_name', 'like', "%{$q}%")
                      ->orWhere('vehicle_type', 'like', "%{$q}%");
            });
        }

        $result = PlateResource::collection($query->latest()->paginate());

        activity('plate_api')
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'index',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'query' => request()->only(['q']),
            ])
            ->log('Listed plates via API');

        return $result;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlateRequest $request)
    {
        $plate = Plate::create($request->validated());

        activity('plate_api')
            ->performedOn($plate)
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'store',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload' => $request->safe()->only(['number','owner_name','vehicle_type']),
            ])
            ->log("Created plate {$plate->number} via API");

        return new PlateResource($plate);
    }

    /**
     * Display the specified resource.
     */
    public function show(Plate $plate)
    {
        activity('plate_api')
            ->performedOn($plate)
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'show',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log("Viewed plate {$plate->number} via API");

        return new PlateResource($plate);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlateRequest $request, Plate $plate)
    {
        $plate->update($request->validated());

        activity('plate_api')
            ->performedOn($plate)
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'update',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'changes' => $plate->getChanges(),
            ])
            ->log("Updated plate {$plate->number} via API");

        return new PlateResource($plate);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plate $plate)
    {
        $plate->delete();

        activity('plate_api')
            ->performedOn($plate)
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'destroy',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'plate_number' => $plate->number,
            ])
            ->log("Deleted plate {$plate->number} via API");

        return response()->noContent();
    }
}
