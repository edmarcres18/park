<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartSessionRequest;
use App\Http\Requests\EndSessionRequest;
use App\Models\ParkingSession;
use App\Models\Plate;
use App\Models\ParkingRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Events\ParkingEvent;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->user();
        $query = ParkingSession::with(['creator', 'branch', 'plate'])->orderBy('created_at', 'desc');
        
        // Filter by branch for attendant users
        if (!$user->hasRole('admin') && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        
        $sessions = $query->get();

        // Determine view based on user role
        $viewPath = $user->hasRole('admin') ? 'admin.sessions.index' : 'attendant.sessions.index';

        return view($viewPath, compact('sessions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();
        $platesQuery = Plate::orderBy('number');
        
        // Filter plates by branch for attendant users
        if (!$user->hasRole('admin') && $user->branch_id) {
            $platesQuery->where('branch_id', $user->branch_id);
        }
        
        $plates = $platesQuery->get();
        $parkingRates = ParkingRate::orderBy('name')->get();
        $activeRate = ParkingRate::getActiveRate();

        // Determine view based on user role
        $viewPath = $user->hasRole('admin') ? 'admin.sessions.create' : 'attendant.sessions.create';

        return view($viewPath, compact('plates', 'parkingRates', 'activeRate'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StartSessionRequest $request): RedirectResponse
    {
        $session = ParkingSession::create($request->validated());
        event(new ParkingEvent(
            action: 'session_started',
            title: 'New Parking Session',
            message: "Plate #{$session->plate_number} session started.",
            type: 'success',
            link: route(auth()->user()->hasRole('admin') ? 'admin.sessions.index' : 'attendant.sessions.index'),
            initiatorId: auth()->id(),
            targetRole: 'admin',
        ));
        $route = auth()->user()->hasRole('admin') ? 'admin.sessions.index' : 'attendant.sessions.index';
        return redirect()->route($route)->with('success', 'Parking session started successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ParkingSession $session): View
    {
        $user = auth()->user();
        $platesQuery = Plate::orderBy('number');
        
        // Filter plates by branch for attendant users
        if (!$user->hasRole('admin') && $user->branch_id) {
            $platesQuery->where('branch_id', $user->branch_id);
        }
        
        $plates = $platesQuery->get();
        $parkingRates = ParkingRate::orderBy('name')->get();
        $activeRate = ParkingRate::getActiveRate();

        // Determine view based on user role
        $viewPath = $user->hasRole('admin') ? 'admin.sessions.edit' : 'attendant.sessions.edit';

        return view($viewPath, compact('session', 'plates', 'parkingRates', 'activeRate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EndSessionRequest $request, ParkingSession $session): RedirectResponse
    {
        $session->endSession($request->end_time);

        if ($request->has('printed')) {
            $session->update(['printed' => $request->printed]);
        }

        $route = auth()->user()->hasRole('admin') ? 'admin.sessions.index' : 'attendant.sessions.index';
        event(new ParkingEvent(
            action: 'session_ended',
            title: 'Parking Session Ended',
            message: "Plate #{$session->plate_number} session ended.",
            type: 'info',
            link: route($route),
            initiatorId: auth()->id(),
            targetRole: 'admin',
        ));
        return redirect()->route($route)->with('success', 'Parking session ended successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * Only admin users can delete parking sessions.
     */
    public function destroy(ParkingSession $session): RedirectResponse
    {
        // Check if user has admin role
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized. Only administrators can delete parking sessions.');
        }

        $session->delete();
        return redirect()->route('admin.sessions.index')->with('success', 'Parking session deleted successfully!');
    }
}
