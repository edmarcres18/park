@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-6">
        <div class="col-12">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">Dashboard Overview</h1>
                    <p class="text-slate-600 mt-1">Welcome back, {{ Auth::user()->name }}! Here's what's happening today.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-slate-500">{{ now()->format('l, F j, Y') }}</span>
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm text-green-600 font-medium">Live</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Today's Vehicles -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Today's Vehicles</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $todayStats['total_vehicles'] }}</p>
                    @if($yesterdayStats['total_vehicles'] > 0)
                        @php
                            $change = (($todayStats['total_vehicles'] - $yesterdayStats['total_vehicles']) / $yesterdayStats['total_vehicles']) * 100;
                        @endphp
                        <p class="text-sm mt-2 {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="ri-arrow-{{ $change >= 0 ? 'up' : 'down' }}-line mr-1"></i>
                            {{ abs($change) }}% from yesterday
                        </p>
                    @endif
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="ri-car-line text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Today's Earnings -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Today's Earnings</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">₱{{ number_format($todayStats['total_earnings'], 2) }}</p>
                    @if($yesterdayStats['total_earnings'] > 0)
                        @php
                            $change = (($todayStats['total_earnings'] - $yesterdayStats['total_earnings']) / $yesterdayStats['total_earnings']) * 100;
                        @endphp
                        <p class="text-sm mt-2 {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="ri-arrow-{{ $change >= 0 ? 'up' : 'down' }}-line mr-1"></i>
                            {{ abs($change) }}% from yesterday
                        </p>
                    @endif
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="ri-money-dollar-circle-line text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Active Sessions -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Active Sessions</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $todayStats['active_sessions'] }}</p>
                    <p class="text-sm text-slate-500 mt-2">Currently running</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="ri-time-line text-2xl text-orange-600"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Monthly Revenue</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">₱{{ number_format($monthlyStats['total_earnings'], 2) }}</p>
                    <p class="text-sm text-slate-500 mt-2">This month</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="ri-bar-chart-2-line text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Analytics -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-slate-800">Revenue Trend</h3>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 bg-blue-100 text-blue-600 text-sm rounded-lg font-medium">7D</button>
                    <button class="px-3 py-1 text-slate-600 text-sm rounded-lg font-medium hover:bg-slate-100">30D</button>
                    <button class="px-3 py-1 text-slate-600 text-sm rounded-lg font-medium hover:bg-slate-100">90D</button>
                </div>
            </div>
            <div class="h-64 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl flex items-center justify-center">
                <div class="text-center">
                    <i class="ri-bar-chart-2-line text-4xl text-slate-400 mb-2"></i>
                    <p class="text-slate-600">Chart Component</p>
                    <p class="text-sm text-slate-500 mt-1">Integrate with Chart.js</p>
                </div>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-slate-800">User Statistics</h3>
                <a href="{{ route('admin.users.index') }}" class="text-blue-600 text-sm font-medium hover:text-blue-700">View All</a>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <div>
                            <p class="font-semibold text-slate-800">Active Attendants</p>
                            <p class="text-sm text-slate-600">{{ $userStats['total_attendants'] }} users</p>
                        </div>
                    </div>
                    <span class="text-green-600 font-semibold">{{ $userStats['online_attendants'] }} online</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <div>
                            <p class="font-semibold text-slate-800">Pending Approvals</p>
                            <p class="text-sm text-slate-600">{{ $userStats['pending_attendants'] }} users</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.pending') }}" class="text-yellow-600 font-semibold hover:text-yellow-700">Review</a>
                </div>

                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <div>
                            <p class="font-semibold text-slate-800">Current Rate</p>
                            <p class="text-sm text-slate-600">{{ $rateStats['active_rate'] ? $rateStats['active_rate']->name : 'No active rate' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.rates.index') }}" class="text-blue-600 font-semibold hover:text-blue-700">Manage</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Attendants and Recent Activity -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        <!-- Top Attendants -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-slate-800">Top Attendants Today</h3>
                <a href="{{ route('admin.reports.sales') }}" class="text-blue-600 text-sm font-medium hover:text-blue-700">View Reports</a>
            </div>
            @if($topAttendants->count() > 0)
                <div class="space-y-4">
                    @foreach($topAttendants as $attendant)
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ substr($attendant->attendant_name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $attendant->attendant_name }}</p>
                                    <p class="text-sm text-slate-600">{{ $attendant->session_count }} sessions</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-slate-800">₱{{ number_format($attendant->total_earnings, 2) }}</p>
                                <p class="text-sm text-slate-500">{{ round($attendant->avg_duration) }} min avg</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="ri-user-line text-4xl text-slate-400 mb-2"></i>
                    <p class="text-slate-600">No attendant activity today</p>
                    <p class="text-sm text-slate-500 mt-1">Attendants will appear here when they start sessions</p>
                </div>
            @endif
        </div>

        <!-- Recent Sessions -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-slate-800">Recent Sessions</h3>
                <a href="{{ route('admin.sessions.index') }}" class="text-blue-600 text-sm font-medium hover:text-blue-700">View All</a>
            </div>
            @if($recentSessions->count() > 0)
                <div class="space-y-3">
                    @foreach($recentSessions as $session)
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                    {{ substr($session->plate_number, 0, 2) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $session->plate_number }}</p>
                                    <p class="text-sm text-slate-600">{{ optional($session->creator)->name ?? 'Unknown' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-slate-800">₱{{ number_format($session->amount_paid ?? 0, 2) }}</p>
                                <p class="text-sm text-slate-500">{{ $session->start_time->format('H:i') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="ri-time-line text-4xl text-slate-400 mb-2"></i>
                    <p class="text-slate-600">No recent sessions</p>
                    <p class="text-sm text-slate-500 mt-1">Sessions will appear here when created</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Active Sessions Table -->
    @if($activeSessions->count() > 0)
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-slate-800">Active Sessions</h3>
                <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                    {{ $activeSessions->count() }} Active
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Plate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Attendant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Started</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Current Fee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($activeSessions as $session)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xs font-bold mr-3">
                                            {{ substr($session->plate_number, 0, 2) }}
                                        </div>
                                        <span class="font-semibold text-slate-800">{{ $session->plate_number }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    {{ optional($session->creator)->name ?? 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    {{ $session->start_time->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    {{ $session->getCurrentDurationMinutes() }} min
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">
                                    ₱{{ number_format($session->getEstimatedCurrentFee(), 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('admin.sessions.edit', $session->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">End Session</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('admin.sessions.create') }}" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center">
            <div class="text-center">
                <i class="ri-add-circle-line text-3xl mb-2"></i>
                <p class="font-semibold">Start Session</p>
            </div>
        </a>

        <a href="{{ route('admin.reports.sales') }}" class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center">
            <div class="text-center">
                <i class="ri-bar-chart-2-line text-3xl mb-2"></i>
                <p class="font-semibold">View Reports</p>
            </div>
        </a>

        <a href="{{ route('admin.users.pending') }}" class="bg-gradient-to-r from-yellow-600 to-orange-600 text-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center">
            <div class="text-center">
                <i class="ri-user-add-line text-3xl mb-2"></i>
                <p class="font-semibold">Pending Users</p>
            </div>
        </a>

        <a href="{{ route('admin.rates.index') }}" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white p-6 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center">
            <div class="text-center">
                <i class="ri-settings-2-line text-3xl mb-2"></i>
                <p class="font-semibold">Manage Rates</p>
            </div>
        </a>
    </div>
</div>

<!-- Chart.js Integration Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts here when needed
    console.log('Dashboard loaded successfully');
});
</script>
@endsection

