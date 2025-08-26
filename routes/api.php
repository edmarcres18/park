<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\PlateApiController;
use App\Http\Controllers\Api\ActivityLogApiController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\SessionApiController;
use App\Http\Controllers\Api\TicketApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\SystemApiController;
use App\Http\Controllers\TicketPrintController;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Public endpoint to fetch minimal system settings for clients (e.g., mobile app)
Route::get('/system/settings', [SystemApiController::class, 'settings'])->middleware('throttle:30,1');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::put('users/{user}/status', [UserController::class, 'updateStatus']);
        Route::get('activity-logs', [ActivityLogApiController::class, 'index']);
    });

    // Attendant-only API endpoints
    Route::middleware('role:attendant')->group(function () {
            // Attendants can list, create and view plates only. No edit/update/delete.
        Route::apiResource('plates', PlateApiController::class)->only(['index', 'store', 'show']);

        // Check for duplicate plate numbers
        Route::get('plates/check-duplicate/{number}', [PlateApiController::class, 'checkDuplicate']);

        // Parking Sessions API routes
        Route::get('/sessions/active', [SessionApiController::class, 'active']);
        Route::get('/sessions/history', [SessionApiController::class, 'history']);
        Route::get('/sessions/rates', [SessionApiController::class, 'rates']);
        Route::post('/sessions/start', [SessionApiController::class, 'start']);
        Route::post('/sessions/end/{session}', [SessionApiController::class, 'end'])->whereNumber('session');
        Route::get('/sessions/{session}/print-data', [SessionApiController::class, 'getSessionPrintData'])->whereNumber('session');

        // Ticket API Routes (attendant-only)
        Route::get('tickets', [TicketApiController::class, 'index']);
        Route::get('tickets/{id}', [TicketApiController::class, 'show'])->whereNumber('id');
        Route::post('tickets/generate', [TicketApiController::class, 'generate'])->middleware('throttle:20,1');
        Route::post('tickets/{ticket}/printed', [TicketApiController::class, 'markPrinted'])->whereNumber('ticket');
        Route::get('tickets/statistics', [TicketApiController::class, 'statistics']);
        // Normalized print data for 58mm
        Route::get('tickets/{ticket}/print-data', [TicketPrintController::class, 'api'])->whereNumber('ticket');

        // Location API routes (restricted to attendants)
        Route::post('/location/update', [LocationController::class, 'updateLocation']);
        Route::get('/location/current', [LocationController::class, 'getCurrentLocation']);
        Route::get('/location/history/{hours?}', [LocationController::class, 'getLocationHistory'])->whereNumber('hours');

        // Dashboard metrics for attendants
        Route::get('/dashboard', [DashboardApiController::class, 'index']);

        // Profile show/update for attendants
        Route::get('/profile', [ProfileApiController::class, 'show']);
        Route::put('/profile', [ProfileApiController::class, 'update']);
        Route::patch('/profile', [ProfileApiController::class, 'update']);

        // Printers API removed
    });
});

// JSON fallback for unmatched API routes
Route::fallback(function () {
    return response()->json(['message' => 'Endpoint not found'], 404);
});
