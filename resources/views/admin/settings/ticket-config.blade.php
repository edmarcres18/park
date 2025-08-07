@extends('layouts.admin')

@section('title', 'Ticket Template Configuration')
@section('subtitle', 'Configure ticket logo and location address')

@section('content')
<div class="max-w-xl mx-auto mt-8">
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('admin.ticket-config.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 bg-white p-6 rounded-xl shadow border border-slate-200">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Current Logo</label>
            @if($config['logo'])
                <img src="{{ $config['logo'] }}" alt="Ticket Logo" class="h-24 mb-2 border rounded">
            @else
                <div class="text-slate-400 italic">No logo uploaded.</div>
            @endif
        </div>
        <div>
            <label for="logo" class="block text-sm font-medium text-slate-700 mb-2">Upload New Logo</label>
            <input type="file" name="logo" id="logo" accept="image/*" class="block w-full text-sm text-slate-700 border border-slate-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
            <p class="text-xs text-slate-500 mt-1">Max size: 2MB. Allowed: jpeg, png, jpg, gif, svg.</p>
        </div>
        <div>
            <label for="location_address" class="block text-sm font-medium text-slate-700 mb-2">Location Address</label>
            <input type="text" name="location_address" id="location_address" value="{{ old('location_address', $config['location_address']) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" maxlength="255" />
        </div>
        <div class="flex items-center justify-end space-x-4 pt-4 border-t border-slate-200">
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Save Configuration</button>
        </div>
    </form>
</div>
@endsection
