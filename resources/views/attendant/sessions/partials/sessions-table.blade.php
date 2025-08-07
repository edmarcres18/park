
<div class="overflow-x-auto">
    @if($sessions->count() > 0)
        <table class="w-full">
            <thead class="bg-gradient-to-r from-slate-50 to-slate-100">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Plate Number</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Customer</th>
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
                            <div class="w-10 h-10 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                                <i class="ri-car-line text-white"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-slate-900">{{ $session->plate_number }}</div>
                                <div class="text-sm text-slate-500">{{ $session->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-slate-900">{{ $session->customer_name ?: 'N/A' }}</div>
                        <div class="text-sm text-slate-500">{{ $session->customer_contact ?: 'No contact' }}</div>
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
                            <a href="{{ route('attendant.sessions.edit', $session) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-lg transition-colors duration-200 inline-flex items-center space-x-1">
                                <i class="ri-stop-circle-line text-sm"></i>
                                <span>End</span>
                            </a>
                        @endif
                        @if(Auth::user()->hasRole('admin'))
                            <form action="{{ route('attendant.sessions.destroy', $session) }}" method="POST" class="inline-block" id="delete-form-{{ $session->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete({{ $session->id }}, '{{ $session->plate_number }}')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg transition-colors duration-200 inline-flex items-center space-x-1">
                                    <i class="ri-delete-bin-line text-sm"></i>
                                    <span>Delete</span>
                                </button>
                            </form>
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
            <a href="{{ route('attendant.sessions.create') }}" class="bg-gradient-to-r from-cyan-600 to-blue-600 text-white px-4 py-2 rounded-lg hover:from-cyan-700 hover:to-blue-700 transition-all duration-200 font-medium">
                Start New Session
            </a>
        </div>
    @endif
</div>
