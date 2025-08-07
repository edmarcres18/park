@extends('layouts.admin')

@section('title', 'Activity Logs')
@section('subtitle', 'Track system activities and user actions across your platform')

@section('content')
<!-- Activity Logs Header -->
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl flex items-center justify-center">
                <i class="ri-history-line text-2xl text-white"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Activity Logs</h2>
                <p class="text-sm text-slate-600">{{ $activities->total() }} total activities recorded</p>
            </div>
        </div>

        <!-- Export Button -->
        <div class="flex items-center space-x-3">
            <button class="inline-flex items-center px-4 py-2 bg-white/80 hover:bg-white border border-slate-200 rounded-xl text-slate-700 hover:text-slate-900 transition-all duration-200 shadow-sm hover:shadow-md">
                <i class="ri-download-line mr-2"></i>
                Export
            </button>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="glass-card rounded-2xl p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-slate-800 flex items-center">
            <i class="ri-filter-3-line mr-2 text-blue-600"></i>
            Filters
        </h3>
        <div class="text-sm text-slate-500">
            {{ $activities->count() }} of {{ $activities->total() }} shown
        </div>
    </div>

    <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Date From -->
            <div class="space-y-2">
                <label for="date_from" class="block text-sm font-medium text-slate-700">
                    <i class="ri-calendar-line mr-1 text-blue-600"></i>
                    Date From
                </label>
                <input type="date"
                       id="date_from"
                       name="date_from"
                       value="{{ request('date_from') }}"
                       class="w-full px-4 py-3 bg-white/60 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
            </div>

            <!-- Date To -->
            <div class="space-y-2">
                <label for="date_to" class="block text-sm font-medium text-slate-700">
                    <i class="ri-calendar-check-line mr-1 text-blue-600"></i>
                    Date To
                </label>
                <input type="date"
                       id="date_to"
                       name="date_to"
                       value="{{ request('date_to') }}"
                       class="w-full px-4 py-3 bg-white/60 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
            </div>

            <!-- User Filter -->
            <div class="space-y-2">
                <label for="user_id" class="block text-sm font-medium text-slate-700">
                    <i class="ri-user-line mr-1 text-blue-600"></i>
                    User
                </label>
                <select id="user_id" name="user_id" class="w-full px-4 py-3 bg-white/60 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Model Filter -->
            <div class="space-y-2">
                <label for="model" class="block text-sm font-medium text-slate-700">
                    <i class="ri-database-line mr-1 text-blue-600"></i>
                    Model
                </label>
                <select id="model" name="model" class="w-full px-4 py-3 bg-white/60 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                    <option value="">All Models</option>
                    @foreach($models as $key => $name)
                        <option value="{{ $key }}" {{ request('model') == $key ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Filter Actions -->
        <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-200">
            <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium shadow-lg hover:shadow-xl">
                <i class="ri-search-line mr-2"></i>
                Apply Filters
            </button>
            <a href="{{ route('admin.activity-logs.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-white/80 hover:bg-white border border-slate-200 text-slate-700 rounded-xl hover:text-slate-900 transition-all duration-200 font-medium shadow-sm hover:shadow-md">
                <i class="ri-refresh-line mr-2"></i>
                Clear Filters
            </a>
        </div>
    </form>
</div>

                <!-- Activity Logs Table -->
@if($activities->count() > 0)
<div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Model</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Details</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    @foreach($activities as $activity)
                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                <small class="d-block text-muted">
                                                    {{ $activity->created_at->format('M d, Y') }}
                                                </small>
                                                <small class="text-primary">
                                                    {{ $activity->created_at->format('H:i:s') }}
                                                </small>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                @if($activity->causer)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            <small class="text-white fw-bold">
                                                                {{ strtoupper(substr($activity->causer->name, 0, 1)) }}
                                                            </small>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">{{ $activity->causer->name }}</div>
                                                            <small class="text-muted">{{ $activity->causer->email }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                @php
                                                    $eventColors = [
                                                        'created' => 'bg-success',
                                                        'updated' => 'bg-warning',
                                                        'deleted' => 'bg-danger',
                                                        'login'   => 'bg-info',
                                                        'logout'  => 'bg-secondary',
                                                    ];
                                                    $color = $eventColors[$activity->event] ?? 'bg-secondary';
                                                @endphp
                                                <span class="badge {{ $color }}">{{ ucfirst($activity->event) }}</span>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $modelIcons = [
                                                            'user'    => 'fas fa-user',
                                                            'plate'   => 'fas fa-car',
                                                            'rate'    => 'fas fa-dollar-sign',
                                                            'session' => 'fas fa-clock',
                                                            'ticket'  => 'fas fa-ticket-alt',
                                                            'auth'    => 'fas fa-sign-in-alt'
                                                        ];
                                                        $icon = $modelIcons[strtolower(class_basename($activity->subject_type))] ?? 'fas fa-cube';
                                                    @endphp
                                                    <i class="{{ $icon }} me-2"></i>
                                                    <span class="text-capitalize">{{ class_basename($activity->subject_type) }}</span>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                @if(isset($activity->properties['ip']))
                                                    <span class="badge bg-light text-dark">{{ $activity->properties['ip'] }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                @if(isset($activity->properties['location']))
                                                    @php $location = $activity->properties['location']; @endphp
                                                    <div class="text-nowrap">
                                                        <small class="d-block">
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            {{ $location['city'] ?? 'Unknown' }}, {{ $location['country_code'] ?? 'XX' }}
                                                        </small>
                                                        @if(!empty($location['country']) && $location['country'] !== 'Unknown')
                                                            <small class="text-muted">{{ $location['country'] }}</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">{{ $activity->description }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                                @if($activity->properties && count($activity->properties) > 0)
                                                    <button class="btn btn-sm btn-outline-info"
                                                            type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#details-{{ $activity->id }}"
                                                            aria-expanded="false">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </button>

                                                    <div class="collapse mt-2" id="details-{{ $activity->id }}">
                                                        <div class="card card-body bg-light">
                                                            @if(isset($activity->properties['old']) && isset($activity->properties['attributes']))
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <h6 class="text-danger">Old Values:</h6>
                                                                        <pre class="small">{{ json_encode($activity->properties['old'], JSON_PRETTY_PRINT) }}</pre>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <h6 class="text-success">New Values:</h6>
                                                                        <pre class="small">{{ json_encode($activity->properties['attributes'], JSON_PRETTY_PRINT) }}</pre>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <pre class="small">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Activity Logs Found</h5>
                            <p class="text-muted">There are no activity logs matching your current filters.</p>
                        </div>
                    @endif
                </div>

                <!-- Pagination -->
                <div class="bg-white dark:bg-gray-800 px-4 py-3 sm:px-6">
                    {{ $activities->links() }}
                </div>
            </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}

.activity-details pre {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 0.5rem;
    font-size: 0.875rem;
    max-height: 200px;
    overflow-y: auto;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.card-tools .text-muted {
    font-size: 0.875rem;
}
</style>
@endpush
