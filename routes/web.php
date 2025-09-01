<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Attendant\DashboardController as AttendantDashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PlateController as AdminPlateController;
use App\Http\Controllers\Attendant\PlateController as AttendantPlateController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\LocationMonitorController;
use App\Http\Controllers\Admin\RateController;
use App\Http\Controllers\Attendant\RateController as AttendantRateController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketPrintController;
use App\Models\User;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\TicketTemplateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ScheduledNotificationController;
use App\Http\Controllers\Admin\BranchController;

    Route::get('/', function () {
        return view('auth.login');
    });

    // // Test route for notifications (remove in production)
    // Route::get('/test-notification', function () {
    //     $admin = \App\Models\User::whereHas('roles', function ($query) {
    //         $query->where('name', 'admin');
    //     })->first();

    //     if ($admin) {
    //         $testUser = new \App\Models\User([
    //             'name' => 'Test User ' . now()->format('H:i:s'),
    //             'email' => 'test' . time() . '@example.com',
    //         ]);

    //         $admin->notify(new \App\Notifications\NewUserRegistered($testUser));
    //         return 'Test notification sent!';
    //     }

    //     return 'No admin user found';
    // });

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->middleware('guest')->name('register');
    Route::post('register', [RegisterController::class, 'register'])->middleware('guest','throttle:3,5');

    Route::get('login', [LoginController::class, 'showLoginForm'])->middleware('guest')->name('login');
    Route::post('login', [LoginController::class, 'login'])->middleware('guest','throttle:5,1');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Notifications routes
    Route::middleware('auth')->group(function () {
        Route::get('/admin/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
        Route::get('/notifications/unread', [NotificationController::class, 'getUnreadNotifications'])->name('notifications.unread');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
    });

    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/pending', [UserController::class, 'pending'])->name('users.pending');
        Route::get('users/rejected', [UserController::class, 'rejected'])->name('users.rejected');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::put('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
        Route::delete('users/{userId}', [UserController::class, 'delete'])->name('users.delete');

        Route::post('users/bulk-approve', [UserController::class, 'bulkApprove'])->name('users.bulk-approve');
        Route::post('users/bulk-reject', [UserController::class, 'bulkReject'])->name('users.bulk-reject');
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::post('users/approve-all', [UserController::class, 'approveAll'])->name('users.approve-all');
        Route::post('users/reject-all', [UserController::class, 'rejectAll'])->name('users.reject-all');
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::put('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
        Route::put('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
        Route::resource('plates', AdminPlateController::class)->except(['destroy']);
        Route::delete('plates/{plate}', [AdminPlateController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('plates.destroy');

        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/sales/export', [ReportController::class, 'exportCsv'])->name('reports.sales.export');
        Route::get('reports/sales/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.sales.export-pdf');

        // Location monitoring routes
        Route::get('location-monitor', [LocationMonitorController::class, 'index'])->name('location-monitor.index');
        Route::get('location-monitor/real-time', [LocationMonitorController::class, 'getRealTimeLocations'])->name('location-monitor.real-time');
        Route::get('location-monitor/user/{user}/history', [LocationMonitorController::class, 'getUserLocationHistory'])->name('location-monitor.user-history');
        Route::post('location-monitor/users-within-radius', [LocationMonitorController::class, 'getUsersWithinRadius'])->name('location-monitor.users-within-radius');
        Route::get('location-monitor/heatmap', [LocationMonitorController::class, 'getHeatmapData'])->name('location-monitor.heatmap');
        Route::get('location-monitor/export', [LocationMonitorController::class, 'exportLocationData'])->name('location-monitor.export');
        Route::delete('location-monitor/clear-old-data', [LocationMonitorController::class, 'clearOldLocationData'])->name('location-monitor.clear-old-data');

        // Parking Rates routes
        Route::resource('rates', RateController::class)->except(['destroy']);
        Route::delete('rates/{rate}', [RateController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('rates.destroy');
        Route::put('rates/{rate}/activate', [RateController::class, 'activate'])->name('rates.activate');

        // Parking Sessions routes
        Route::resource('sessions', SessionController::class)->except(['destroy']);
        Route::delete('sessions/{session}', [SessionController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('sessions.destroy');

        // Admin Ticket Routes
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [TicketController::class, 'index'])->name('index');
            Route::get('/create', [TicketController::class, 'create'])->name('create');
            Route::post('/', [TicketController::class, 'store'])->name('store');
            Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
            Route::get('/{ticket}/print', [TicketController::class, 'print'])->name('print');
            Route::put('/{ticket}/mark-printed', [TicketController::class, 'markPrinted'])->name('mark-printed');
            Route::post('/bulk-print', [TicketController::class, 'bulkPrint'])->name('bulk-print');
            Route::get('/statistics', [TicketController::class, 'statistics'])->name('statistics');

            // 58mm print preview (Blade)
            Route::get('/{ticket}/print-58mm', [TicketPrintController::class, 'web'])->name('print-58mm');
        });

        // Branch Management routes (Admin only)
        Route::resource('branches', BranchController::class);

        // Site Settings routes (Admin only)
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SiteSettingController::class, 'index'])->name('index');
            Route::get('/create', [SiteSettingController::class, 'create'])->name('create');
            Route::post('/', [SiteSettingController::class, 'store'])->name('store');
            Route::get('/{setting}/edit', [SiteSettingController::class, 'edit'])->name('edit');
            Route::put('/{setting}', [SiteSettingController::class, 'update'])->name('update');
            Route::delete('/{setting}', [SiteSettingController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-update', [SiteSettingController::class, 'bulkUpdate'])->name('bulk-update');
            Route::post('/clear-cache', [SiteSettingController::class, 'clearCache'])->name('clear-cache');
        });
    });

    Route::middleware(['auth', 'role:attendant'])->prefix('attendant')->name('attendant.')->group(function () {
        Route::get('dashboard', AttendantDashboardController::class)->name('dashboard');

        // Plates routes - attendants cannot delete
        Route::resource('plates', AttendantPlateController::class)->except(['destroy']);
        Route::delete('plates/{plate}', [AttendantPlateController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('plates.destroy');

        // Sessions routes - attendants cannot delete
        Route::resource('sessions', SessionController::class)->except(['destroy']);
        Route::delete('sessions/{session}', [SessionController::class, 'destroy'])
            ->middleware('role:admin')
            ->name('sessions.destroy');

        // Attendant Ticket Routes (limited access)
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [TicketController::class, 'index'])->name('index');
            Route::post('/', [TicketController::class, 'store'])->name('store');
            Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
            Route::get('/{ticket}/print', [TicketController::class, 'print'])->name('print');
            Route::put('/{ticket}/mark-printed', [TicketController::class, 'markPrinted'])->name('mark-printed');
            Route::post('/bulk-print', [TicketController::class, 'bulkPrint'])->name('bulk-print');

            // 58mm print preview (Blade)
            Route::get('/{ticket}/print-58mm', [TicketPrintController::class, 'web'])->name('print-58mm');
        });

        // Attendant Rates routes (read-only access)
        Route::get('rates', [AttendantRateController::class, 'index'])->name('rates.index');
        Route::get('rates/{rate}', [AttendantRateController::class, 'show'])->name('rates.show');
        Route::get('rates/create', [AttendantRateController::class, 'create'])->name('rates.create');
        Route::post('rates', [AttendantRateController::class, 'store'])->name('rates.store');
        Route::get('rates/{rate}/edit', [AttendantRateController::class, 'edit'])->name('rates.edit');
        Route::put('rates/{rate}', [AttendantRateController::class, 'update'])->name('rates.update');
        Route::delete('rates/{rate}', [AttendantRateController::class, 'destroy'])->name('rates.destroy');
    });

    //Ticket Verification route
    Route::get('tickets/verify/{ticket_number}', [TicketController::class, 'verify'])->name('tickets.verify');

    // Ticket Template Config (logo, location_address)
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
        Route::get('ticket-config', [\App\Http\Controllers\Admin\TicketConfigController::class, 'edit'])->name('admin.ticket-config.edit');
        Route::post('ticket-config', [\App\Http\Controllers\Admin\TicketConfigController::class, 'update'])->name('admin.ticket-config.update');
    });

    // Debug route to test if user exists
    Route::get('users/{userId}/test', function($userId) {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_status' => $user->status,
            'user_roles' => $user->roles->pluck('name')
        ]);
    })->name('users.test');

    // Authenticated user profile routes
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
