<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e40af">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>@yield('title', $siteSettings->app_name ?? config('app.name', 'ParkSmart')) - {{ $siteSettings->app_name ?? config('app.name', 'ParkSmart') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="{{ asset('js/geolocation.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui']
                    },
                    colors: {
                        'primary': {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e'
                        },
                        'sidebar': {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a'
                        }
                    },
                    animation: {
                        'slide-in': 'slide-in 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                        'slide-out': 'slide-out 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                        'fade-in': 'fade-in 0.2s ease-out',
                        'bounce-gentle': 'bounce-gentle 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55)',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite'
                    },
                    backdropBlur: {
                        'xs': '2px'
                    },
                    spacing: {
                        '18': '4.5rem',
                        '88': '22rem'
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes slide-in {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slide-out {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(-100%); opacity: 0; }
        }
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slide-in-right {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slide-out-right {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        @keyframes bounce-gentle {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes bounce-in {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        /* Enhanced Glass Effect */
        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .glass-dark {
            backdrop-filter: blur(16px) saturate(180%);
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .toast-enter {
            animation: bounce-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        .toast-exit {
            animation: slide-out-right 0.4s ease-in-out forwards;
        }
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: currentColor;
            opacity: 0.3;
            animation: progress-shrink 5s linear forwards;
        }
        @keyframes progress-shrink {
            from { width: 100%; }
            to { width: 0%; }
        }
        .toast-hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .toast-container {
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
        }
        .toast-container::-webkit-scrollbar {
            width: 4px;
        }
        .toast-container::-webkit-scrollbar-track {
            background: transparent;
        }
        .toast-container::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.5);
            border-radius: 2px;
        }
        .toast-container::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.7);
        }

        /* Enhanced Touch Targets */
        .touch-target {
            min-height: 44px;
            min-width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Focus Ring */
        .focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }
        
        /* Enhanced Tooltips */
        .tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-bottom: 0.75rem;
            padding: 0.5rem 0.75rem;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(8px);
            color: white;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: 0.75rem;
            opacity: 0;
            pointer-events: none;
            white-space: nowrap;
            z-index: 60;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(-50%) translateY(-4px) scale(0.95);
        }

        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid rgba(15, 23, 42, 0.95);
        }

        .group:hover .tooltip {
            opacity: 1;
            transform: translateX(-50%) translateY(0) scale(1);
        }
        
        /* Responsive Grid System */
        .responsive-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(280px, 100%), 1fr));
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .responsive-grid {
                gap: 1.5rem;
            }
        }
        
        /* Custom Scrollbars */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }
        
        /* Mobile Responsive Design */
        @media (max-width: 640px) {
            .glass-effect {
                backdrop-filter: blur(8px);
                background: rgba(255, 255, 255, 0.9);
            }
            
            .sidebar-mobile {
                width: 100vw !important;
            }
            
            .header-title {
                font-size: 1.25rem !important;
            }
            
            .header-subtitle {
                font-size: 0.75rem !important;
            }
        }
        
        @media (max-width: 768px) {
            .md\:hidden {
                display: none !important;
            }
            
            .main-content {
                padding: 1rem !important;
            }
            
            .overflow-auto::-webkit-scrollbar {
                display: none;
            }
            
            .overflow-auto {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        }
        
        @media (min-width: 1024px) {
            .lg\:fixed {
                position: fixed !important;
            }
            
            .lg\:translate-x-0 {
                transform: translateX(0) !important;
            }
        }
        
        /* Safe Area Handling for Mobile */
        @supports (padding: max(0px)) {
            .safe-area-top {
                padding-top: max(1rem, env(safe-area-inset-top));
            }
            .safe-area-bottom {
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 font-sans antialiased overflow-hidden" x-data="{ sidebarOpen: false, notificationsOpen: false }" x-cloak>
    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-3 max-w-sm w-full toast-container">
        @if (session('success'))
            <div class="toast toast-success toast-enter bg-white border border-green-200 rounded-xl p-4 shadow-xl relative overflow-hidden hover:toast-hover transition-all duration-300 cursor-pointer" onclick="removeToast(this)">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                        <i class="ri-check-line text-white text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="text-green-800 font-semibold text-sm">Success</h4>
                            <span class="text-green-500 text-xs">{{ now()->format('H:i') }}</span>
                        </div>
                        <p class="text-green-700 text-sm mt-1 leading-relaxed">{{ session('success') }}</p>
                    </div>
                    <button onclick="event.stopPropagation(); removeToast(this.parentElement)" class="text-green-400 hover:text-green-600 transition-colors p-1 rounded-full hover:bg-green-50">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>
                <div class="toast-progress bg-green-500"></div>
            </div>
        @endif

        @if (session('error'))
            <div class="toast toast-error toast-enter bg-white border border-red-200 rounded-xl p-4 shadow-xl relative overflow-hidden hover:toast-hover transition-all duration-300 cursor-pointer" onclick="removeToast(this)">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-pink-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                        <i class="ri-error-warning-line text-white text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="text-red-800 font-semibold text-sm">Error</h4>
                            <span class="text-red-500 text-xs">{{ now()->format('H:i') }}</span>
                        </div>
                        <p class="text-red-700 text-sm mt-1 leading-relaxed">{{ session('error') }}</p>
                    </div>
                    <button onclick="event.stopPropagation(); removeToast(this.parentElement)" class="text-red-400 hover:text-red-600 transition-colors p-1 rounded-full hover:bg-red-50">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>
                <div class="toast-progress bg-red-500"></div>
            </div>
        @endif

        @if (session('warning'))
            <div class="toast toast-warning toast-enter bg-white border border-yellow-200 rounded-xl p-4 shadow-xl relative overflow-hidden hover:toast-hover transition-all duration-300 cursor-pointer" onclick="removeToast(this)">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                        <i class="ri-alert-line text-white text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="text-yellow-800 font-semibold text-sm">Warning</h4>
                            <span class="text-yellow-500 text-xs">{{ now()->format('H:i') }}</span>
                        </div>
                        <p class="text-yellow-700 text-sm mt-1 leading-relaxed">{{ session('warning') }}</p>
                    </div>
                    <button onclick="event.stopPropagation(); removeToast(this.parentElement)" class="text-yellow-400 hover:text-yellow-600 transition-colors p-1 rounded-full hover:bg-yellow-50">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>
                <div class="toast-progress bg-yellow-500"></div>
            </div>
        @endif

        @if (session('info'))
            <div class="toast toast-info toast-enter bg-white border border-blue-200 rounded-xl p-4 shadow-xl relative overflow-hidden hover:toast-hover transition-all duration-300 cursor-pointer" onclick="removeToast(this)">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                        <i class="ri-information-line text-white text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="text-blue-800 font-semibold text-sm">Information</h4>
                            <span class="text-blue-500 text-xs">{{ now()->format('H:i') }}</span>
                        </div>
                        <p class="text-blue-700 text-sm mt-1 leading-relaxed">{{ session('info') }}</p>
                    </div>
                    <button onclick="event.stopPropagation(); removeToast(this.parentElement)" class="text-blue-400 hover:text-blue-600 transition-colors p-1 rounded-full hover:bg-blue-50">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>
                <div class="toast-progress bg-blue-500"></div>
            </div>
        @endif

        @if (session('status'))
            <div class="toast toast-status toast-enter bg-white border border-blue-200 rounded-xl p-4 shadow-xl relative overflow-hidden hover:toast-hover transition-all duration-300 cursor-pointer" onclick="removeToast(this)">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                        <i class="ri-information-line text-white text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="text-blue-800 font-semibold text-sm">Status</h4>
                            <span class="text-blue-500 text-xs">{{ now()->format('H:i') }}</span>
                        </div>
                        <p class="text-blue-700 text-sm mt-1 leading-relaxed">{{ session('status') }}</p>
                    </div>
                    <button onclick="event.stopPropagation(); removeToast(this.parentElement)" class="text-blue-400 hover:text-blue-600 transition-colors p-1 rounded-full hover:bg-blue-50">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>
                <div class="toast-progress bg-blue-500"></div>
            </div>
        @endif
    </div>
<style>
    @media (max-width: 1024px) {
        .lg\:hidden { display: block; }
        .lg\:translate-x-0 { transform: translateX(-100%); }
    }
    @media (min-width: 1025px) {
        #sidebar { transform: translateX(0); }
    }
</style>
    <!-- Mobile Overlay -->
    <div id="mobile-overlay" 
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden transition-opacity duration-300" 
         :class="sidebarOpen || notificationsOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'"
         @click="sidebarOpen = false; notificationsOpen = false"></div>

    <div class="flex h-screen relative">
        <!-- Enhanced Left Sidebar -->
        <aside id="sidebar" 
               class="fixed lg:static inset-y-0 left-0 z-50 w-64 sm:w-72 lg:w-80 glass-dark text-white transform transition-all duration-300 ease-in-out shadow-2xl flex flex-col" 
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <!-- Enhanced Logo Section -->
            <div class="flex items-center justify-between p-4 sm:p-6 border-b border-white/10 safe-area-top">
                <div class="flex items-center space-x-3 min-w-0 flex-1">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-primary-500 via-blue-600 to-indigo-700 rounded-xl flex items-center justify-center overflow-hidden shadow-lg ring-2 ring-white/20">
                        @if(!empty($siteSettings->brand_logo))
                            <img src="{{ asset('storage/' . $siteSettings->brand_logo) }}" alt="Brand Logo" class="w-full h-full object-contain rounded-xl" loading="lazy">
                        @else
                            <i class="ri-admin-line text-xl sm:text-2xl text-white"></i>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-lg sm:text-xl font-bold bg-gradient-to-r from-white via-blue-100 to-indigo-200 bg-clip-text text-transparent truncate">{{ $siteSettings->app_name ?? config('app.name', 'ParkSmart') }}</h1>
                        <p class="text-xs text-slate-300 truncate">Admin Dashboard</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" 
                        class="lg:hidden touch-target p-2 text-slate-300 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 focus-ring">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>

            <!-- Enhanced Navigation Menu -->
            <nav class="mt-2 px-3 sm:px-4 space-y-1 flex-1 overflow-y-auto custom-scrollbar pb-20" role="navigation" aria-label="Admin Navigation">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="group flex items-center px-3 sm:px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-blue-600 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-lg ring-2 ring-white/20' : '' }}" 
                   aria-current="{{ request()->routeIs('admin.dashboard') ? 'page' : 'false' }}">
                    <i class="ri-dashboard-3-line text-xl mr-3 sm:mr-4 flex-shrink-0"></i>
                    <span class="font-medium truncate">Dashboard</span>
                    <div class="ml-auto opacity-0 group-hover:opacity-100 transition-all duration-200 transform group-hover:translate-x-1">
                        <i class="ri-arrow-right-s-line text-lg"></i>
                    </div>
                </a>

                <!-- Plate Records -->
                <div class="space-y-1" x-data="{ open: {{ request()->routeIs('admin.plates.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="group w-full flex items-center px-3 sm:px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-blue-600 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.plates.*') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-lg ring-2 ring-white/20' : '' }}" 
                            :aria-expanded="open" 
                            aria-controls="plate-submenu">
                        <i class="ri-car-line text-xl mr-3 sm:mr-4 flex-shrink-0"></i>
                        <span class="font-medium truncate">Plate Records</span>
                        <div class="ml-auto">
                            <i class="ri-arrow-down-s-line transform transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </div>
                    </button>
                    <div id="plate-submenu" 
                         class="ml-6 sm:ml-8 space-y-1 mt-2 overflow-hidden transition-all duration-300" 
                         x-show="open" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="opacity-100 transform translate-y-0" 
                         x-transition:leave-end="opacity-0 transform -translate-y-2">
                        <a href="{{ route('admin.plates.index') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.plates.index') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-eye-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">View All Plates</span>
                        </a>
                        <a href="{{ route('admin.plates.create') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.plates.create') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-add-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">Add New Plate</span>
                        </a>
                    </div>
                </div>

                <!-- Parking Rates -->
                <div class="space-y-1" x-data="{ open: {{ request()->routeIs('admin.rates.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="group w-full flex items-center px-3 sm:px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-blue-600 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.rates.*') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-lg ring-2 ring-white/20' : '' }}" 
                            :aria-expanded="open" 
                            aria-controls="rates-submenu">
                        <i class="ri-settings-2-line text-xl mr-3 sm:mr-4 flex-shrink-0"></i>
                        <span class="font-medium truncate">Parking Rates</span>
                        <div class="ml-auto">
                            <i class="ri-arrow-down-s-line transform transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </div>
                    </button>
                    <div id="rates-submenu" 
                         class="ml-6 sm:ml-8 space-y-1 mt-2 overflow-hidden transition-all duration-300" 
                         x-show="open" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="opacity-100 transform translate-y-0" 
                         x-transition:leave-end="opacity-0 transform -translate-y-2">
                        <a href="{{ route('admin.rates.index') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.rates.index') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-list-check text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">View All Rates</span>
                        </a>
                        <a href="{{ route('admin.rates.create') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.rates.create') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-add-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">Add New Rate</span>
                        </a>
                    </div>
                </div>

                <!-- Parking Sessions -->
                <div class="space-y-1" x-data="{ open: {{ request()->routeIs('admin.sessions.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="group w-full flex items-center px-3 sm:px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-blue-600 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.sessions.*') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-lg ring-2 ring-white/20' : '' }}" 
                            :aria-expanded="open" 
                            aria-controls="sessions-submenu">
                        <i class="ri-time-line text-xl mr-3 sm:mr-4 flex-shrink-0"></i>
                        <span class="font-medium truncate">Parking Sessions</span>
                        <div class="ml-auto">
                            <i class="ri-arrow-down-s-line transform transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </div>
                    </button>
                    <div id="sessions-submenu" 
                         class="ml-6 sm:ml-8 space-y-1 mt-2 overflow-hidden transition-all duration-300" 
                         x-show="open" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="opacity-100 transform translate-y-0" 
                         x-transition:leave-end="opacity-0 transform -translate-y-2">
                        <a href="{{ route('admin.sessions.index') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.sessions.index') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-list-check text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">All Sessions</span>
                        </a>
                        <a href="{{ route('admin.sessions.create') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.sessions.create') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-add-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">Start New Session</span>
                        </a>
                    </div>
                </div>

                <!-- Ticketing -->
                <div class="space-y-1" x-data="{ open: {{ request()->routeIs('admin.tickets.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="group w-full flex items-center px-3 sm:px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-blue-600 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.tickets.*') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-lg ring-2 ring-white/20' : '' }}" 
                            :aria-expanded="open" 
                            aria-controls="ticketing-submenu">
                        <i class="ri-ticket-2-line text-xl mr-3 sm:mr-4 flex-shrink-0"></i>
                        <span class="font-medium truncate">Ticketing</span>
                        <div class="ml-auto">
                            <i class="ri-arrow-down-s-line transform transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </div>
                    </button>
                    <div id="ticketing-submenu" 
                         class="ml-6 sm:ml-8 space-y-1 mt-2 overflow-hidden transition-all duration-300" 
                         x-show="open" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="opacity-100 transform translate-y-0" 
                         x-transition:leave-end="opacity-0 transform -translate-y-2">
                        <a href="{{ route('admin.tickets.index') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.tickets.index') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-list-check text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">All Tickets</span>
                        </a>
                    </div>
                </div>

                <!-- Branch Management -->
                <div class="space-y-1" x-data="{ open: {{ request()->routeIs('admin.branches.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="group w-full flex items-center px-3 sm:px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-blue-600 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.branches.*') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-lg ring-2 ring-white/20' : '' }}" 
                            :aria-expanded="open" 
                            aria-controls="branches-submenu">
                        <i class="ri-building-line text-xl mr-3 sm:mr-4 flex-shrink-0"></i>
                        <span class="font-medium truncate">Branch Management</span>
                        <div class="ml-auto">
                            <i class="ri-arrow-down-s-line transform transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </div>
                    </button>
                    <div id="branches-submenu" 
                         class="ml-6 sm:ml-8 space-y-1 mt-2 overflow-hidden transition-all duration-300" 
                         x-show="open" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="opacity-100 transform translate-y-0" 
                         x-transition:leave-end="opacity-0 transform -translate-y-2">
                        <a href="{{ route('admin.branches.index') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.branches.index') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-list-check text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">All Branches</span>
                        </a>
                        <a href="{{ route('admin.branches.create') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.branches.create') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-add-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">Add New Branch</span>
                        </a>
                    </div>
                </div>

                <!-- Reports & Sales -->
                <div class="space-y-1" x-data="{ open: {{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="group w-full flex items-center px-3 sm:px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-blue-600 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.reports.*') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-lg ring-2 ring-white/20' : '' }}" 
                            :aria-expanded="open" 
                            aria-controls="reports-submenu">
                        <i class="ri-bar-chart-2-line text-xl mr-3 sm:mr-4 flex-shrink-0"></i>
                        <span class="font-medium truncate">Reports & Sales</span>
                        <div class="ml-auto">
                            <i class="ri-arrow-down-s-line transform transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </div>
                    </button>
                    <div id="reports-submenu" 
                         class="ml-6 sm:ml-8 space-y-1 mt-2 overflow-hidden transition-all duration-300" 
                         x-show="open" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="opacity-100 transform translate-y-0" 
                         x-transition:leave-end="opacity-0 transform -translate-y-2">
                        <a href="{{ route('admin.reports.sales') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.reports.sales') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-calendar-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">Daily Sales</span>
                        </a>
                    </div>
                </div>

                <!-- User Management -->
                <div class="space-y-1" x-data="{ open: {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="group w-full flex items-center px-3 sm:px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-blue-600 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-lg ring-2 ring-white/20' : '' }}" 
                            :aria-expanded="open" 
                            aria-controls="users-submenu">
                        <i class="ri-team-line text-xl mr-3 sm:mr-4 flex-shrink-0"></i>
                        <span class="font-medium truncate">User Management</span>
                        <div class="ml-auto">
                            <i class="ri-arrow-down-s-line transform transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </div>
                    </button>
                    <div id="users-submenu" 
                         class="ml-6 sm:ml-8 space-y-1 mt-2 overflow-hidden transition-all duration-300" 
                         x-show="open" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="opacity-100 transform translate-y-0" 
                         x-transition:leave-end="opacity-0 transform -translate-y-2">
                        <a href="{{ route('admin.users.create') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.users.create') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-user-add-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">Create New User</span>
                        </a>
                        <a href="{{ route('admin.users.pending') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.users.pending') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-user-add-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">Pending Attendants</span>
                            @php
                                $pendingCount = \App\Models\User::getPendingAttendantsCount();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="ml-auto bg-yellow-500 text-white text-xs px-2 py-1 rounded-full font-medium shadow-sm">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.users.index') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-user-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">Active Attendants</span>
                            @php
                                $activeCount = \App\Models\User::getActiveAttendantsCount();
                            @endphp
                            @if($activeCount > 0)
                                <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full font-medium shadow-sm">{{ $activeCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.users.rejected') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.users.rejected') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-user-forbid-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">Rejected Registrations</span>
                            @php
                                $rejectedCount = \App\Models\User::getRejectedAttendantsCount();
                            @endphp
                            @if($rejectedCount > 0)
                                <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full font-medium shadow-sm">{{ $rejectedCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                <!-- Notifications -->
                <!-- <a href="{{ route('admin.notifications.index') }}" class="group flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.notifications.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                    <i class="ri-notification-3-line text-xl mr-4"></i>
                    <span class="font-medium">Notifications</span>
                    @php
                        $unreadCount = Auth::user()->unreadNotifications->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $unreadCount }}</span>
                    @endif
                    <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <i class="ri-arrow-right-s-line"></i>
                    </div>
                </a> -->

                <!-- Settings -->
                <div class="space-y-1" x-data="{ open: {{ request()->routeIs('profile.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.activity-logs.*') || request()->routeIs('admin.location-monitor.*') || request()->routeIs('admin.ticket-config.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="group w-full flex items-center px-3 sm:px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-blue-600 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('profile.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.activity-logs.*') || request()->routeIs('admin.location-monitor.*') || request()->routeIs('admin.ticket-config.*') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-lg ring-2 ring-white/20' : '' }}" 
                            :aria-expanded="open" 
                            aria-controls="settings-submenu">
                        <i class="ri-settings-3-line text-xl mr-3 sm:mr-4 flex-shrink-0"></i>
                        <span class="font-medium truncate">Settings</span>
                        <div class="ml-auto">
                            <i class="ri-arrow-down-s-line transform transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </div>
                    </button>
                    <div id="settings-submenu" 
                         class="ml-6 sm:ml-8 space-y-1 mt-2 overflow-hidden transition-all duration-300" 
                         x-show="open" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="opacity-100 transform translate-y-0" 
                         x-transition:leave-end="opacity-0 transform -translate-y-2">
                        <a href="{{ route('profile.edit') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('profile.edit') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-user-settings-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">My Profile</span>
                        </a>
                        <a href="{{ route('admin.settings.index') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.settings.index') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-apps-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">Site Settings</span>
                        </a>
                        <a href="{{ route('admin.activity-logs.index') }}" 
                           class="flex items-center px-3 sm:px-4 py-2 text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 touch-target focus-ring {{ request()->routeIs('admin.activity-logs.*') ? 'bg-gradient-to-r from-primary-600 to-blue-600 text-white shadow-md' : '' }}">
                            <i class="ri-history-line text-sm mr-3 flex-shrink-0"></i>
                            <span class="text-sm truncate">Activity Logs</span>
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Enhanced Bottom Section -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10 safe-area-bottom">
                <div class="flex items-center space-x-3 mb-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin User') }}&background=0ea5e9&color=fff&rounded=true" alt="Admin" class="w-10 h-10 rounded-full ring-2 ring-primary-500/50 shadow-lg" loading="lazy">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Admin User' }}</p>
                        <p class="text-xs text-slate-300 truncate">Administrator</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-xl transition-all duration-200 font-medium shadow-lg touch-target focus-ring">
                        <i class="ri-logout-box-line mr-2"></i>
                        <span class="truncate">Sign Out</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
<div id="main-content-wrapper" class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out">
            <!-- Top Header -->
            <header class="glass-effect border-b border-white/20 px-4 lg:px-8 py-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 sm:space-x-4 min-w-0 flex-1">
                        <button @click="sidebarOpen = !sidebarOpen" 
                                class="lg:hidden touch-target p-2.5 bg-white/70 hover:bg-white/90 rounded-xl transition-all duration-200 shadow-sm focus-ring" 
                                aria-label="Toggle sidebar">
                            <i class="ri-menu-line text-xl text-slate-700"></i>
                        </button>
                        <div class="min-w-0 flex-1">
                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-800 truncate header-title">@yield('title', 'Dashboard')</h1>
                            <p class="text-xs sm:text-sm text-slate-600 mt-0.5 truncate header-subtitle">@yield('subtitle', 'Welcome back, manage your parking system')</p>
                        </div>
                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center space-x-2 sm:space-x-3 header-actions">
                        <!-- Notifications -->
                        <button @click="notificationsOpen = !notificationsOpen" 
                                class="relative touch-target p-2.5 bg-white/70 hover:bg-white/90 rounded-xl transition-all duration-200 shadow-sm focus-ring" 
                                aria-label="Toggle notifications">
                            <i class="ri-notification-3-line text-lg sm:text-xl text-slate-700"></i>
                            @php
                                $unreadNotifications = Auth::user()->unreadNotifications;
                                $unreadCount = $unreadNotifications->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-medium animate-pulse">{{ $unreadCount }}</span>
                            @endif
                        </button>

                        <!-- Profile -->
                        <div class="hidden md:flex items-center space-x-3 bg-white/70 rounded-xl px-3 py-2 profile-info shadow-sm">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin User') }}&background=0ea5e9&color=fff&rounded=true" alt="Admin" class="w-8 h-8 rounded-full ring-2 ring-white/50" loading="lazy">
                            <div class="text-sm min-w-0">
                                <p class="font-medium text-slate-800 truncate">{{ Auth::user()->name ?? 'Admin User' }}</p>
                                <p class="text-slate-600 text-xs truncate">Administrator</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-auto p-4 lg:p-8 main-content">
                @yield('content')
            </main>
        </div>

        <!-- Enhanced Right Sidebar for Notifications -->
        <aside id="notifications-sidebar" 
               class="fixed inset-y-0 right-0 z-50 w-80 sm:w-88 lg:w-96 glass-effect border-l border-white/20 shadow-2xl transform transition-all duration-300 ease-in-out" 
               :class="notificationsOpen ? 'translate-x-0' : 'translate-x-full'">
            <div class="h-full flex flex-col safe-area-top safe-area-bottom">
                <!-- Enhanced Header -->
                <div class="p-4 sm:p-6 border-b border-slate-200/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="ri-notification-3-line text-white text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg sm:text-xl font-bold text-slate-800">Notifications</h2>
                                <p class="text-xs sm:text-sm text-slate-600">Stay updated with recent activities</p>
                            </div>
                        </div>
                        <button @click="notificationsOpen = false" 
                                class="touch-target p-2 hover:bg-slate-100 rounded-lg transition-all duration-200 focus-ring" 
                                aria-label="Close notifications">
                            <i class="ri-close-line text-xl text-slate-600"></i>
                        </button>
                    </div>
                </div>

                <!-- Enhanced Notifications List -->
                <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar" id="notifications-list">
                    @php
                        $notifications = Auth::user()->notifications()->latest()->take(10)->get();
                    @endphp

                    @forelse($notifications as $notification)
                        <div class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 border border-blue-200/50 rounded-xl p-4 hover:shadow-lg hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 {{ $notification->read_at ? 'opacity-75' : '' }}" data-id="{{ $notification->id }}">
                            <div class="flex items-start space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="ri-user-add-line text-white text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="font-semibold text-slate-800 text-sm truncate">New User Registered</p>
                                        @if(!$notification->read_at)
                                            <span class="w-2 h-2 bg-primary-500 rounded-full animate-pulse flex-shrink-0 ml-2"></span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                                        {{ $notification->data['name'] ?? 'Unknown User' }} joined the platform
                                    </p>
                                    <div class="flex items-center justify-between mt-3">
                                        <p class="text-xs text-slate-500">{{ $notification->created_at->diffForHumans() }}</p>
                                        @if(!$notification->read_at)
                                            <button onclick="window.markAsRead('{{ $notification->id }}')" class="text-xs text-primary-600 hover:text-primary-800 font-medium transition-colors duration-200">
                                                Mark as read
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gradient-to-br from-slate-100 to-slate-200 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                                <i class="ri-notification-off-line text-3xl text-slate-400"></i>
                            </div>
                            <p class="text-slate-500 text-sm font-medium">No notifications yet</p>
                            <p class="text-slate-400 text-xs mt-2 max-w-48 mx-auto leading-relaxed">You'll see notifications here when they arrive</p>
                        </div>
                    @endforelse
                </div>

                <!-- Enhanced Footer Actions -->
                <div class="p-4 border-t border-slate-200/50 space-y-3">
                    <button onclick="window.markAllAsRead()" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 px-4 rounded-xl transition-all duration-200 font-medium text-sm focus-ring">
                        <i class="ri-check-double-line mr-2"></i>
                        Mark All as Read
                    </button>
                    <a href="{{ route('admin.notifications.index') }}" class="block w-full bg-gradient-to-r from-primary-600 to-blue-600 text-white py-2.5 px-4 rounded-xl hover:from-primary-700 hover:to-blue-700 transition-all duration-200 font-medium text-center text-sm shadow-lg focus-ring">
                        <i class="ri-external-link-line mr-2"></i>
                        View All Notifications
                    </a>
                </div>
            </div>
        </aside>
    </div>

    @vite('resources/js/app.js')

    <!-- JavaScript for Mobile Interactions -->
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const closeSidebar = document.getElementById('close-sidebar');
        const mobileOverlay = document.getElementById('mobile-overlay');

        mobileMenuBtn?.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            mobileOverlay.classList.remove('hidden');
        });

        closeSidebar?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            mobileOverlay.classList.add('hidden');
        });

        mobileOverlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            mobileOverlay.classList.add('hidden');
        });

        // Universal notifications sidebar toggle
        const notificationsBtn = document.getElementById('notifications-btn');
        const notificationsSidebar = document.getElementById('notifications-sidebar');
        const closeNotificationsBtn = document.getElementById('close-notifications');
        const mainContentWrapper = document.getElementById('main-content-wrapper');
        const notificationsList = document.getElementById('notifications-list');
        const notificationsBadge = document.getElementById('notifications-badge');
        const csrfToken = '{{ csrf_token() }}';

        function toggleNotifications() {
            const isHidden = notificationsSidebar.classList.contains('translate-x-full');

            if (isHidden) {
                notificationsSidebar.classList.remove('translate-x-full');
                if (window.innerWidth < 1024) {
                    mobileOverlay.classList.remove('hidden');
                } else {
                    mainContentWrapper.style.marginRight = `${notificationsSidebar.offsetWidth}px`;
                }
            } else {
                notificationsSidebar.classList.add('translate-x-full');
                if (window.innerWidth < 1024) {
                    mobileOverlay.classList.add('hidden');
                } else {
                    mainContentWrapper.style.marginRight = '0';
                }
            }
        }

        notificationsBtn?.addEventListener('click', toggleNotifications);
        closeNotificationsBtn?.addEventListener('click', toggleNotifications);

        async function fetchUnreadNotifications() {
            try {
                const res = await fetch('{{ url('/notifications/unread') }}', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                updateNotificationsUI(data.notifications, data.count);
            } catch (e) { /* noop */ }
        }

        function updateNotificationsUI(items, count) {
            if (!notificationsList) return;
            notificationsList.innerHTML = '';
            if (!items || items.length === 0) {
                notificationsList.innerHTML = `
                    <div class=\"text-center py-8\">\n                        <div class=\"w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4\">\n                            <i class=\"ri-notification-off-line text-2xl text-slate-400\"></i>\n                        </div>\n                        <p class=\"text-slate-500 text-sm\">No notifications yet</p>\n                        <p class=\"text-slate-400 text-xs mt-1\">You'll see notifications here when they arrive</p>\n                    </div>`;
            } else {
                for (const n of items) {
                    const iconClass = n.type === 'success' ? 'ri-check-line' : (n.type === 'warning' ? 'ri-alert-line' : 'ri-information-line');
                    const item = document.createElement('div');
                    item.className = 'bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 hover:shadow-md transition-shadow';
                    item.innerHTML = `
                        <div class=\"flex items-start space-x-3\">\n                            <div class=\"w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0\">\n                                <i class=\"${iconClass} text-white\"></i>\n                            </div>\n                            <div class=\"flex-1\">\n                                <div class=\"flex items-center justify-between\">\n                                    <p class=\"font-semibold text-slate-800\">${escapeHtml(n.title || 'Notification')}</p>\n                                </div>\n                                <p class=\"text-sm text-slate-600 mt-1\">${escapeHtml(n.message || '')}</p>\n                                <p class=\"text-xs text-slate-500 mt-2\">just now</p>\n                                <div class=\"mt-2 flex gap-3\">\n                                    <button class=\"text-xs text-blue-600 hover:text-blue-800 underline\" onclick=\"window.markAsRead('${n.id}')\">Mark as read</button>\n                                    ${n.link ? `<a class=\\"text-xs text-blue-600 hover:text-blue-800 underline\\" href=\\"${n.link}\\">Open</a>` : ''}\n                                </div>\n                            </div>\n                        </div>`;
                    notificationsList.prepend(item);
                }
            }
            updateBadge(count ?? items.length);
        }

        function updateBadge(count) {
            if (!notificationsBadge) return;
            if (count > 0) {
                notificationsBadge.textContent = count;
                notificationsBadge.classList.remove('hidden');
            } else {
                notificationsBadge.classList.add('hidden');
            }
        }

        function escapeHtml(str) {
            return String(str || '').replace(/[&<>\"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;'}[s]));
        }

        window.markAsRead = async function(id) {
            try {
                await fetch(`{{ url('/notifications') }}/${id}/read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
                fetchUnreadNotifications();
            } catch (e) { /* noop */ }
        }

        window.markAllAsRead = async function() {
            try {
                await fetch(`{{ url('/notifications/read-all') }}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
                fetchUnreadNotifications();
            } catch (e) { /* noop */ }
        }

        // Setup Echo subscriptions
        @auth
        try {
            const userId = {{ Auth::id() }};
            const isAdmin = {{ Auth::user()->hasRole('admin') ? 'true' : 'false' }};
            if (isAdmin) {
                window.Echo.private('admin')
                    .notification((notification) => {
                        updateNotificationsUI([notification], (parseInt(notificationsBadge?.textContent || '0', 10) || 0) + 1);
                    });
            } else {
                window.Echo.private(`attendant.${userId}`)
                    .notification((notification) => {
                        updateNotificationsUI([notification], (parseInt(notificationsBadge?.textContent || '0', 10) || 0) + 1);
                    });
            }
        } catch (_) {}
        @endauth

        // Initial load
        fetchUnreadNotifications();

        // Hide sidebar when clicking overlay
        mobileOverlay?.addEventListener('click', () => {
            const leftSidebar = document.getElementById('sidebar');
            if (!leftSidebar.classList.contains('-translate-x-full')) {
                leftSidebar.classList.add('-translate-x-full');
                mobileOverlay.classList.add('hidden');
            }
            if (!notificationsSidebar.classList.contains('translate-x-full')) {
                notificationsSidebar.classList.add('translate-x-full');
                mobileOverlay.classList.add('hidden');
            }
        });

        // Auto-hide mobile sidebar on window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.add('-translate-x-full');
                mobileOverlay.classList.add('hidden');
                notificationsSidebar.classList.remove('translate-x-full');
            }
        });

                // Submenu toggle function with accordion behavior
        function toggleSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const icon = document.getElementById(submenuId + '-icon');

            if (submenu && icon) {
                // Get all submenus and their icons
                const allSubmenus = [
                    'plate-submenu', 'rates-submenu', 'sessions-submenu',
                    'ticketing-submenu', 'reports-submenu', 'users-submenu', 'settings-submenu'
                ];

                // Check if current submenu is open
                const isCurrentOpen = !submenu.classList.contains('hidden');

                // Close all submenus first
                allSubmenus.forEach(menuId => {
                    const menu = document.getElementById(menuId);
                    const menuIcon = document.getElementById(menuId + '-icon');

                    if (menu && menuIcon) {
                        menu.classList.add('hidden');
                        menuIcon.classList.remove('rotate-180');
                    }
                });

                // If the current submenu was closed, open it
                if (isCurrentOpen) {
                    submenu.classList.add('hidden');
                    icon.classList.remove('rotate-180');
                } else {
                    submenu.classList.remove('hidden');
                    icon.classList.add('rotate-180');
                }
            }
        }

        // Function to mark notification as read
        function markAsRead(notificationId) {
            const url = `/admin/notifications/${notificationId}/mark-as-read`;

            fetch(url, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notificationCard = document.querySelector(`[data-id="${notificationId}"]`);
                    if (notificationCard) {
                        notificationCard.classList.add('opacity-75');
                        const unreadDot = notificationCard.querySelector('.w-2.h-2.bg-blue-500');
                        if (unreadDot) {
                            unreadDot.remove();
                        }
                        const markAsReadBtn = notificationCard.querySelector(`button`);
                        if (markAsReadBtn) {
                            markAsReadBtn.remove();
                        }
                    }
                    updateNotificationCount(data.unread_count);
                    showToast('Notification marked as read', 'success');
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
                showToast('Failed to mark notification as read', 'error');
            });
        }

        // Function to update notification count
        function updateNotificationCount(count) {
            const notificationBtn = document.getElementById('notifications-btn');
            let countSpan = notificationBtn.querySelector('.absolute');

            if (count > 0) {
                if (!countSpan) {
                    countSpan = document.createElement('span');
                    countSpan.className = 'absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center animate-pulse';
                    notificationBtn.appendChild(countSpan);
                }
                countSpan.textContent = count;
            } else if (countSpan) {
                countSpan.remove();
            }
        }

        // Fetch unread count periodically
        setInterval(() => {
            fetch('/admin/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    updateNotificationCount(data.unread_count);
                })
                .catch(error => console.error('Error fetching notification count:', error));
        }, 30000); // every 30 seconds

                // Enhanced Toast Notification System
        function showToast(message, type = 'success', duration = 5000) {
            const container = document.getElementById('toast-container');

            // Create toast element with enhanced styling
            const toast = document.createElement('div');
            const color = getToastColor(type);
            const title = getToastTitle(type);
            const icon = getToastIcon(type);
            const currentTime = new Date().toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            toast.className = `toast toast-${type} toast-enter bg-white border border-${color}-200 rounded-xl p-4 shadow-xl relative overflow-hidden hover:toast-hover transition-all duration-300 cursor-pointer`;
            toast.onclick = () => removeToast(toast);

            // Create enhanced toast content
            toast.innerHTML = `
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-${color}-500 to-${getGradientColor(type)}-500 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                        <i class="${icon} text-white text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h4 class="text-${color}-800 font-semibold text-sm">${title}</h4>
                            <span class="text-${color}-500 text-xs">${currentTime}</span>
                        </div>
                        <p class="text-${color}-700 text-sm mt-1 leading-relaxed">${message}</p>
                    </div>
                    <button onclick="event.stopPropagation(); removeToast(this.parentElement)" class="text-${color}-400 hover:text-${color}-600 transition-colors p-1 rounded-full hover:bg-${color}-50">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>
                <div class="toast-progress bg-${color}-500"></div>
            `;

            // Add to container
            container.appendChild(toast);

            // Auto remove after duration
            setTimeout(() => {
                removeToast(toast);
            }, duration);
        }

                function removeToast(toast) {
            if (toast) {
                // Add exit animation
                toast.classList.remove('toast-enter');
                toast.classList.add('toast-exit');

                // Remove progress bar animation
                const progressBar = toast.querySelector('.toast-progress');
                if (progressBar) {
                    progressBar.style.animation = 'none';
                }

                // Remove from DOM after animation
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 400);
            }
        }

        function getToastColor(type) {
            const colors = {
                'success': 'green',
                'error': 'red',
                'warning': 'yellow',
                'info': 'blue',
                'status': 'blue'
            };
            return colors[type] || 'blue';
        }

        function getGradientColor(type) {
            const gradients = {
                'success': 'emerald',
                'error': 'pink',
                'warning': 'orange',
                'info': 'indigo',
                'status': 'indigo'
            };
            return gradients[type] || 'indigo';
        }

        function getToastTitle(type) {
            const titles = {
                'success': 'Success',
                'error': 'Error',
                'warning': 'Warning',
                'info': 'Information',
                'status': 'Status'
            };
            return titles[type] || 'Information';
        }

        function getToastIcon(type) {
            const icons = {
                'success': 'ri-check-line',
                'error': 'ri-error-warning-line',
                'warning': 'ri-alert-line',
                'info': 'ri-information-line',
                'status': 'ri-information-line'
            };
            return icons[type] || 'ri-information-line';
        }

        // Enhanced auto-remove functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    removeToast(toast);
                }, 5000);

                // Add hover pause functionality
                toast.addEventListener('mouseenter', () => {
                    const progressBar = toast.querySelector('.toast-progress');
                    if (progressBar) {
                        progressBar.style.animationPlayState = 'paused';
                    }
                });

                toast.addEventListener('mouseleave', () => {
                    const progressBar = toast.querySelector('.toast-progress');
                    if (progressBar) {
                        progressBar.style.animationPlayState = 'running';
                    }
                });
            });
        });

        // Responsive toast positioning
        function updateToastPosition() {
            const container = document.getElementById('toast-container');
            if (container) {
                if (window.innerWidth < 768) {
                    // Mobile: center top
                    container.className = 'fixed top-4 left-4 right-4 z-50 space-y-3 max-w-sm mx-auto toast-container';
                } else {
                    // Desktop: top right
                    container.className = 'fixed top-4 right-4 z-50 space-y-3 max-w-sm w-full toast-container';
                }
            }
        }

        // Update position on window resize
        window.addEventListener('resize', updateToastPosition);

        // Initial position update
        document.addEventListener('DOMContentLoaded', updateToastPosition);

        // Global function to show toasts from anywhere
        window.showToast = showToast;
        window.removeToast = removeToast;
    </script>
</body>
</html>

