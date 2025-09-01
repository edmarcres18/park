@extends('layouts.admin')

@section('title', 'Ticket Management')
@section('subtitle', 'Manage and view all parking tickets')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="ri-ticket-2-line text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Ticket Management</h2>
                        <p class="text-blue-100 text-sm">View and manage all parking tickets</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm font-medium">
                        {{ $tickets->total() }} Total Tickets
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form method="GET" action="{{ route('admin.tickets.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Search by Plate Number</label>
                    <input type="text" name="plate_number" value="{{ request('plate_number') }}" placeholder="Enter plate number..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Print Status</label>
                    <select name="print_status" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Tickets</option>
                        <option value="printed" {{ request('print_status') == 'printed' ? 'selected' : '' }}>Printed</option>
                        <option value="unprinted" {{ request('print_status') == 'unprinted' ? 'selected' : '' }}>Unprinted</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium">
                        <i class="ri-search-line mr-2"></i>Apply Filters
                    </button>
                </div>
            </div>
            <!-- Clear Filters Button -->
            @if(request()->hasAny(['plate_number', 'print_status', 'date_from', 'date_to']))
            <div class="mt-4 flex justify-end">
                <a href="{{ route('admin.tickets.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors duration-200 text-sm font-medium">
                    <i class="ri-close-line mr-2"></i>Clear Filters
                </a>
            </div>
            @endif
        </form>
        
        <!-- Filter Status Indicators -->
        @if(request()->hasAny(['plate_number', 'print_status', 'date_from', 'date_to']))
        <div class="mt-4 flex flex-wrap gap-2">
            <span class="text-sm text-slate-600 font-medium">Active Filters:</span>
            @if(request('plate_number'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Plate: {{ request('plate_number') }}
                </span>
            @endif
            @if(request('print_status'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Status: {{ ucfirst(request('print_status')) }}
                </span>
            @endif
            @if(request('date_from'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    From: {{ request('date_from') }}
                </span>
            @endif
            @if(request('date_to'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    To: {{ request('date_to') }}
                </span>
            @endif
        </div>
        @endif
    </div>

    <!-- Tickets Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Ticket Number</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Plate Number</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider hidden md:table-cell">Time In</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider hidden lg:table-cell">Time Out</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Rate</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse ($tickets as $key => $ticket)
                    <tr class="hover:bg-slate-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                            {{ $tickets->firstItem() + $key }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center mr-3">
                                    <i class="ri-ticket-line text-white text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-slate-900">{{ $ticket->ticket_number }}</div>
                                    <div class="text-xs text-slate-500">{{ $ticket->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                <i class="ri-car-line mr-1"></i>
                                {{ $ticket->plate_number }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($ticket->branch)
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                                        <i class="ri-building-line text-white text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-slate-900">{{ $ticket->branch->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $ticket->branch->code }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                    <i class="ri-building-line mr-1"></i>
                                    Not Assigned
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 hidden md:table-cell">
                            <div class="flex items-center">
                                <i class="ri-time-line text-green-500 mr-2"></i>
                                {{ $ticket->formatted_time_in }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 hidden lg:table-cell">
                            @if($ticket->time_out)
                                <div class="flex items-center">
                                    <i class="ri-time-line text-red-500 mr-2"></i>
                                    {{ $ticket->formatted_time_out }}
                                </div>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Active
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold text-green-600">
                                {{ $ticket->formatted_rate }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" 
                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-blue-600 bg-blue-100 hover:bg-blue-200 transition-colors duration-200">
                                    <i class="ri-eye-line mr-1"></i>
                                    View
                                </a>
                                <a href="{{ route('admin.tickets.print', $ticket) }}" target="_blank"
                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-purple-600 bg-purple-100 hover:bg-purple-200 transition-colors duration-200">
                                    <i class="ri-printer-line mr-1"></i>
                                    Print
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="ri-ticket-line text-slate-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-slate-900 mb-2">No Tickets Found</h3>
                                <p class="text-slate-500">There are no tickets to display at the moment.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($tickets->hasPages())
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

