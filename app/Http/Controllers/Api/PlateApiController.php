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
     * Check if a plate number already exists.
     */
    public function checkDuplicate($number)
    {
        if (empty($number)) {
            return response()->json([
                'exists' => false,
                'message' => 'Please provide a plate number to check.'
            ], 400);
        }

        // Validate format first
        if (!Plate::isValidFormat($number)) {
            return response()->json([
                'exists' => false,
                'message' => 'Plate number must follow the format: AAA 123, AAA 1234, or AA 12345.',
                'valid_format' => false
            ], 422);
        }

        $exists = Plate::numberExists($number);
        $existingPlate = $exists ? Plate::findByNumber($number) : null;

        activity('plate_api')
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => 'check_duplicate',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'checked_number' => $number,
                'exists' => $exists,
            ])
            ->log("Checked if plate number {$number} exists via API");

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'A plate with this number already exists.' : 'Plate number is available.',
            'plate' => $existingPlate ? new PlateResource($existingPlate) : null,
            'valid_format' => true
        ]);
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
        try {
            // Validate format first
            if (!Plate::isValidFormat($request->number)) {
                return response()->json([
                    'message' => 'Plate number must follow the format: AAA 123, AAA 1234, or AA 12345.',
                    'valid_format' => false
                ], 422);
            }

            // Check if plate already exists before creating
            if (Plate::numberExists($request->number)) {
                $existingPlate = Plate::findByNumber($request->number);

                activity('plate_api')
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'action' => 'store_duplicate_attempt',
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'attempted_number' => $request->number,
                        'existing_plate_id' => $existingPlate->id,
                    ])
                    ->log("Attempted to create duplicate plate {$request->number} via API");

                return response()->json([
                    'message' => 'A plate with this number already exists.',
                    'existing_plate' => new PlateResource($existingPlate)
                ], 422);
            }

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

        } catch (\Exception $e) {
            activity('plate_api')
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'store_error',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'error' => $e->getMessage(),
                    'attempted_number' => $request->number ?? 'unknown',
                ])
                ->log("Error creating plate via API: " . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create plate. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
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
        try {
            // Check if the new number conflicts with another plate
            if ($request->has('number') && $request->number !== $plate->number) {
                if (Plate::numberExists($request->number, $plate->id)) {
                    $conflictingPlate = Plate::findByNumber($request->number);

                    activity('plate_api')
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'action' => 'update_duplicate_attempt',
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'current_plate_id' => $plate->id,
                            'current_number' => $plate->number,
                            'attempted_number' => $request->number,
                            'conflicting_plate_id' => $conflictingPlate->id,
                        ])
                        ->log("Attempted to update plate {$plate->number} to duplicate number {$request->number} via API");

                    return response()->json([
                        'message' => 'Cannot update plate number. A plate with this number already exists.',
                        'conflicting_plate' => new PlateResource($conflictingPlate)
                    ], 422);
                }
            }

            $oldData = $plate->toArray();
            $plate->update($request->validated());

            activity('plate_api')
                ->performedOn($plate)
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'update',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'old_data' => $oldData,
                    'changes' => $plate->getChanges(),
                ])
                ->log("Updated plate {$plate->number} via API");

            return new PlateResource($plate);

        } catch (\Exception $e) {
            activity('plate_api')
                ->causedBy(auth()->user())
                ->withProperties([
                    'action' => 'update_error',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'error' => $e->getMessage(),
                    'plate_id' => $plate->id,
                    'attempted_data' => $request->validated(),
                ])
                ->log("Error updating plate {$plate->number} via API: " . $e->getMessage());

            return response()->json([
                'message' => 'Failed to update plate. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
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
