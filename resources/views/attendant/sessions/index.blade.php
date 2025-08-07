@extends('layouts.attendant')

@section('title', 'Parking Sessions')
@section('subtitle', 'Manage and monitor parking sessions')

@section('content')
<div class="space-y-6">
    <!-- Action Bar -->
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl px-4 py-2 shadow-lg">
                <span class="text-sm text-slate-600">Total Sessions: <span class="font-semibold text-slate-800">{{ $sessions->count() }}</span></span>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-xl px-4 py-2 shadow-lg">
                <span class="text-sm text-slate-600">Active: <span class="font-semibold text-cyan-600">{{ $sessions->filter(fn($s) => $s->isActive())->count() }}</span></span>
            </div>
        </div>
        <a href="{{ route('attendant.sessions.create') }}" class="bg-gradient-to-r from-cyan-600 to-blue-600 text-white px-6 py-3 rounded-xl hover:from-cyan-700 hover:to-blue-700 transition-all duration-200 font-medium shadow-lg flex items-center space-x-2">
            <i class="ri-add-line"></i>
            <span>Start New Session</span>
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-cyan-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-cyan-600 text-sm font-medium">Active Sessions</p>
                    <p class="text-3xl font-bold text-cyan-900">{{ $sessions->where('end_time', null)->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-cyan-500 rounded-xl flex items-center justify-center">
                    <i class="ri-time-line text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-600 text-sm font-medium">Completed Today</p>
                    <p class="text-3xl font-bold text-green-900">{{ $sessions->whereNotNull('end_time')->where('created_at', '>=', today())->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                    <i class="ri-check-circle-line text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-600 text-sm font-medium">Today's Revenue</p>
                    <p class="text-3xl font-bold text-blue-900">₱{{ number_format($sessions->where('created_at', '>=', today())->sum('amount_paid'), 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                    <i class="ri-money-dollar-circle-line text-2xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-orange-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-600 text-sm font-medium">Printed Tickets</p>
                    <p class="text-3xl font-bold text-orange-900">{{ $sessions->where('printed', true)->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center">
                    <i class="ri-printer-line text-2xl text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions Table with Tabs -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden">
        <!-- Tab Navigation -->
        <div class="border-b border-slate-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button class="tab-button active border-b-2 border-cyan-500 py-4 px-1 text-sm font-medium text-cyan-600" data-tab="all">
                    <i class="ri-list-check-3 mr-2"></i>All Sessions
                </button>
                <button class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300" data-tab="active">
                    <i class="ri-time-line mr-2"></i>Active ({{ $sessions->where('end_time', null)->count() }})
                </button>
                <button class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-slate-500 hover:text-slate-700 hover:border-slate-300" data-tab="completed">
                    <i class="ri-check-circle-line mr-2"></i>Completed
                </button>
            </nav>
        </div>

        <!-- Tab Contents -->
        <div class="tab-content active" id="all-content">
            @include('attendant.sessions.partials.sessions-table', ['sessions' => $sessions])
        </div>
        
        <div class="tab-content hidden" id="active-content">
            @include('attendant.sessions.partials.sessions-table', ['sessions' => $sessions->whereNull('end_time')])
        </div>
        
        <div class="tab-content hidden" id="completed-content">
            @include('attendant.sessions.partials.sessions-table', ['sessions' => $sessions->whereNotNull('end_time')])
        </div>
    </div>
</div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('{{ session('success') }}', 'success');
    });
</script>
@endif

<script>
// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.getAttribute('data-tab');
            
            // Remove active classes
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-cyan-500', 'text-cyan-600');
                btn.classList.add('border-transparent', 'text-slate-500');
            });
            
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            
            // Add active classes with cyan color for attendant
            button.classList.add('active', 'border-cyan-500', 'text-cyan-600');
            button.classList.remove('border-transparent', 'text-slate-500');
            
            const targetContent = document.getElementById(targetTab + '-content');
            if (targetContent) {
                targetContent.classList.remove('hidden');
                targetContent.classList.add('active');
            }
        });
    });

    // Auto-refresh active sessions every 30 seconds
    setInterval(function() {
        const activeTab = document.querySelector('.tab-button.active');
        if (activeTab && activeTab.getAttribute('data-tab') === 'active') {
            location.reload();
        }
    }, 30000);
});

// Confirm delete function
function confirmDelete(sessionId, plateNumber) {
    if (confirm(`Are you sure you want to delete the parking session for plate number "${plateNumber}"? This action cannot be undone.`)) {
        document.getElementById('delete-form-' + sessionId).submit();
    }
}
</script>
@endsection
