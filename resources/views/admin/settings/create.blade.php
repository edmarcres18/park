@extends('layouts.admin')

@section('title', 'Create Setting')
@section('subtitle', 'Add a new site setting')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="ri-settings-3-line text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Create Setting</h2>
                        <p class="text-blue-100 text-sm">Add a new site configuration setting</p>
                    </div>
                </div>
                <a href="{{ route('admin.settings.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="ri-arrow-left-line mr-2"></i>Back to Settings
                </a>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-8 sm:px-10">
            <form action="{{ route('admin.settings.store') }}" method="POST" class="space-y-8" enctype="multipart/form-data">
                @csrf
                <!-- Setting Key -->
                <div>
                    <label for="key" class="block text-sm font-medium text-slate-700 mb-2">Setting Key</label>
                    <input type="text" name="key" id="key" value="{{ old('key') }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., app_name, brand_logo" required>
                    @error('key')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Setting Value or Logo Upload -->
                <div id="value-field">
                    <label for="value" class="block text-sm font-medium text-slate-700 mb-2">Setting Value</label>
                    <textarea name="value" id="value" rows="3"
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Enter the setting value">{{ old('value') }}</textarea>
                    @error('value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div id="logo-upload-field" style="display:none;">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Brand Logo</label>
                    <input type="file" name="logo_file" accept="image/*" class="block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-slate-500 mt-1">Upload a logo (JPG, PNG, SVG, WEBP, max 2MB). Recommended: square, transparent background.</p>
                    @error('logo_file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Setting Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-slate-700 mb-2">Setting Type</label>
                    <select name="type" id="type"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select a type</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Setting Group -->
                <div>
                    <label for="group" class="block text-sm font-medium text-slate-700 mb-2">Setting Group</label>
                    <select name="group" id="group"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Select a group</option>
                        @foreach($groups as $group)
                            <option value="{{ $group }}" {{ old('group') == $group ? 'selected' : '' }}>
                                {{ ucfirst($group) }}
                            </option>
                        @endforeach
                    </select>
                    @error('group')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="2"
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Optional description of this setting">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Is Public -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_public" id="is_public" value="1"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded"
                           {{ old('is_public') ? 'checked' : '' }}>
                    <label for="is_public" class="ml-2 block text-sm text-slate-700">
                        Make this setting publicly accessible
                    </label>
                </div>
                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-slate-200">
                    <a href="{{ route('admin.settings.index') }}"
                       class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Setting
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    // Show file input if key is brand_logo
    document.addEventListener('DOMContentLoaded', function() {
        const keyInput = document.getElementById('key');
        const valueField = document.getElementById('value-field');
        const logoField = document.getElementById('logo-upload-field');
        function toggleLogoField() {
            if (keyInput.value.trim() === 'brand_logo') {
                valueField.style.display = 'none';
                logoField.style.display = 'block';
            } else {
                valueField.style.display = 'block';
                logoField.style.display = 'none';
            }
        }
        keyInput.addEventListener('input', toggleLogoField);
        toggleLogoField();
    });
</script>
@endsection

