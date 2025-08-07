@extends('layouts.admin')

@section('title', 'Sales Reports')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Sales Reports</h1>
                    <p class="mb-0 text-muted">View and export parking sales data</p>
                </div>
                <div>
                    <a href="{{ route('reports.sales.export', request()->query()) }}" 
                       class="btn btn-success">
                        <i class="fas fa-download me-2"></i>Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Filters
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.sales') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" 
                           class="form-control @error('start_date') is-invalid @enderror" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ $startDate }}">
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" 
                           class="form-control @error('end_date') is-invalid @enderror" 
                           id="end_date" 
                           name="end_date" 
                           value="{{ $endDate }}">
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="attendant_id" class="form-label">Attendant</label>
                    <select class="form-select @error('attendant_id') is-invalid @enderror" 
                            id="attendant_id" 
                            name="attendant_id">
                        <option value="">All Attendants</option>
                        @foreach($attendants as $attendant)
                            <option value="{{ $attendant->id }}" 
                                    {{ $attendantId == $attendant->id ? 'selected' : '' }}>
                                {{ $attendant->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('attendant_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Sessions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalSessions) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-car fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Earnings
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($totalEarnings, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Daily Sales Breakdown -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-day me-2"></i>Daily Sales Breakdown
                    </h6>
                </div>
                <div class="card-body">
                    @if($dailySales->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Sessions</th>
                                        <th>Earnings</th>
                                        <th>Avg per Session</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dailySales as $day)
                                        <tr>
                                            <td>
                                                <strong>{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($day->date)->format('l') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $day->session_count }}</span>
                                            </td>
                                            <td>
                                                <strong class="text-success">${{ number_format($day->total_earnings, 2) }}</strong>
                                            </td>
                                            <td>
                                                ${{ number_format($day->total_earnings / $day->session_count, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No sales data found for the selected period.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Earnings by Attendant -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users me-2"></i>Earnings by Attendant
                    </h6>
                </div>
                <div class="card-body">
                    @if($attendantEarnings->count() > 0)
                        @foreach($attendantEarnings as $attendant)
                            <div class="mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">{{ $attendant->attendant_name }}</h6>
                                    <span class="badge bg-success">${{ number_format($attendant->total_earnings, 2) }}</span>
                                </div>
                                <div class="small text-muted">
                                    <i class="fas fa-car me-1"></i>{{ $attendant->session_count }} sessions
                                </div>
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-success" 
                                         role="progressbar" 
                                         style="width: {{ $totalEarnings > 0 ? ($attendant->total_earnings / $totalEarnings) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-tie fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No attendant data found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Set max date for end_date when start_date changes
    document.getElementById('start_date').addEventListener('change', function() {
        const endDate = document.getElementById('end_date');
        endDate.min = this.value;
        
        // If end date is before start date, update it
        if (endDate.value && endDate.value < this.value) {
            endDate.value = this.value;
        }
    });

    // Set min date for start_date when end_date changes
    document.getElementById('end_date').addEventListener('change', function() {
        const startDate = document.getElementById('start_date');
        startDate.max = this.value;
        
        // If start date is after end date, update it
        if (startDate.value && startDate.value > this.value) {
            startDate.value = this.value;
        }
    });
</script>
@endpush
@endsection
