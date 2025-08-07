@extends('layouts.admin')

@section('title', 'Site Settings')
@section('subtitle', 'Manage application settings and configuration')

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
                        <h2 class="text-xl font-bold text-white">Site Settings</h2>
                        <p class="text-blue-100 text-sm">Manage application configuration and settings</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.settings.create') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="ri-add-line mr-2"></i>Add Setting
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Branding Section -->
    <div class="bg-white rounded-xl shadow-sm border-2 border-blue-300 overflow-hidden mb-8 relative">
        <!-- Always-visible Edit Buttons for Branding -->
        <div class="absolute right-6 top-6 flex flex-col md:flex-row gap-3 z-10">
            @php
                $appNameSetting = $settings->where('key', 'app_name')->first();
                $brandLogoSetting = $settings->where('key', 'brand_logo')->first();
            @endphp
            @if($appNameSetting)
                <a href="{{ route('admin.settings.edit', $appNameSetting) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl text-base font-bold shadow-lg transition-all flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-blue-400 border-2 border-blue-600 hover:border-blue-400 animate-pulse-on-hover"
                   title="Edit the application name" aria-label="Edit App Name">
                    <i class="ri-edit-line mr-2 text-lg"></i>Edit App Name
                </a>
            @endif
            @if($brandLogoSetting)
                <a href="{{ route('admin.settings.edit', $brandLogoSetting) }}"
                   class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-xl text-base font-bold shadow-lg transition-all flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-purple-400 border-2 border-purple-600 hover:border-purple-400 animate-pulse-on-hover"
                   title="Upload or change the brand logo" aria-label="Edit Brand Logo">
                    <i class="ri-image-edit-line mr-2 text-lg"></i>Edit Logo
                </a>
            @endif
        </div>
        <!-- End Edit Buttons -->
        <div class="flex flex-col md:flex-row items-center justify-between px-6 py-8 gap-8">
            <div class="flex items-center space-x-6">
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center overflow-hidden border-4 border-blue-100 mb-2">
                        @if(!empty($siteSettings->brand_logo))
                            <img src="{{ asset('storage/' . $siteSettings->brand_logo) }}" alt="Brand Logo" class="w-24 h-24 object-contain rounded-xl">
                        @else
                            <i class="ri-image-line text-5xl text-white"></i>
                        @endif
                    </div>
                    <span class="text-xs text-slate-500">Recommended: Square, transparent, max 2MB</span>
                    @if(empty($siteSettings->brand_logo))
                        <span class="text-xs text-red-500 mt-1">No logo set</span>
                    @endif
                </div>
                <div>
                    <div class="flex items-center space-x-2 mb-1">
                        <h2 class="text-2xl font-bold text-slate-900">{{ $siteSettings->app_name ?? config('app.name', 'ParkSmart') }}</h2>
                        <span class="inline-block align-middle" title="This name appears everywhere in the app">
                            <i class="ri-information-line text-blue-400"></i>
                        </span>
                    </div>
                    <p class="text-slate-500 text-sm">Current Application Name</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Groups -->
    @foreach($groups as $groupName => $groupSettings)
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-900 capitalize">{{ $groupName }} Settings</h3>
            <p class="text-sm text-slate-600">Manage {{ $groupName }} related configuration</p>
        </div>

        <div class="divide-y divide-slate-200">
            @foreach($groupSettings as $setting)
            <div class="px-6 py-4 hover:bg-slate-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3">
                            <h4 class="font-medium text-slate-900">{{ $setting->key }}</h4>
                            @if($setting->is_public)
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Public</span>
                            @endif
                        </div>
                        @if($setting->description)
                            <p class="text-sm text-slate-600 mt-1">{{ $setting->description }}</p>
                        @endif
                        <div class="flex items-center space-x-4 mt-2">
                            <span class="text-xs text-slate-500">Type: {{ $setting->type }}</span>
                            <span class="text-xs text-slate-500">Group: {{ $setting->group }}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="text-sm text-slate-700 max-w-xs truncate">
                            @if($setting->type === 'boolean')
                                <span class="px-2 py-1 rounded text-xs {{ $setting->value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $setting->value ? 'True' : 'False' }}
                                </span>
                            @elseif($setting->type === 'json')
                                <code class="text-xs bg-slate-100 px-2 py-1 rounded">{{ Str::limit($setting->value, 50) }}</code>
                            @else
                                {{ Str::limit($setting->value, 100) }}
                            @endif
                        </div>
                        <a href="{{ route('admin.settings.edit', $setting) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="ri-edit-line"></i>
                        </a>
                        <form action="{{ route('admin.settings.destroy', $setting) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this setting?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <!-- Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Cache Management</h3>
                <p class="text-sm text-slate-600">Clear settings cache to refresh configuration</p>
            </div>
            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="ri-refresh-line mr-2"></i>Clear Cache
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // This script is for the pulse animation on hover, which is handled by Tailwind classes
            // No specific JavaScript needed here for the animation itself,
            // but it's good practice to keep the script block.
        });
    </script>
@endsection

@section('styles')
    @parent
    <style>
        .animate-pulse-on-hover:hover {
            animation: pulse 0.5s;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59,130,246,0.5); }
            70% { box-shadow: 0 0 0 10px rgba(59,130,246,0); }
            100% { box-shadow: 0 0 0 0 rgba(59,130,246,0); }
        }
        @media (max-width: 768px) {
            .absolute.right-6.top-6 {
                position: static !important;
                margin-bottom: 1rem;
                flex-direction: row !important;
                justify-content: center;
            }
        }
    </style>
@endsection

