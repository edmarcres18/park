<?php

namespace App\Http\Controllers\Attendant;

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
        $plates = Plate::latest()->paginate(10);
        return view('attendant.plates.index', compact('plates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('attendant.plates.create');
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

                Log::warning('Duplicate plate creation attempt', [
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

            Log::info('Plate created successfully', [
                'plate_id' => $plate->id,
                'plate_number' => $plate->number,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('attendant.plates.index')
                ->with('success', 'Plate created successfully.');

        } catch (\Exception $e) {
            Log::error('Error creating plate', [
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
        return view('attendant.plates.show', compact('plate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plate $plate)
    {
        return view('attendant.plates.edit', compact('plate'));
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

                    Log::warning('Duplicate plate update attempt', [
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

            Log::info('Plate updated successfully', [
                'plate_id' => $plate->id,
                'old_data' => $oldData,
                'new_data' => $plate->fresh()->toArray(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('attendant.plates.index')
                ->with('success', 'Plate updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating plate', [
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
     */
    public function destroy(Plate $plate)
    {
        try {
            $plateNumber = $plate->number;
            $plate->delete();

            Log::info('Plate deleted successfully', [
                'plate_number' => $plateNumber,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('attendant.plates.index')
                ->with('success', 'Plate deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting plate', [
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
