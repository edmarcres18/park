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
                    <h2>Tickets</h2>
                </div>
            </div>
        </div>

        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif

        <table class="table table-bordered">
            <tr>
                <th>No</th>
                <th>Ticket Number</th>
                <th>Plate Number</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Rate</th>
                <th width="280px">Action</th>
            </tr>
            @foreach ($tickets as $key => $ticket)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $ticket->ticket_number }}</td>
                <td>{{ $ticket->plate_number }}</td>
                <td>{{ $ticket->formatted_time_in }}</td>
                <td>{{ $ticket->formatted_time_out }}</td>
                <td>{{ $ticket->formatted_rate }}</td>
                <td>
                    @if(auth()->user()->hasRole('admin'))
                        <a class="btn btn-info btn-sm" href="{{ route('admin.tickets.show', $ticket) }}">Show</a>
                        <a class="btn btn-primary btn-sm" href="{{ route('admin.tickets.print', $ticket) }}" target="_blank">Print</a>
                    @else
                        <a class="btn btn-info btn-sm" href="{{ route('attendant.tickets.show', $ticket) }}">Show</a>
                        <a class="btn btn-primary btn-sm" href="{{ route('attendant.tickets.print', $ticket) }}" target="_blank">Print</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>

        {!! $tickets->links() !!}
    </div>
@endsection
