<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plate;
use App\Http\Requests\StorePlateRequest;
use App\Http\Requests\UpdatePlateRequest;
use App\Http\Resources\PlateResource;

class PlateApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PlateResource::collection(Plate::latest()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlateRequest $request)
    {
        $plate = Plate::create($request->validated());

        return new PlateResource($plate);
    }

    /**
     * Display the specified resource.
     */
    public function show(Plate $plate)
    {
        return new PlateResource($plate);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlateRequest $request, Plate $plate)
    {
        $plate->update($request->validated());

        return new PlateResource($plate);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plate $plate)
    {
        $plate->delete();

        return response()->noContent();
    }
}
