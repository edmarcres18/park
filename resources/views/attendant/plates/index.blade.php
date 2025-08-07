@extends('layouts.attendant')

@section('title', 'Plates')
@section('subtitle', 'View and manage vehicle plates')

@section('content')
<div class="bg-white rounded-2xl p-6 shadow-lg">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-slate-800">Plates Management</h3>
        <a href="{{ route('attendant.plates.create') }}" class="bg-gradient-to-r from-teal-500 to-blue-600 text-white py-2 px-4 rounded-xl shadow-lg hover:from-teal-600 hover:to-blue-700 transition-all duration-200">
            <i class="ri-add-circle-line mr-2"></i>Add New Plate
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
            <div class="flex items-center">
                <i class="ri-check-circle-line text-green-600 text-xl mr-3"></i>
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Owner Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Vehicle Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                @forelse ($plates as $plate)
                    <tr class="hover:bg-slate-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $plate->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <i class="ri-hashtag mr-1"></i>{{ $plate->number }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                            <i class="ri-user-line mr-2 text-slate-400"></i>{{ $plate->owner_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($plate->vehicle_type == 'Car') bg-green-100 text-green-800
                                @elseif($plate->vehicle_type == 'Motorcycle') bg-yellow-100 text-yellow-800
                                @elseif($plate->vehicle_type == 'Truck') bg-red-100 text-red-800
                                @elseif($plate->vehicle_type == 'Van') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                <i class="ri-car-line mr-1"></i>{{ $plate->vehicle_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <a href="{{ route('attendant.plates.edit', $plate->id) }}"
                               class="inline-flex items-center bg-gradient-to-r from-teal-500 to-blue-600 text-white py-2 px-4 rounded-xl shadow-lg hover:from-teal-600 hover:to-blue-700 transition-all duration-200">
                                <i class="ri-edit-line mr-1"></i>Edit
                            </a>
                            @if(Auth::user()->hasRole('admin'))
                                <form action="{{ route('attendant.plates.destroy', $plate->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this plate?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center bg-red-500 text-white py-2 px-4 rounded-xl shadow-lg hover:bg-red-600 transition duration-200">
                                        <i class="ri-delete-bin-line mr-1"></i>Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="ri-inbox-line text-6xl text-slate-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-slate-900 mb-2">No plates found</h3>
                                <p class="text-slate-500 mb-4">Get started by adding your first vehicle plate.</p>
                                <a href="{{ route('attendant.plates.create') }}"
                                   class="bg-gradient-to-r from-teal-500 to-blue-600 text-white py-2 px-4 rounded-xl shadow-lg hover:from-teal-600 hover:to-blue-700 transition-all duration-200">
                                    <i class="ri-add-circle-line mr-2"></i>Add First Plate
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($plates->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $plates->links() }}
        </div>
    @endif
</div>
@endsection
