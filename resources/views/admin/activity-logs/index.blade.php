@extends('layouts.admin')

@section('content')
<div class="main-content">
    <div class="p-6 sm:p-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Activity Logs</h1>
            <p class="text-gray-600">Monitor all system activities and user actions</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="ri-filter-line mr-2"></i>
                    Filters
                </h2>
                <form method="GET" action="{{ route('admin.activity-logs.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                            <input type="date" id="date_from" name="date_from" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                   value="{{ request('date_from') }}">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                            <input type="date" id="date_to" name="date_to" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                   value="{{ request('date_to') }}">
                        </div>
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">User</label>
                            <select id="user_id" name="user_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">All Users</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="model" class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                            <select id="model" name="model" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">All Models</option>
                                <option value="user" {{ request('model') == 'user' ? 'selected' : '' }}>User</option>
                                <option value="plate" {{ request('model') == 'plate' ? 'selected' : '' }}>Plate</option>
                                <option value="rate" {{ request('model') == 'rate' ? 'selected' : '' }}>Rate</option>
                                <option value="session" {{ request('model') == 'session' ? 'selected' : '' }}>Session</option>
                                <option value="ticket" {{ request('model') == 'ticket' ? 'selected' : '' }}>Ticket</option>
                                <option value="authentication" {{ request('model') == 'authentication' ? 'selected' : '' }}>Authentication</option>
                            </select>
                        </div>
                        <div>
                            <label for="log_name" class="block text-sm font-medium text-gray-700 mb-2">Log Name</label>
                            <select id="log_name" name="log_name" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">All Log Names</option>
                                @foreach ($logNames as $logName)
                                    <option value="{{ $logName }}" {{ request('log_name') == $logName ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $logName)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="ri-search-line mr-2"></i>
                            Filter
                        </button>
                        <a href="{{ route('admin.activity-logs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <i class="ri-refresh-line mr-2"></i>
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Activity Logs Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="ri-history-line mr-2"></i>
                    Activity Logs ({{ $activities->total() }} total)
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">User</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Branch</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Action</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Model</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">IP</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activities as $activity)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-4 text-sm text-gray-900">
                                        {{ $activity->created_at->format('M d, Y H:i:s') }}
                                    </td>
                                    <td class="py-4 px-4 text-sm">
                                        @if($activity->causer)
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                                                    <i class="ri-user-line text-primary-600 text-sm"></i>
                                                </div>
                                                <span class="text-gray-900 font-medium">{{ $activity->causer->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-500">System</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-sm">
                                        @if($activity->causer && $activity->causer->branch)
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="ri-building-line text-white text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $activity->causer->branch->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $activity->causer->branch->code }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                                <i class="ri-building-line mr-1"></i>
                                                Not Assigned
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-sm text-gray-900">
                                        {{ $activity->description }}
                                    </td>
                                    <td class="py-4 px-4 text-sm">
                                        @if($activity->subject_type)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ class_basename($activity->subject_type) }}
                                            </span>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-sm text-gray-600">
                                        {{ $activity->properties['ip'] ?? '-' }}
                                    </td>
                                    <td class="py-4 px-4 text-sm text-gray-600">
                                        @if(isset($activity->properties['location']))
                                            {{ $activity->properties['location']['city'] ?? 'Unknown' }}, 
                                            {{ $activity->properties['location']['country'] ?? 'Unknown' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-500">
                                        <i class="ri-history-line text-4xl mb-4 text-gray-300"></i>
                                        <p class="text-lg font-medium mb-2">No activity logs found</p>
                                        <p class="text-sm">Try adjusting your filters or check back later.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($activities->hasPages())
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
