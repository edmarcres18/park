<div class="overflow-x-auto">
    @if($sessions->count() > 0)
        <table class="w-full">
            <thead class="bg-gradient-to-r from-slate-50 to-slate-100">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Plate Number</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Vehicle Type</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Branch</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Start Time</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">End Time</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Duration</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                @foreach($sessions as $session)
                <tr class="hover:bg-slate-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="ri-car-line text-white"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-slate-900">{{ $session->plate_number }}</div>
                                <div class="text-sm text-slate-500">{{ $session->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                @switch(strtolower($session->plate->vehicle_type))
                                    @case('car')
                                        <i class="ri-car-line text-white text-sm"></i>
                                        @break
                                    @case('motorcycle')
                                        <i class="ri-motorbike-line text-white text-sm"></i>
                                        @break
                                    @case('suv')
                                        <i class="ri-car-line text-white text-sm"></i>
                                        @break
                                    @case('van')
                                        <i class="ri-truck-line text-white text-sm"></i>
                                        @break
                                    @case('truck')
                                        <i class="ri-truck-line text-white text-sm"></i>
                                        @break
                                    @case('bus')
                                        <i class="ri-bus-line text-white text-sm"></i>
                                        @break
                                    @default
                                        <i class="ri-car-line text-white text-sm"></i>
                                @endswitch
                            </div>
                            <div>
                                <div class="text-sm text-slate-900">{{ $session->plate->vehicle_type ?: 'N/A' }}</div>
                                <div class="text-sm text-slate-500">{{ $session->plate->owner_name ?: 'No owner' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($session->branch)
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="ri-building-line text-white text-xs"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-slate-900">{{ $session->branch->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $session->branch->code }}</div>
                                </div>
                            </div>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                <i class="ri-building-line mr-1"></i>
                                Not Assigned
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-slate-900">{{ $session->start_time->format('M d, Y') }}</div>
                        <div class="text-sm text-slate-500">{{ $session->start_time->format('h:i A') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($session->end_time)
                            <div class="text-sm text-slate-900">{{ $session->end_time->format('M d, Y') }}</div>
                            <div class="text-sm text-slate-500">{{ $session->end_time->format('h:i A') }}</div>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($session->isActive())
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                <div class="w-2 h-2 bg-slate-400 rounded-full mr-2"></div>
                                Completed
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-slate-900">{{ $session->formatted_duration }}</div>
                        @if($session->isActive())
                            <div class="text-xs text-blue-600">{{ $session->getCurrentDurationMinutes() }} min so far</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-slate-900">{{ $session->formatted_amount }}</div>
                        @if($session->isActive())
                            <div class="text-xs text-slate-500">Est: ₱{{ number_format($session->getEstimatedCurrentFee(), 2) }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        @if($session->isActive())
                            <a href="{{ route('admin.sessions.edit', $session) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-lg transition-colors duration-200 inline-flex items-center space-x-1">
                                <i class="ri-stop-circle-line text-sm"></i>
                                <span>End</span>
                            </a>
                        @endif
                        @if(Auth::user()->hasRole('admin'))
                            <form action="{{ route('admin.sessions.destroy', $session) }}" method="POST" class="inline-block" id="delete-form-{{ $session->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete({{ $session->id }}, '{{ $session->plate_number }}')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg transition-colors duration-200 inline-flex items-center space-x-1">
                                    <i class="ri-delete-bin-line text-sm"></i>
                                    <span>Delete</span>
                                </button>
                            </form>
                        @else
                            <span class="bg-gray-400 text-white px-3 py-1 rounded-lg cursor-not-allowed inline-flex items-center space-x-1 opacity-60">
                                <i class="ri-lock-line text-sm"></i>
                                <span>Admin Only</span>
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="flex flex-col items-center py-12">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                <i class="ri-parking-line text-2xl text-slate-400"></i>
            </div>
            <h3 class="text-lg font-medium text-slate-900 mb-2">No parking sessions found</h3>
            <p class="text-slate-500 mb-4">Start your first parking session to see it listed here.</p>
            <a href="{{ route('admin.sessions.create') }}" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium">
                Start New Session
            </a>
        </div>
    @endif
</div>
