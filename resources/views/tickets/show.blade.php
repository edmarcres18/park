@if(auth()->user()->hasRole('admin'))
@extends('layouts.admin')
@else
@extends('layouts.attendant')
@endif

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-12 margin-tb">
                <div class="pull-left">
                    <h2>Ticket Details</h2>
                </div>
                <div class="pull-right">
                    @if(auth()->user()->hasRole('admin'))
                        <a class="btn btn-primary" href="{{ route('admin.tickets.index') }}">Back</a>
                    @else
                        <a class="btn btn-primary" href="{{ route('attendant.tickets.index') }}">Back</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Ticket Number:</strong>
                    {{ $ticket->ticket_number }}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Plate Number:</strong>
                    {{ $ticket->plate_number }}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Time In:</strong>
                    {{ $ticket->formatted_time_in }}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Time Out:</strong>
                    {{ $ticket->formatted_time_out }}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Rate:</strong>
                    {{ $ticket->formatted_rate }}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Parking Slot:</strong>
                    {{ $ticket->parking_slot ?: 'N/A' }}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Duration:</strong>
                    {{ $ticket->duration }}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Total Fee:</strong>
                    ₱{{ number_format($ticket->total_fee, 2) }}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Printed:</strong>
                    {{ $ticket->is_printed ? 'Yes' : 'No' }}
                </div>
            </div>
            @if($ticket->notes)
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Notes:</strong>
                    {{ $ticket->notes }}
                </div>
            </div>
            @endif
        </div>

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                @if(auth()->user()->hasRole('admin'))
                    <a class="btn btn-success" href="{{ route('admin.tickets.print', $ticket) }}" target="_blank">Print Ticket</a>
                @else
                    <a class="btn btn-success" href="{{ route('attendant.tickets.print', $ticket) }}" target="_blank">Print Ticket</a>
                @endif
            </div>
        </div>
    </div>
@endsection
