<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plate;
use App\Http\Requests\StorePlateRequest;
use App\Http\Requests\UpdatePlateRequest;
use Illuminate\Support\Facades\Log;

class PlateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plates = Plate::with('branch')->latest()->paginate(10);
        return view('admin.plates.index', compact('plates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.plates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlateRequest $request)
    {
        try {
            // Check if plate already exists before creating
            if (Plate::numberExists($request->number)) {
                $existingPlate = Plate::findByNumber($request->number);

                Log::warning('Admin duplicate plate creation attempt', [
                    'attempted_number' => $request->number,
                    'existing_plate_id' => $existingPlate->id,
                    'user_id' => auth()->id(),
                    'ip' => $request->ip(),
                ]);

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['number' => 'A plate with this number already exists.']);
            }

            $plate = Plate::create($request->validated());

            Log::info('Admin created plate successfully', [
                'plate_id' => $plate->id,
                'plate_number' => $plate->number,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('admin.plates.index')
                ->with('success', 'Plate created successfully.');

        } catch (\Exception $e) {
            Log::error('Admin error creating plate', [
                'error' => $e->getMessage(),
                'attempted_data' => $request->only(['number', 'owner_name', 'vehicle_type']),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create plate. Please try again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Plate $plate)
    {
        return view('admin.plates.show', compact('plate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plate $plate)
    {
        return view('admin.plates.edit', compact('plate'));
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

                    Log::warning('Admin duplicate plate update attempt', [
                        'current_plate_id' => $plate->id,
                        'current_number' => $plate->number,
                        'attempted_number' => $request->number,
                        'conflicting_plate_id' => $conflictingPlate->id,
                        'user_id' => auth()->id(),
                        'ip' => $request->ip(),
                    ]);

                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['number' => 'Cannot update plate number. A plate with this number already exists.']);
                }
            }

            $oldData = $plate->toArray();
            $plate->update($request->validated());

            Log::info('Admin updated plate successfully', [
                'plate_id' => $plate->id,
                'old_data' => $oldData,
                'new_data' => $plate->fresh()->toArray(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('admin.plates.index')
                ->with('success', 'Plate updated successfully.');

        } catch (\Exception $e) {
            Log::error('Admin error updating plate', [
                'error' => $e->getMessage(),
                'plate_id' => $plate->id,
                'attempted_data' => $request->validated(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update plate. Please try again.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     * Only admin users can delete plates.
     */
    public function destroy(Plate $plate)
    {
        // Check if user has admin role
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized. Only administrators can delete plates.');
        }

        try {
            $plateNumber = $plate->number;
            $plate->delete();

            Log::info('Admin deleted plate successfully', [
                'plate_number' => $plateNumber,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('admin.plates.index')
                ->with('success', 'Plate deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Admin error deleting plate', [
                'error' => $e->getMessage(),
                'plate_id' => $plate->id,
                'plate_number' => $plate->number,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete plate. Please try again.']);
        }
    }
}
