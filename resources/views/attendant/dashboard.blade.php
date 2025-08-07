@extends('layouts.attendant')

@section('title', 'Attendant Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-6">
        <div class="col-12">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">Welcome, {{ Auth::user()->name }}!</h1>
                    <p class="text-slate-600 mt-1">Manage your parking sessions and track your earnings.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-slate-500">{{ now()->format('l, F j, Y') }}</span>
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm text-green-600 font-medium">Online</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Today's Sessions -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Today's Sessions</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $todayStats['total_sessions'] }}</p>
                    @if($yesterdayStats['total_sessions'] > 0)
                        @php
                            $change = (($todayStats['total_sessions'] - $yesterdayStats['total_sessions']) / $yesterdayStats['total_sessions']) * 100;
                        @endphp
                        <p class="text-sm mt-2 {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="ri-arrow-{{ $change >= 0 ? 'up' : 'down' }}-line mr-1"></i>
                            {{ abs($change) }}% from yesterday
                        </p>
                    @endif
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="ri-time-line text-2xl text-blue-600"></i>
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
                    <i class="ri-play-circle-line text-2xl text-orange-600"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Earnings -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Monthly Earnings</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">₱{{ number_format($monthlyStats['total_earnings'], 2) }}</p>
                    <p class="text-sm text-slate-500 mt-2">This month</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="ri-calendar-line text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Rate and Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Current Rate Info -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-slate-800">Current Rate</h3>
                <a href="{{ route('attendant.rates.index') }}" class="text-blue-600 text-sm font-medium hover:text-blue-700">View All</a>
            </div>
            @if($currentRate)
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-slate-800">{{ $currentRate->name }}</h4>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">
                            Active
                        </span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-600 text-sm">Rate:</span>
                            <span class="font-semibold text-slate-800">{{ $currentRate->formatted_rate_amount }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600 text-sm">Type:</span>
                            <span class="text-slate-800">{{ $currentRate->rate_type_label }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600 text-sm">Grace Period:</span>
                            <span class="text-slate-800">{{ $currentRate->formatted_grace_period }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <i class="ri-error-warning-line text-4xl text-slate-400 mb-2"></i>
                    <p class="text-slate-600">No active rate</p>
                    <p class="text-sm text-slate-500 mt-1">Contact admin to set up rates</p>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <h3 class="text-xl font-bold text-slate-800 mb-6">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('attendant.sessions.create') }}" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center">
                    <div class="text-center">
                        <i class="ri-add-circle-line text-2xl mb-2"></i>
                        <p class="font-semibold">Start New Session</p>
                    </div>
                </a>

                <a href="{{ route('attendant.plates.create') }}" class="bg-gradient-to-r from-green-600 to-emerald-600 text-white p-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center">
                    <div class="text-center">
                        <i class="ri-car-line text-2xl mb-2"></i>
                        <p class="font-semibold">Add New Plate</p>
                    </div>
                </a>

                <a href="{{ route('attendant.sessions.index') }}" class="bg-gradient-to-r from-orange-600 to-red-600 text-white p-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center">
                    <div class="text-center">
                        <i class="ri-list-check text-2xl mb-2"></i>
                        <p class="font-semibold">View All Sessions</p>
                    </div>
                </a>

                <a href="{{ route('attendant.plates.index') }}" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white p-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center">
                    <div class="text-center">
                        <i class="ri-database-2-line text-2xl mb-2"></i>
                        <p class="font-semibold">Manage Plates</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Active Sessions -->
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
                                    {{ $session->start_time->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    {{ $session->getCurrentDurationMinutes() }} min
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">
                                    ₱{{ number_format($session->getEstimatedCurrentFee(), 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('attendant.sessions.edit', $session->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">End Session</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Recent Sessions and Statistics -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        <!-- Recent Sessions -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-slate-800">Recent Sessions</h3>
                <a href="{{ route('attendant.sessions.index') }}" class="text-blue-600 text-sm font-medium hover:text-blue-700">View All</a>
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
                                    <p class="text-sm text-slate-600">{{ $session->start_time->format('M d, H:i') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-slate-800">₱{{ number_format($session->amount_paid ?? 0, 2) }}</p>
                                <p class="text-sm text-slate-500">{{ $session->duration_minutes ?? 0 }} min</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="ri-time-line text-4xl text-slate-400 mb-2"></i>
                    <p class="text-slate-600">No recent sessions</p>
                    <p class="text-sm text-slate-500 mt-1">Start your first session to see it here</p>
                </div>
            @endif
        </div>

        <!-- Performance Statistics -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-slate-800">Your Performance</h3>
                <span class="text-sm text-slate-500">This month</span>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <div>
                            <p class="font-semibold text-slate-800">Total Sessions</p>
                            <p class="text-sm text-slate-600">{{ $sessionStats['total_sessions_all_time'] }} all time</p>
                        </div>
                    </div>
                    <span class="text-blue-600 font-semibold">{{ $monthlyStats['total_sessions'] }}</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <div>
                            <p class="font-semibold text-slate-800">Total Earnings</p>
                            <p class="text-sm text-slate-600">₱{{ number_format($sessionStats['total_earnings_all_time'], 2) }} all time</p>
                        </div>
                    </div>
                    <span class="text-green-600 font-semibold">₱{{ number_format($monthlyStats['total_earnings'], 2) }}</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-purple-50 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                        <div>
                            <p class="font-semibold text-slate-800">Avg Session Duration</p>
                            <p class="text-sm text-slate-600">Based on completed sessions</p>
                        </div>
                    </div>
                    <span class="text-purple-600 font-semibold">{{ round($sessionStats['avg_session_duration']) }} min</span>
                </div>

                @if($sessionStats['best_day_earnings'])
                    <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <div>
                                <p class="font-semibold text-slate-800">Best Day</p>
                                <p class="text-sm text-slate-600">{{ \Carbon\Carbon::parse($sessionStats['best_day_earnings']->date)->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <span class="text-yellow-600 font-semibold">₱{{ number_format($sessionStats['best_day_earnings']->daily_earnings, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Session Statistics Chart -->
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-800">Session Statistics</h3>
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
</div>

<!-- Chart.js Integration Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts here when needed
    console.log('Attendant dashboard loaded successfully');
});
</script>
@endsection

