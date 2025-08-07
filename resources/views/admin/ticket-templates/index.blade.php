@extends('layouts.admin')

@section('title', 'Ticket Templates')
@section('subtitle', 'Manage ticket printing templates')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="ri-file-text-line text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Ticket Templates</h2>
                        <p class="text-blue-100 text-sm">Manage ticket printing templates and layouts</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.ticket-templates.create') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="ri-add-line mr-2"></i>Create Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-900">Available Templates</h3>
            <p class="text-sm text-slate-600">Manage and configure ticket printing templates</p>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse($templates as $template)
            <div class="px-6 py-4 hover:bg-slate-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3">
                            <h4 class="font-medium text-slate-900">{{ $template->name }}</h4>
                            @if($template->is_default)
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Default</span>
                            @endif
                            @if($template->is_active)
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Active</span>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Inactive</span>
                            @endif
                        </div>
                        @if($template->description)
                            <p class="text-sm text-slate-600 mt-1">{{ $template->description }}</p>
                        @endif
                        <div class="flex items-center space-x-4 mt-2">
                            <span class="text-xs text-slate-500">Slug: {{ $template->slug }}</span>
                            <span class="text-xs text-slate-500">Order: {{ $template->sort_order }}</span>
                            <span class="text-xs text-slate-500">Created: {{ $template->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.ticket-templates.preview', $template) }}"
                           class="text-blue-600 hover:text-blue-800" title="Preview">
                            <i class="ri-eye-line"></i>
                        </a>
                        <a href="{{ route('admin.ticket-templates.edit', $template) }}"
                           class="text-blue-600 hover:text-blue-800" title="Edit">
                            <i class="ri-edit-line"></i>
                        </a>
                        @if(!$template->is_default)
                            <form action="{{ route('admin.ticket-templates.set-default', $template) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800" title="Set as Default">
                                    <i class="ri-star-line"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.ticket-templates.duplicate', $template) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-orange-600 hover:text-orange-800" title="Duplicate">
                                    <i class="ri-file-copy-line"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.ticket-templates.destroy', $template) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this template?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ri-file-text-line text-2xl text-slate-400"></i>
                </div>
                <h3 class="text-lg font-medium text-slate-900 mb-2">No templates found</h3>
                <p class="text-slate-600 mb-4">Get started by creating your first ticket template.</p>
                <a href="{{ route('admin.ticket-templates.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="ri-add-line mr-2"></i>Create Template
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Template Variables Info -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Available Template Variables</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <h4 class="font-medium text-slate-700">Ticket Information</h4>
                <ul class="text-sm text-slate-600 space-y-1">
                    <li><code class="bg-slate-100 px-1 rounded">@{{ticket_number}}</code> - Ticket number</li>
                    <li><code class="bg-slate-100 px-1 rounded">@{{plate_number}}</code> - Plate number</li>
                    <li><code class="bg-slate-100 px-1 rounded">@{{time_in}}</code> - Time in</li>
                    <li><code class="bg-slate-100 px-1 rounded">@{{time_out}}</code> - Time out</li>
                    <li><code class="bg-slate-100 px-1 rounded">@{{duration}}</code> - Duration</li>
                </ul>
            </div>
            <div class="space-y-2">
                <h4 class="font-medium text-slate-700">Additional Information</h4>
                <ul class="text-sm text-slate-600 space-y-1">
                    <li><code class="bg-slate-100 px-1 rounded">@{{rate}}</code> - Rate amount</li>
                    <li><code class="bg-slate-100 px-1 rounded">@{{parking_slot}}</code> - Parking slot</li>
                    <li><code class="bg-slate-100 px-1 rounded">@{{location}}</code> - Location</li>
                    <li><code class="bg-slate-100 px-1 rounded">@{{attendant}}</code> - Attendant name</li>
                    <li><code class="bg-slate-100 px-1 rounded">@{{qr_code}}</code> - QR code data</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

