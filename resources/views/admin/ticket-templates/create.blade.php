@extends('layouts.admin')

@section('title', 'Create Template')
@section('subtitle', 'Create a new ticket template')

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
                        <h2 class="text-xl font-bold text-white">Create Template</h2>
                        <p class="text-blue-100 text-sm">Create a new ticket printing template</p>
                    </div>
                </div>
                <a href="{{ route('admin.ticket-templates.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="ri-arrow-left-line mr-2"></i>Back to Templates
                </a>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-8 sm:px-10">
            <form action="{{ route('admin.ticket-templates.store') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Template Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Template Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., Standard Ticket, Compact Ticket" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="2"
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Optional description of this template">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- HTML Template -->
                <div>
                    <label for="html_template" class="block text-sm font-medium text-slate-700 mb-2">HTML Template</label>
                    <textarea name="html_template" id="html_template" rows="15"
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                              placeholder="Enter HTML template with variables like @{{ticket_number}}, @{{plate_number}}, etc." required>{{ old('html_template') }}</textarea>
                    @error('html_template')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-slate-500">Use variables like @{{ticket_number}}, @{{plate_number}}, @{{time_in}}, @{{rate}}, etc.</p>
                </div>

                <!-- CSS Styles -->
                <div>
                    <label for="css_styles" class="block text-sm font-medium text-slate-700 mb-2">CSS Styles</label>
                    <textarea name="css_styles" id="css_styles" rows="8"
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                              placeholder="Enter custom CSS styles for the template">{{ old('css_styles') }}</textarea>
                    @error('css_styles')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Template Variables -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Template Variables</label>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($variables as $key => $label)
                        <div class="flex items-center">
                            <input type="checkbox" name="template_variables[]" id="var_{{ $key }}" value="{{ $key }}"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded"
                                   {{ in_array($key, old('template_variables', [])) ? 'checked' : '' }}>
                            <label for="var_{{ $key }}" class="ml-2 block text-sm text-slate-700">
                                {{ $label }} (<code class="text-xs">@{{{{ $key }}}}</code>)
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Settings -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active" class="ml-2 block text-sm text-slate-700">
                            Active
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_default" id="is_default" value="1"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded"
                               {{ old('is_default') ? 'checked' : '' }}>
                        <label for="is_default" class="ml-2 block text-sm text-slate-700">
                            Set as Default
                        </label>
                    </div>
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-slate-200">
                    <a href="{{ route('admin.ticket-templates.index') }}"
                       class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

