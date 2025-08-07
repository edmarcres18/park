<?php

namespace App\Http\Controllers\Attendant;

use App\Http\Controllers\Controller;
use App\Models\ParkingRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RateController extends Controller
{
    /**
     * Display a listing of the resource (read-only for attendants).
     */
    public function index()
    {
        try {
            $rates = ParkingRate::orderBy('is_active', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->get();

            $activeRate = ParkingRate::getActiveRate();

            return view('attendant.rates.index', compact('rates', 'activeRate'));
        } catch (\Exception $e) {
            Log::error('Error loading parking rates for attendant: ' . $e->getMessage());
            return redirect()->route('attendant.dashboard')
                           ->with('error', 'Unable to load parking rates. Please try again.');
        }
    }

    /**
     * Display the specified resource (read-only for attendants).
     */
    public function show(ParkingRate $rate)
    {
        try {
            return view('attendant.rates.show', compact('rate'));
        } catch (\Exception $e) {
            Log::error('Error showing parking rate for attendant: ' . $e->getMessage());
            return redirect()->route('attendant.rates.index')
                           ->with('error', 'Unable to load parking rate details.');
        }
    }
}
