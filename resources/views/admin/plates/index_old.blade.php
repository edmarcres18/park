@extends('layouts.admin')

@section('title', 'Plates')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Plates</h1>
        <a href="{{ route('admin.plates.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Add Plate</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Plate List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Number</th>
                            <th>Owner Name</th>
                            <th>Vehicle Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($plates as $plate)
                            <tr>
                                <td>{{ $plate->id }}</td>
                                <td>{{ $plate->number }}</td>
                                <td>{{ $plate->owner_name }}</td>
                                <td>{{ $plate->vehicle_type }}</td>
                                <td>
                                    <a href="{{ route('admin.plates.edit', $plate->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('admin.plates.destroy', $plate->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $plates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

