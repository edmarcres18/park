@extends('layouts.admin')

@section('title', 'Sales Reports')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-6">
        <div class="col-12">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">Sales Reports</h1>
                    <p class="text-slate-600 mt-1">Comprehensive sales analytics and reporting</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.reports.sales.export') }}?from={{ $from->format('Y-m-d') }}&to={{ $to->format('Y-m-d') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        <i class="ri-download-line mr-2"></i>Export CSV
                    </a>
                    <a href="{{ route('admin.reports.sales.export') }}?from={{ $from->format('Y-m-d') }}&to={{ $to->format('Y-m-d') }}&format=pdf"
                       class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        <i class="ri-file-pdf-line mr-2"></i>Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 mb-8">
        <form method="GET" action="{{ route('admin.reports.sales') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">From Date</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">To Date</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    <i class="ri-search-line mr-2"></i>Filter
                </button>
            </div>
            <div>
                <a href="{{ route('admin.reports.sales') }}" class="bg-slate-600 hover:bg-slate-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    <i class="ri-refresh-line mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Total Sessions</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ $summaryStats->total_sessions }}</p>
                    <p class="text-sm text-slate-500 mt-2">In selected period</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="ri-time-line text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Total Revenue</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">₱{{ number_format($summaryStats->total_earnings, 2) }}</p>
                    <p class="text-sm text-slate-500 mt-2">Gross earnings</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="ri-money-dollar-circle-line text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Avg per Session</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">₱{{ number_format($summaryStats->avg_earnings_per_session, 2) }}</p>
                    <p class="text-sm text-slate-500 mt-2">Average earnings</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="ri-bar-chart-2-line text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-600 text-sm font-medium">Completion Rate</p>
                    <p class="text-3xl font-bold text-slate-800 mt-1">{{ number_format($summaryStats->completion_rate, 1) }}%</p>
                    <p class="text-sm text-slate-500 mt-2">Sessions completed</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="ri-check-line text-2xl text-orange-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-slate-800">Revenue Trend</h3>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 bg-blue-100 text-blue-600 text-sm rounded-lg font-medium">Daily</button>
                    <button class="px-3 py-1 text-slate-600 text-sm rounded-lg font-medium hover:bg-slate-100">Weekly</button>
                    <button class="px-3 py-1 text-slate-600 text-sm rounded-lg font-medium hover:bg-slate-100">Monthly</button>
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

        <!-- Sessions Chart -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-slate-800">Session Count</h3>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 bg-green-100 text-green-600 text-sm rounded-lg font-medium">Daily</button>
                    <button class="px-3 py-1 text-slate-600 text-sm rounded-lg font-medium hover:bg-slate-100">Weekly</button>
                    <button class="px-3 py-1 text-slate-600 text-sm rounded-lg font-medium hover:bg-slate-100">Monthly</button>
                </div>
            </div>
            <div class="h-64 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl flex items-center justify-center">
                <div class="text-center">
                    <i class="ri-line-chart-line text-4xl text-slate-400 mb-2"></i>
                    <p class="text-slate-600">Chart Component</p>
                    <p class="text-sm text-slate-500 mt-1">Integrate with Chart.js</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Attendants -->
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-800">Top Performing Attendants</h3>
            <span class="text-sm text-slate-500">By revenue in selected period</span>
        </div>
        @if($topAttendants->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Attendant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Sessions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Total Revenue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Avg Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Performance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($topAttendants as $index => $attendant)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($index < 3)
                                            <div class="w-8 h-8 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center text-white text-sm font-bold mr-3">
                                                {{ $index + 1 }}
                                            </div>
                                        @else
                                            <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center text-slate-600 text-sm font-bold mr-3">
                                                {{ $index + 1 }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold mr-3">
                                            {{ substr($attendant->attendant_name, 0, 1) }}
                                        </div>
                                        <span class="font-semibold text-slate-800">{{ $attendant->attendant_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    {{ $attendant->session_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">
                                    ₱{{ number_format($attendant->total_earnings, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    {{ round($attendant->avg_duration) }} min
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $percentage = ($attendant->total_earnings / $summaryStats->total_earnings) * 100;
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="w-20 bg-slate-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-sm text-slate-600">{{ number_format($percentage, 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i class="ri-user-line text-4xl text-slate-400 mb-2"></i>
                <p class="text-slate-600">No attendant data available</p>
                <p class="text-sm text-slate-500 mt-1">Attendants will appear here when they have sessions</p>
            </div>
        @endif
    </div>

    <!-- Sales by Rate -->
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-800">Sales by Parking Rate</h3>
            <span class="text-sm text-slate-500">Revenue breakdown by rate type</span>
        </div>
        @if($salesByRate->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($salesByRate as $rate)
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-4 border border-blue-200">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-slate-800">{{ $rate->rate_name }}</h4>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">
                                {{ $rate->rate_type_label }}
                            </span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-600 text-sm">Rate:</span>
                                <span class="font-semibold text-slate-800">₱{{ number_format($rate->rate_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600 text-sm">Sessions:</span>
                                <span class="text-slate-800">{{ $rate->session_count }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600 text-sm">Revenue:</span>
                                <span class="font-semibold text-slate-800">₱{{ number_format($rate->total_earnings, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600 text-sm">Avg Duration:</span>
                                <span class="text-slate-800">{{ round($rate->avg_duration) }} min</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="ri-settings-2-line text-4xl text-slate-400 mb-2"></i>
                <p class="text-slate-600">No rate data available</p>
                <p class="text-sm text-slate-500 mt-1">Rate data will appear here when sessions are created</p>
            </div>
        @endif
    </div>

    <!-- Recent Sessions -->
    <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-800">Recent Sessions</h3>
            <a href="{{ route('admin.sessions.index') }}" class="text-blue-600 text-sm font-medium hover:text-blue-700">View All Sessions</a>
        </div>
        @if($recentSessions->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Session ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Plate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Attendant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Start Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach($recentSessions as $session)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    #{{ $session->id }}
                                </td>
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
                                    {{ $session->start_time->format('M d, H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    {{ $session->duration_minutes ?? $session->getCurrentDurationMinutes() }} min
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800">
                                    ₱{{ number_format($session->amount_paid ?? $session->getEstimatedCurrentFee(), 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($session->isActive())
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">Active</span>
                                    @else
                                        <span class="bg-slate-100 text-slate-800 text-xs font-medium px-2 py-1 rounded-full">Completed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i class="ri-time-line text-4xl text-slate-400 mb-2"></i>
                <p class="text-slate-600">No sessions found</p>
                <p class="text-sm text-slate-500 mt-1">Sessions will appear here when created</p>
            </div>
        @endif
    </div>
</div>

<!-- Chart.js Integration Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts here when needed
    console.log('Sales report loaded successfully');
});
</script>
@endsection
