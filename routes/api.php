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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::put('users/{user}/status', [UserController::class, 'updateStatus']);
        Route::get('activity-logs', [ActivityLogApiController::class, 'index']);
    });

    Route::middleware(['auth:sanctum', 'role:admin|attendant'])->group(function () {
        Route::apiResource('plates', PlateApiController::class);
        
        // Parking Sessions API routes
        Route::get('/sessions/active', [SessionApiController::class, 'active']);
        Route::get('/sessions/history', [SessionApiController::class, 'history']);
        Route::post('/sessions/start', [SessionApiController::class, 'start']);
        Route::post('/sessions/end/{session}', [SessionApiController::class, 'end']);
        
        // Ticket API Routes (accessible by both admin and attendant)
        Route::get('tickets/{id}', [TicketApiController::class, 'show']);
        Route::post('tickets/generate', [TicketApiController::class, 'generate']);
    });

    // Location API routes  
    Route::post('/location/update', [LocationController::class, 'updateLocation']);
    Route::get('/location/current', [LocationController::class, 'getCurrentLocation']);
    Route::get('/location/history/{hours?}', [LocationController::class, 'getLocationHistory']);
});
