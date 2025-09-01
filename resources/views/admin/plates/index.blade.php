@extends('layouts.admin')

@section('title', 'Plates Management')
@section('subtitle', 'Manage and view all plates in the system')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Plates Management</h2>
            <p class="text-slate-600 mt-1">Manage all registered plates in the system</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <div class="relative">
                <input type="text" id="search-plates" placeholder="Search plates..."
                       class="pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <i class="ri-search-line absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
            </div>
            <a href="{{ route('admin.plates.create') }}"
               class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-4 py-2 rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-200 flex items-center space-x-2">
                <i class="ri-add-line"></i>
                <span>Add Plate</span>
            </a>
        </div>
    </div>

    <!-- Toast notifications are now handled by the admin layout -->

    <!-- Plates Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">All Plates</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Number</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Owner Name</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Vehicle Type</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse ($plates as $plate)
                        <tr class="hover:bg-slate-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="ri-car-line text-blue-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-slate-900">{{ $plate->number }}</div>
                                        <div class="text-sm text-slate-500">ID: {{ $plate->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">{{ $plate->owner_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    <i class="ri-car-line mr-1"></i>
                                    {{ $plate->vehicle_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($plate->branch)
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                                            <i class="ri-building-line text-white text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-slate-900">{{ $plate->branch->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $plate->branch->code }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                        <i class="ri-building-line mr-1"></i>
                                        Not Assigned
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ $plate->created_at ? $plate->created_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.plates.edit', $plate) }}"
                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors duration-200">
                                        <i class="ri-edit-line mr-1"></i>
                                        Edit
                                    </a>
                                    @if(Auth::user()->hasRole('admin'))
                                        <button onclick="deletePlate({{ $plate->id }}, '{{ $plate->number }}')"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-red-700 bg-red-100 hover:bg-red-200 transition-colors duration-200">
                                            <i class="ri-delete-bin-line mr-1"></i>
                                            Delete
                                        </button>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">
                                            <i class="ri-lock-line mr-1"></i>
                                            Admin Only
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center space-y-4">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center">
                                        <i class="ri-car-line text-2xl text-slate-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-slate-500 text-sm">No plates found</p>
                                        <p class="text-slate-400 text-xs mt-1">Plates will appear here once added</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center">
        {{ $plates->links() }}
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="ri-error-warning-line text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Confirm Deletion</h3>
                    <p class="text-slate-600 text-sm">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-slate-700 mb-6">
                Are you sure you want to permanently delete plate <span id="delete-plate-number" class="font-semibold"></span>?
                This will remove all associated data.
            </p>
            <div class="flex items-center space-x-3">
                <button id="confirm-delete" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium">
                    Yes, Delete Plate
                </button>
                <button id="cancel-delete" class="flex-1 bg-slate-200 text-slate-700 py-2 px-4 rounded-lg hover:bg-slate-300 transition-colors duration-200 font-medium">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('search-plates').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const number = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        const owner = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const vehicleType = row.querySelector('td:nth-child(3)').textContent.toLowerCase();

        if (number.includes(searchTerm) || owner.includes(searchTerm) || vehicleType.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

function deletePlate(plateId, plateNumber) {
    document.getElementById('delete-plate-number').textContent = plateNumber;
    document.getElementById('delete-modal').classList.remove('hidden');

    document.getElementById('confirm-delete').onclick = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/plates/${plateId}`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    };
}

document.getElementById('cancel-delete').addEventListener('click', function() {
    document.getElementById('delete-modal').classList.add('hidden');
});

// Close modal when clicking outside
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});
</script>
@endsection
