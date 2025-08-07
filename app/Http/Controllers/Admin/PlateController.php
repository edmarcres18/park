<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plate;
use App\Http\Requests\StorePlateRequest;
use App\Http\Requests\UpdatePlateRequest;

class PlateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plates = Plate::latest()->paginate(10);
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
        Plate::create($request->validated());

        return redirect()->route('admin.plates.index')
            ->with('success', 'Plate created successfully.');
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
        $plate->update($request->validated());

        return redirect()->route('admin.plates.index')
            ->with('success', 'Plate updated successfully.');
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

        $plate->delete();

        return redirect()->route('admin.plates.index')
            ->with('success', 'Plate deleted successfully.');
    }
}
