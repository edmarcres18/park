@extends('layouts.attendant')

@section('title', 'Ticket Management')
@section('subtitle', 'Monitor and manage parking tickets')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-cyan-600 to-blue-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="ri-ticket-2-line text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Parking Tickets</h2>
                        <p class="text-cyan-100 text-sm">View and manage parking tickets</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="bg-white/10 rounded-lg px-3 py-2">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                            <span class="text-white text-sm font-medium">{{ $tickets->total() }} Total</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form method="GET" action="{{ route('attendant.tickets.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Search by Plate Number</label>
                    <input type="text" name="plate_number" value="{{ request('plate_number') }}" placeholder="Enter plate number..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Print Status</label>
                    <select name="print_status" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                        <option value="">All Tickets</option>
                        <option value="printed" {{ request('print_status') == 'printed' ? 'selected' : '' }}>Printed</option>
                        <option value="unprinted" {{ request('print_status') == 'unprinted' ? 'selected' : '' }}>Unprinted</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-gradient-to-r from-cyan-600 to-blue-600 text-white px-4 py-2 rounded-lg hover:from-cyan-700 hover:to-blue-700 transition-all duration-200 font-medium">
                        <i class="ri-search-line mr-2"></i>Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if ($message = Session::get('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
            <div class="flex items-center">
                <i class="ri-check-circle-line text-green-500 mr-2"></i>
                <span>{{ $message }}</span>
            </div>
        </div>
    @endif

    <!-- Tickets Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Ticket Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Plate Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Time In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Time Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse ($tickets as $key => $ticket)
                    <tr class="hover:bg-slate-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                            {{ $tickets->firstItem() + $key }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-cyan-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="ri-ticket-line text-cyan-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-slate-900">{{ $ticket->ticket_number }}</div>
                                    <div class="text-xs text-slate-500">{{ $ticket->created_at->format('M j, Y') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $ticket->plate_number }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            {{ $ticket->formatted_time_in }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            {{ $ticket->formatted_time_out }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                            {{ $ticket->formatted_rate }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($ticket->is_printed)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="ri-check-line mr-1"></i>Printed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <i class="ri-time-line mr-1"></i>Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('attendant.tickets.show', $ticket) }}" 
                                   class="inline-flex items-center px-3 py-1.5 bg-cyan-100 text-cyan-700 rounded-lg hover:bg-cyan-200 transition-colors duration-150 text-xs font-medium">
                                    <i class="ri-eye-line mr-1"></i>View
                                </a>
                                <a href="{{ route('attendant.tickets.print', $ticket) }}" 
                                   target="_blank"
                                   class="inline-flex items-center px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors duration-150 text-xs font-medium">
                                    <i class="ri-printer-line mr-1"></i>Print
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="ri-ticket-line text-2xl text-slate-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-slate-900 mb-2">No tickets found</h3>
                                <p class="text-slate-500">There are no parking tickets to display at the moment.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($tickets->hasPages())
        <div class="bg-slate-50 px-6 py-3 border-t border-slate-200">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
</div>

<script>
// Real-time search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const plateSearch = document.getElementById('plate-search');
    const printStatus = document.getElementById('print-status');
    const applyFilters = document.getElementById('apply-filters');
    
    function applyFilter() {
        const params = new URLSearchParams();
        
        if (plateSearch.value) {
            params.append('plate_number', plateSearch.value);
        }
        
        if (printStatus.value) {
            params.append('print_status', printStatus.value);
        }
        
        window.location.href = '{{ route("attendant.tickets.index") }}?' + params.toString();
    }
    
    applyFilters.addEventListener('click', applyFilter);
    
    // Auto-filter on Enter key
    plateSearch.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilter();
        }
    });
    
    // Real-time refresh every 30 seconds
    setInterval(function() {
        // Only refresh if no filters are applied to avoid disruption
        if (!plateSearch.value && !printStatus.value) {
            location.reload();
        }
    }, 30000);
});
</script>
@endsection
