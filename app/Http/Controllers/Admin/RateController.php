<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParkingRate;
use App\Http\Requests\StoreParkingRateRequest;
use App\Http\Requests\UpdateParkingRateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $rates = ParkingRate::orderBy('is_active', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->get();
            
            $activeRate = ParkingRate::getActiveRate();
            
            return view('admin.rates.index', compact('rates', 'activeRate'));
        } catch (\Exception $e) {
            Log::error('Error loading parking rates: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')
                           ->with('error', 'Unable to load parking rates. Please try again.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $rateTypes = ParkingRate::RATE_TYPES;
            return view('admin.rates.create', compact('rateTypes'));
        } catch (\Exception $e) {
            Log::error('Error loading create rate form: ' . $e->getMessage());
            return redirect()->route('admin.rates.index')
                           ->with('error', 'Unable to load create form. Please try again.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreParkingRateRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Handle checkbox value
            $data['is_active'] = $request->boolean('is_active');
            
            $rate = ParkingRate::create($data);
            
            $message = 'Parking rate created successfully.';
            if ($data['is_active']) {
                $message .= ' This rate is now active and all other rates have been deactivated.';
            }
            
            Log::info('Parking rate created', [
                'rate_id' => $rate->id,
                'created_by' => auth()->id(),
                'rate_data' => $data
            ]);
            
            return redirect()->route('admin.rates.index')
                           ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error creating parking rate: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Unable to create parking rate. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ParkingRate $rate)
    {
        try {
            return view('admin.rates.show', compact('rate'));
        } catch (\Exception $e) {
            Log::error('Error showing parking rate: ' . $e->getMessage());
            return redirect()->route('admin.rates.index')
                           ->with('error', 'Unable to load parking rate details.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ParkingRate $rate)
    {
        try {
            $rateTypes = ParkingRate::RATE_TYPES;
            return view('admin.rates.edit', compact('rate', 'rateTypes'));
        } catch (\Exception $e) {
            Log::error('Error loading edit rate form: ' . $e->getMessage());
            return redirect()->route('admin.rates.index')
                           ->with('error', 'Unable to load edit form. Please try again.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateParkingRateRequest $request, ParkingRate $rate)
    {
        try {
            $data = $request->validated();
            
            // Handle checkbox value
            $data['is_active'] = $request->boolean('is_active');
            
            $oldData = $rate->toArray();
            $rate->update($data);
            
            $message = 'Parking rate updated successfully.';
            if ($data['is_active'] && !$oldData['is_active']) {
                $message .= ' This rate is now active and all other rates have been deactivated.';
            }
            
            Log::info('Parking rate updated', [
                'rate_id' => $rate->id,
                'updated_by' => auth()->id(),
                'old_data' => $oldData,
                'new_data' => $data
            ]);
            
            return redirect()->route('admin.rates.index')
                           ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error updating parking rate: ' . $e->getMessage(), [
                'rate_id' => $rate->id,
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Unable to update parking rate. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     * Only admin users can delete parking rates.
     */
    public function destroy(ParkingRate $rate)
    {
        try {
            // Check if user has admin role
            if (!auth()->user()->hasRole('admin')) {
                abort(403, 'Unauthorized. Only administrators can delete parking rates.');
            }
            
            // Check if this is the active rate
            if ($rate->is_active) {
                return redirect()->route('admin.rates.index')
                               ->with('error', 'Cannot delete the active parking rate. Please activate another rate first.');
            }
            
            $rateName = $rate->name ?: "Rate #{$rate->id}";
            
            Log::info('Parking rate deleted', [
                'rate_id' => $rate->id,
                'deleted_by' => auth()->id(),
                'rate_data' => $rate->toArray()
            ]);
            
            $rate->delete();
            
            return redirect()->route('admin.rates.index')
                           ->with('success', "Parking rate '{$rateName}' has been deleted successfully.");
        } catch (\Exception $e) {
            Log::error('Error deleting parking rate: ' . $e->getMessage(), [
                'rate_id' => $rate->id,
                'user_id' => auth()->id()
            ]);
            
            return redirect()->route('admin.rates.index')
                           ->with('error', 'Unable to delete parking rate. Please try again.');
        }
    }

    /**
     * Activate a specific rate.
     */
    public function activate(ParkingRate $rate)
    {
        try {
            $rate->update(['is_active' => true]);
            
            Log::info('Parking rate activated', [
                'rate_id' => $rate->id,
                'activated_by' => auth()->id()
            ]);
            
            return redirect()->route('admin.rates.index')
                           ->with('success', 'Parking rate has been activated successfully. All other rates have been deactivated.');
        } catch (\Exception $e) {
            Log::error('Error activating parking rate: ' . $e->getMessage());
            return redirect()->route('admin.rates.index')
                           ->with('error', 'Unable to activate parking rate. Please try again.');
        }
    }
}
