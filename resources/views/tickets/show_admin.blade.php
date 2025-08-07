@extends('layouts.admin')

@section('title', 'Ticket Details')
@section('subtitle', 'Detailed view of ticket information')

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
                        <h2 class="text-xl font-bold text-white">Ticket Details</h2>
                        <p class="text-blue-100 text-sm">Detailed information about the parking ticket</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Details -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div class="overflow-hidden rounded-lg bg-blue-50 p-4">
                    <dt class="text-sm font-medium text-slate-500">Ticket Number</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $ticket->ticket_number }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-blue-50 p-4">
                    <dt class="text-sm font-medium text-slate-500">Plate Number</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $ticket->plate_number }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-blue-50 p-4">
                    <dt class="text-sm font-medium text-slate-500">Time In</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $ticket->formatted_time_in }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-blue-50 p-4">
                    <dt class="text-sm font-medium text-slate-500">Time Out</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $ticket->formatted_time_out }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-blue-50 p-4">
                    <dt class="text-sm font-medium text-slate-500">Rate</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $ticket->formatted_rate }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-blue-50 p-4">
                    <dt class="text-sm font-medium text-slate-500">Parking Slot</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $ticket->parking_slot ?: 'N/A' }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-blue-50 p-4">
                    <dt class="text-sm font-medium text-slate-500">Duration</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $ticket->duration }}</dd>
                </div>
                <div class="overflow-hidden rounded-lg bg-blue-50 p-4">
                    <dt class="text-sm font-medium text-slate-500">Printed</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $ticket->is_printed ? 'Yes' : 'No' }}</dd>
                </div>
                @if($ticket->notes)
                <div class="overflow-hidden rounded-lg bg-blue-50 p-4 md:col-span-2">
                    <dt class="text-sm font-medium text-slate-500">Notes</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $ticket->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end mt-4">
        <a class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-purple-500 focus:outline-none focus:border-purple-700 focus:ring focus:ring-purple-200 active:bg-purple-600 transition" href="{{ route('admin.tickets.print', $ticket) }}" target="_blank">Print Ticket</a>
    </div>
</div>
@endsection

