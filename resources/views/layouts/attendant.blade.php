<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>@yield('title', $siteSettings->app_name ?? config('app.name', 'ParkSmart')) - {{ $siteSettings->app_name ?? config('app.name', 'ParkSmart') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b'
                        },
                        'sidebar': {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a'
                        },
                        'accent': {
                            50: '#fef7ff',
                            500: '#a855f7',
                            600: '#9333ea'
                        }
                    },
                    spacing: {
                        '18': '4.5rem',
                        '88': '22rem'
                    },
                    animation: {
                        'slide-in': 'slide-in 0.3s ease-out',
                        'fade-in': 'fade-in 0.2s ease-out',
                        'bounce-subtle': 'bounce-subtle 0.6s ease-out',
                        'pulse-slow': 'pulse 3s infinite'
                    },
                    backdropBlur: {
                        'xs': '2px'
                    }
                }
            }
        }
    </script>
    <style>
        /* Core Animations */
        @keyframes slide-in {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slide-in-right {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slide-out-right {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        @keyframes bounce-in {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes bounce-subtle {
            0%, 20%, 53%, 80%, 100% { transform: translate3d(0,0,0); }
            40%, 43% { transform: translate3d(0, -8px, 0); }
            70% { transform: translate3d(0, -4px, 0); }
            90% { transform: translate3d(0, -2px, 0); }
        }
        @keyframes progress-shrink {
            from { width: 100%; }
            to { width: 0%; }
        }

        /* Enhanced Glass Effect */
        .glass-effect {
            backdrop-filter: blur(16px) saturate(180%);
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .glass-dark {
            backdrop-filter: blur(16px) saturate(180%);
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Toast System */
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
        .toast-hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .toast-container {
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
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
            background: rgba(16, 185, 129, 0.3);
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.5);
        }

        /* Mobile-First Responsive Design */
        .mobile-nav-item {
            @apply flex items-center justify-center w-full h-12 rounded-xl transition-all duration-200;
        }

        /* Desktop Sidebar Fixed Positioning */
        @media (min-width: 1024px) {
            #sidebar {
                position: fixed !important;
                height: 100vh !important;
                overflow-y: auto !important;
            }
            
            #main-content-wrapper {
                margin-left: 18rem; /* 72 * 0.25rem = 18rem */
            }
        }

        @media (min-width: 1280px) {
            #main-content-wrapper {
                margin-left: 20rem; /* 80 * 0.25rem = 20rem */
            }
        }

        /* Mobile Optimizations */
        @media (max-width: 640px) {
            .toast-container {
                left: 1rem;
                right: 1rem;
                top: 1rem;
                max-width: calc(100vw - 2rem);
            }
            .glass-effect {
                backdrop-filter: blur(8px);
                background: rgba(255, 255, 255, 0.9);
            }
            .sidebar-collapsed {
                width: 4rem !important;
            }
            .sidebar-collapsed .sidebar-text {
                display: none;
            }
            .sidebar-collapsed .sidebar-arrow {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .header-actions {
                gap: 0.5rem;
            }
            .header-actions button,
            .header-actions a {
                padding: 0.5rem;
                min-height: 44px;
                min-width: 44px;
            }
            .header-title {
                font-size: 1.25rem !important;
            }
            .header-subtitle {
                font-size: 0.75rem !important;
            }
        }

        @media (max-width: 1024px) {
            .notifications-sidebar {
                width: 100vw !important;
                max-width: 100vw !important;
            }
        }

        /* Touch-friendly Interactive Elements */
        .touch-target {
            min-height: 44px;
            min-width: 44px;
        }

        /* Loading States */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Focus States for Accessibility */
        .focus-ring:focus {
            outline: 2px solid #10b981;
            outline-offset: 2px;
        }

        /* Smooth Transitions */
        * {
            transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }

        /* Mobile Navigation Enhancements */
        .mobile-nav-overlay {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        /* Responsive Grid Improvements */
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
<body class="bg-gradient-to-br from-primary-50 to-blue-50 font-sans antialiased min-h-screen safe-area-top safe-area-bottom">
    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-3 max-w-sm w-full toast-container sm:max-w-md">
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
    </div>

    <!-- Mobile Overlay -->
    <div id="mobile-overlay" class="fixed inset-0 mobile-nav-overlay z-40 lg:hidden hidden transition-opacity duration-300"></div>

    <!-- Main App Container -->
    <div class="flex min-h-screen relative">
        <!-- Enhanced Left Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 sm:w-80 lg:w-72 xl:w-80 glass-dark text-white transform -translate-x-full lg:translate-x-0 transition-all duration-300 ease-in-out shadow-2xl flex flex-col custom-scrollbar">
            <!-- Enhanced Logo Section -->
            <div class="flex items-center justify-between p-4 sm:p-6 border-b border-white/10">
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center overflow-hidden shadow-lg ring-2 ring-white/20">
                        @if(!empty($siteSettings->brand_logo))
                            <img src="{{ asset('storage/' . $siteSettings->brand_logo) }}" alt="Brand Logo" class="w-12 h-12 object-contain rounded-2xl">
                        @else
                            <i class="ri-parking-line text-2xl text-white"></i>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-lg sm:text-xl font-bold text-white truncate">{{ $siteSettings->app_name ?? config('app.name', 'ParkSmart') }}</h1>
                        <p class="text-xs text-slate-300 flex items-center">
                            <i class="ri-shield-user-line mr-1"></i>
                            Attendant Panel
                        </p>
                    </div>
                </div>
                <button id="close-sidebar" class="lg:hidden text-slate-400 hover:text-white transition-colors touch-target p-2 rounded-xl hover:bg-white/10 focus-ring">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>

            <!-- Enhanced Navigation Menu -->
            <nav class="flex-1 px-3 sm:px-4 py-4 space-y-2 overflow-y-auto custom-scrollbar">
                <!-- Dashboard -->
                <a href="{{ route('attendant.dashboard') }}" class="group flex items-center px-4 py-3.5 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-primary-700 rounded-2xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('attendant.dashboard') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg ring-2 ring-primary-500/30' : '' }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-white/10 mr-4 group-hover:bg-white/20 transition-colors">
                        <i class="ri-dashboard-3-line text-lg"></i>
                    </div>
                    <span class="font-medium sidebar-text">Dashboard</span>
                    <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity sidebar-arrow">
                        <i class="ri-arrow-right-s-line text-sm"></i>
                    </div>
                </a>

                <!-- Plates Section -->
                <div class="space-y-1">
                    <button class="w-full flex items-center px-4 py-3.5 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-primary-700 rounded-2xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('attendant.plates.*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg ring-2 ring-primary-500/30' : '' }}" onclick="toggleSubmenu('plates-submenu')">
                        <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-white/10 mr-4 group-hover:bg-white/20 transition-colors">
                            <i class="ri-car-line text-lg"></i>
                        </div>
                        <span class="font-medium sidebar-text">Plates</span>
                        <div class="ml-auto sidebar-arrow">
                            <i class="ri-arrow-down-s-line transform transition-transform duration-200 {{ request()->routeIs('attendant.plates.*') ? 'rotate-180' : '' }}" id="plates-submenu-icon"></i>
                        </div>
                    </button>
                    <div id="plates-submenu" class="ml-12 space-y-1 mt-2 {{ request()->routeIs('attendant.plates.*') ? '' : 'hidden' }}">
                        <a href="{{ route('attendant.plates.index') }}" class="flex items-center px-4 py-2.5 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('attendant.plates.index') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-md' : '' }}">
                            <i class="ri-eye-line text-sm mr-3"></i>
                            <span class="text-sm sidebar-text">View All Plates</span>
                        </a>
                        <a href="{{ route('attendant.plates.create') }}" class="flex items-center px-4 py-2.5 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('attendant.plates.create') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-md' : '' }}">
                            <i class="ri-add-line text-sm mr-3"></i>
                            <span class="text-sm sidebar-text">Add New Plate</span>
                        </a>
                    </div>
                </div>

                <!-- Sessions Section -->
                <div class="space-y-1">
                    <button class="w-full flex items-center px-4 py-3.5 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-primary-700 rounded-2xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('attendant.sessions.*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg ring-2 ring-primary-500/30' : '' }}" onclick="toggleSubmenu('sessions-submenu')">
                        <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-white/10 mr-4 group-hover:bg-white/20 transition-colors">
                            <i class="ri-time-line text-lg"></i>
                        </div>
                        <span class="font-medium sidebar-text">Sessions</span>
                        <div class="ml-auto sidebar-arrow">
                            <i class="ri-arrow-down-s-line transform transition-transform duration-200 {{ request()->routeIs('attendant.sessions.*') ? 'rotate-180' : '' }}" id="sessions-submenu-icon"></i>
                        </div>
                    </button>
                    <div id="sessions-submenu" class="ml-12 space-y-1 mt-2 {{ request()->routeIs('attendant.sessions.*') ? '' : 'hidden' }}">
                        <a href="{{ route('attendant.sessions.index') }}" class="flex items-center px-4 py-2.5 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('attendant.sessions.index') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-md' : '' }}">
                            <i class="ri-list-check text-sm mr-3"></i>
                            <span class="text-sm sidebar-text">All Sessions</span>
                        </a>
                        <a href="{{ route('attendant.sessions.create') }}" class="flex items-center px-4 py-2.5 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('attendant.sessions.create') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-md' : '' }}">
                            <i class="ri-add-line text-sm mr-3"></i>
                            <span class="text-sm sidebar-text">Start New Session</span>
                        </a>
                    </div>
                </div>

                <!-- Tickets Section -->
                <div class="space-y-1">
                    <button class="w-full flex items-center px-4 py-3.5 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-primary-700 rounded-2xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('attendant.tickets.*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg ring-2 ring-primary-500/30' : '' }}" onclick="toggleSubmenu('tickets-submenu')">
                        <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-white/10 mr-4 group-hover:bg-white/20 transition-colors">
                            <i class="ri-ticket-2-line text-lg"></i>
                        </div>
                        <span class="font-medium sidebar-text">Tickets</span>
                        <div class="ml-auto sidebar-arrow">
                            <i class="ri-arrow-down-s-line transform transition-transform duration-200 {{ request()->routeIs('attendant.tickets.*') ? 'rotate-180' : '' }}" id="tickets-submenu-icon"></i>
                        </div>
                    </button>
                    <div id="tickets-submenu" class="ml-12 space-y-1 mt-2 {{ request()->routeIs('attendant.tickets.*') ? '' : 'hidden' }}">
                        <a href="{{ route('attendant.tickets.index') }}" class="flex items-center px-4 py-2.5 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('attendant.tickets.index') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-md' : '' }}">
                            <i class="ri-list-check text-sm mr-3"></i>
                            <span class="text-sm sidebar-text">All Tickets</span>
                        </a>
                    </div>
                </div>

                <!-- Settings Section -->
                <div class="space-y-1">
                    <button class="w-full flex items-center px-4 py-3.5 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-primary-600 hover:to-primary-700 rounded-2xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('profile.*') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-lg ring-2 ring-primary-500/30' : '' }}" onclick="toggleSubmenu('settings-submenu')">
                        <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-white/10 mr-4 group-hover:bg-white/20 transition-colors">
                            <i class="ri-settings-3-line text-lg"></i>
                        </div>
                        <span class="font-medium sidebar-text">Settings</span>
                        <div class="ml-auto sidebar-arrow">
                            <i class="ri-arrow-down-s-line transform transition-transform duration-200 {{ request()->routeIs('profile.*') ? 'rotate-180' : '' }}" id="settings-submenu-icon"></i>
                        </div>
                    </button>
                    <div id="settings-submenu" class="ml-12 space-y-1 mt-2 {{ request()->routeIs('profile.*') ? '' : 'hidden' }}">
                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2.5 text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-200 touch-target focus-ring {{ request()->routeIs('profile.edit') ? 'bg-gradient-to-r from-primary-600 to-primary-700 text-white shadow-md' : '' }}">
                            <i class="ri-user-settings-line text-sm mr-3"></i>
                            <span class="text-sm sidebar-text">Profile</span>
                        </a>
                    </div>
                </div>
            </nav>

            <script type="module">
                const csrfToken = '{{ csrf_token() }}';
                const badge = document.getElementById('notifications-badge');
                const list = document.getElementById('notifications-list');

                function updateBadge(count){ if(!badge) return; if(count>0){ badge.textContent = count; badge.classList.remove('hidden'); } else { badge.classList.add('hidden'); } }
                function escapeHtml(str){ return String(str||'').replace(/[&<>\"]/g, s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s])); }
                function pushItem(n){ if(!list) return; const icon = n.type==='success'?'ri-check-line':(n.type==='warning'?'ri-alert-line':'ri-information-line'); const div=document.createElement('div'); div.className='bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 hover:shadow-md transition-shadow'; div.innerHTML = `<div class="flex items-start space-x-3"><div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0"><i class="${icon} text-white"></i></div><div class="flex-1"><div class="flex items-center justify-between"><p class="font-semibold text-slate-800">${escapeHtml(n.title||'Notification')}</p></div><p class="text-sm text-slate-600 mt-1">${escapeHtml(n.message||'')}</p><p class="text-xs text-slate-500 mt-2">just now</p></div></div>`; list.prepend(div); }

                @auth
                try {
                    const userId = {{ Auth::id() }};
                    window.Echo.private(`attendant.${userId}`).notification((n)=>{ pushItem(n); updateBadge((parseInt(badge?.textContent||'0',10)||0)+1); });
                } catch(_){ }
                @endauth
            </script>

            <!-- Enhanced Bottom Section -->
            <div class="mt-auto p-4 border-t border-white/10 bg-gradient-to-t from-black/20 to-transparent">
                <div class="flex items-center space-x-3 mb-4 p-3 bg-white/5 rounded-2xl">
                    <div class="relative">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=10b981&color=fff&rounded=true" alt="Attendant" class="w-12 h-12 rounded-2xl ring-2 ring-primary-500/50 shadow-lg">
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white animate-pulse-slow"></div>
                    </div>
                    <div class="flex-1 min-w-0 sidebar-text">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-300 flex items-center">
                            <i class="ri-shield-check-line mr-1 text-green-400"></i>
                            Online
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-2xl transition-all duration-200 touch-target focus-ring shadow-lg hover:shadow-xl">
                        <i class="ri-logout-box-line mr-2"></i>
                        <span class="sidebar-text">Sign Out</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Enhanced Main Content -->
        <div id="main-content-wrapper" class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out">
            <!-- Enhanced Top Header -->
            <header class="glass-effect border-b border-white/20 px-4 sm:px-6 lg:px-8 py-3 sm:py-4 shadow-sm sticky top-0 z-30">
                <div class="flex items-center justify-between gap-4">
                    <!-- Left Section -->
                    <div class="flex items-center space-x-3 sm:space-x-4 flex-1 min-w-0">
                        <button id="mobile-menu-btn" class="lg:hidden touch-target p-2 rounded-xl bg-white/70 hover:bg-white/90 transition-colors shadow-sm focus-ring">
                            <i class="ri-menu-line text-xl text-slate-700"></i>
                        </button>
                        <div class="flex-1 min-w-0">
                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-800 truncate header-title">@yield('title', 'Dashboard')</h1>
                            <p class="text-xs sm:text-sm text-slate-600 mt-0.5 truncate header-subtitle">@yield('subtitle', 'Manage your parking sessions')</p>
                        </div>
                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center space-x-2 sm:space-x-3 header-actions">
                        <!-- Quick Actions with Enhanced Mobile Tooltips -->
                        <div class="flex items-center space-x-2">
                            <div class="relative group">
                                <a href="{{ route('attendant.sessions.create') }}" class="bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white p-2.5 sm:px-4 sm:py-2.5 rounded-xl font-medium transition-all duration-200 shadow-lg hover:shadow-xl touch-target focus-ring flex items-center">
                                    <i class="ri-add-line text-lg sm:mr-2"></i>
                                    <span class="hidden sm:inline">New Session</span>
                                </a>
                                <!-- Mobile Tooltip -->
                                <div class="tooltip sm:hidden">New Session</div>
                            </div>
                            <div class="relative group">
                                <a href="{{ route('attendant.plates.create') }}" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white p-2.5 sm:px-4 sm:py-2.5 rounded-xl font-medium transition-all duration-200 shadow-lg hover:shadow-xl touch-target focus-ring flex items-center">
                                    <i class="ri-car-line text-lg sm:mr-2"></i>
                                    <span class="hidden sm:inline">Add Plate</span>
                                </a>
                                <!-- Mobile Tooltip -->
                                <div class="tooltip sm:hidden">Add Plate</div>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <button id="notifications-btn" class="relative touch-target p-2.5 bg-white/70 hover:bg-white/90 rounded-xl transition-colors shadow-sm focus-ring">
                            <i class="ri-notification-3-line text-lg sm:text-xl text-slate-700"></i>
                            @php
                                $unreadNotifications = Auth::user()->unreadNotifications;
                                $unreadCount = $unreadNotifications->count();
                            @endphp
                            <span id="notifications-badge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center shadow-lg animate-bounce-subtle {{ $unreadCount > 0 ? '' : 'hidden' }}">{{ $unreadCount }}</span>
                        </button>

                        <!-- Profile (Desktop Only) -->
                        <div class="hidden lg:flex items-center space-x-3 bg-white/70 rounded-xl px-3 py-2 shadow-sm">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=10b981&color=fff&rounded=true" alt="Attendant" class="w-8 h-8 rounded-xl ring-2 ring-primary-500/30">
                            <div class="text-sm">
                                <p class="font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                                <p class="text-slate-600 text-xs">Attendant</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Enhanced Main Content Area -->
            <main class="flex-1 overflow-auto p-3 sm:p-4 lg:p-6 xl:p-8 custom-scrollbar">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>

        <!-- Enhanced Right Sidebar for Notifications -->
        <aside id="notifications-sidebar" class="fixed inset-y-0 right-0 z-50 w-full sm:w-96 lg:w-80 xl:w-96 glass-effect border-l border-white/20 shadow-2xl transform translate-x-full transition-all duration-300 ease-in-out notifications-sidebar">
            <div class="h-full flex flex-col">
                <!-- Enhanced Header -->
                <div class="p-4 sm:p-6 border-b border-slate-200/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center">
                                <i class="ri-notification-3-line text-white text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg sm:text-xl font-bold text-slate-800">Notifications</h2>
                                <p class="text-xs sm:text-sm text-slate-600">Stay updated with activities</p>
                            </div>
                        </div>
                        <button id="close-notifications" class="touch-target p-2 hover:bg-slate-100 rounded-xl transition-colors focus-ring">
                            <i class="ri-close-line text-xl text-slate-600"></i>
                        </button>
                    </div>
                </div>

                <!-- Enhanced Notifications List -->
                <div class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-3 custom-scrollbar" id="notifications-list">
                    @php
                        $notifications = Auth::user()->notifications()->latest()->take(10)->get();
                    @endphp

                    @forelse($notifications as $notification)
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/50 rounded-2xl p-4 hover:shadow-lg transition-all duration-200 {{ $notification->read_at ? 'opacity-75' : 'ring-2 ring-blue-200/50' }}">
                            <div class="flex items-start space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="ri-information-line text-white text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="font-semibold text-slate-800 text-sm truncate">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                        @if(!$notification->read_at)
                                            <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse flex-shrink-0 ml-2"></span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-slate-600 leading-relaxed">{{ $notification->data['message'] ?? '' }}</p>
                                    <div class="flex items-center justify-between mt-3">
                                        <p class="text-xs text-slate-500">{{ $notification->created_at->diffForHumans() }}</p>
                                        @if(!$notification->read_at)
                                            <button onclick="window.markAsRead('{{ $notification->id }}')" class="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors touch-target">
                                                Mark as read
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gradient-to-r from-slate-100 to-slate-200 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner">
                                <i class="ri-notification-off-line text-3xl text-slate-400"></i>
                            </div>
                            <p class="text-slate-500 text-base font-medium">No notifications yet</p>
                            <p class="text-slate-400 text-sm mt-2 max-w-xs mx-auto leading-relaxed">You'll see important updates and alerts here when they arrive</p>
                        </div>
                    @endforelse
                </div>

                <!-- Enhanced Footer Actions -->
                <div class="p-4 border-t border-slate-200/50 bg-gradient-to-t from-slate-50/50 to-transparent">
                    <button onclick="window.markAllAsRead()" class="w-full bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white py-3 px-4 rounded-2xl font-medium transition-all duration-200 shadow-lg hover:shadow-xl touch-target focus-ring">
                        <i class="ri-check-double-line mr-2"></i>
                        Mark All as Read
                    </button>
                </div>
            </div>
        </aside>
    </div>

    <!-- Enhanced JavaScript for Mobile Interactions -->
    @vite('resources/js/app.js')
    <script>
        // Enhanced Mobile Navigation
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const closeSidebar = document.getElementById('close-sidebar');
        const mobileOverlay = document.getElementById('mobile-overlay');

        // Improved mobile menu with better UX
        function openMobileMenu() {
            sidebar.classList.remove('-translate-x-full');
            mobileOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scroll
            sidebar.setAttribute('aria-hidden', 'false');
        }

        function closeMobileMenu() {
            sidebar.classList.add('-translate-x-full');
            mobileOverlay.classList.add('hidden');
            document.body.style.overflow = ''; // Restore scroll
            sidebar.setAttribute('aria-hidden', 'true');
        }

        mobileMenuBtn?.addEventListener('click', openMobileMenu);
        closeSidebar?.addEventListener('click', closeMobileMenu);
        mobileOverlay?.addEventListener('click', closeMobileMenu);

        // Close menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
                closeMobileMenu();
            }
        });

        // Enhanced Notifications System
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
                // Open notifications
                notificationsSidebar.classList.remove('translate-x-full');
                notificationsSidebar.setAttribute('aria-hidden', 'false');
                
                if (window.innerWidth < 1024) {
                    mobileOverlay.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                } else {
                    mainContentWrapper.style.marginRight = `${notificationsSidebar.offsetWidth}px`;
                }
                
                // Focus management for accessibility
                setTimeout(() => {
                    const firstFocusable = notificationsSidebar.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                    firstFocusable?.focus();
                }, 100);
            } else {
                // Close notifications
                notificationsSidebar.classList.add('translate-x-full');
                notificationsSidebar.setAttribute('aria-hidden', 'true');
                
                if (window.innerWidth < 1024) {
                    mobileOverlay.classList.add('hidden');
                    document.body.style.overflow = '';
                } else {
                    mainContentWrapper.style.marginRight = '0';
                }
                
                // Return focus to trigger button
                notificationsBtn?.focus();
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

        // Echo subscription for attendant
        @auth
        try {
            const userId = {{ Auth::id() }};
            window.Echo.private(`attendant.${userId}`)
                .notification((notification) => {
                    updateNotificationsUI([notification], (parseInt(notificationsBadge?.textContent || '0', 10) || 0) + 1);
                });
        } catch (_) {}
        @endauth

        // Initial load
        fetchUnreadNotifications();

        // Enhanced responsive behavior
        function handleResize() {
            const isMobile = window.innerWidth < 1024;
            
            if (!isMobile) {
                // Desktop: ensure sidebar is visible and overlay is hidden
                closeMobileMenu();
                // Close notifications overlay if open on mobile
                if (!notificationsSidebar.classList.contains('translate-x-full')) {
                    mobileOverlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            }
        }

        window.addEventListener('resize', handleResize);

        // Enhanced submenu toggle with better UX
        function toggleSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const icon = document.getElementById(submenuId + '-icon');

            if (submenu && icon) {
                const allSubmenus = [
                    'sessions-submenu', 'plates-submenu', 'tickets-submenu', 'settings-submenu'
                ];

                const isCurrentOpen = !submenu.classList.contains('hidden');

                // Close all other submenus first
                allSubmenus.forEach(menuId => {
                    if (menuId !== submenuId) {
                        const menu = document.getElementById(menuId);
                        const menuIcon = document.getElementById(menuId + '-icon');

                        if (menu && menuIcon) {
                            menu.classList.add('hidden');
                            menuIcon.classList.remove('rotate-180');
                        }
                    }
                });

                // Toggle current submenu
                if (isCurrentOpen) {
                    submenu.classList.add('hidden');
                    icon.classList.remove('rotate-180');
                } else {
                    submenu.classList.remove('hidden');
                    icon.classList.add('rotate-180');
                    
                    // Smooth scroll to ensure submenu is visible
                    setTimeout(() => {
                        submenu.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 100);
                }
            }
        }

        // Enhanced Toast Notification System with better mobile support
        function showToast(message, type = 'success', duration = 5000) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            const color = getToastColor(type);
            const title = getToastTitle(type);
            const icon = getToastIcon(type);
            const currentTime = new Date().toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });

            toast.className = `toast toast-${type} toast-enter bg-white/95 backdrop-blur-sm border border-${color}-200 rounded-2xl p-4 shadow-2xl relative overflow-hidden hover:toast-hover transition-all duration-300 cursor-pointer touch-target`;
            toast.onclick = () => removeToast(toast);
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'polite');

            // Enhanced toast content with better mobile layout
            toast.innerHTML = `
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-${color}-500 to-${getGradientColor(type)}-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg">
                        <i class="${icon} text-white text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-${color}-800 font-semibold text-sm">${title}</h4>
                            <span class="text-${color}-500 text-xs font-medium">${currentTime}</span>
                        </div>
                        <p class="text-${color}-700 text-sm leading-relaxed">${message}</p>
                    </div>
                    <button onclick="event.stopPropagation(); removeToast(this.parentElement)" class="text-${color}-400 hover:text-${color}-600 transition-colors p-2 rounded-xl hover:bg-${color}-50 touch-target focus-ring flex-shrink-0" aria-label="Close notification">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>
                <div class="toast-progress bg-${color}-500"></div>
            `;

            container.appendChild(toast);

            // Auto remove with pause on hover
            let timeoutId = setTimeout(() => removeToast(toast), duration);
            
            toast.addEventListener('mouseenter', () => clearTimeout(timeoutId));
            toast.addEventListener('mouseleave', () => {
                timeoutId = setTimeout(() => removeToast(toast), 2000);
            });
        }

        function removeToast(toast) {
            if (!toast || !toast.parentNode) return;
            
            toast.classList.remove('toast-enter');
            toast.classList.add('toast-exit');
            
            const progressBar = toast.querySelector('.toast-progress');
            if (progressBar) {
                progressBar.style.animation = 'none';
            }

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 400);
        }

        function getToastColor(type) {
            const colors = {
                'success': 'green',
                'error': 'red',
                'warning': 'yellow',
                'info': 'blue'
            };
            return colors[type] || 'blue';
        }

        function getGradientColor(type) {
            const gradients = {
                'success': 'emerald',
                'error': 'pink',
                'warning': 'orange',
                'info': 'indigo'
            };
            return gradients[type] || 'indigo';
        }

        function getToastTitle(type) {
            const titles = {
                'success': 'Success',
                'error': 'Error',
                'warning': 'Warning',
                'info': 'Information'
            };
            return titles[type] || 'Information';
        }

        function getToastIcon(type) {
            const icons = {
                'success': 'ri-check-line',
                'error': 'ri-error-warning-line',
                'warning': 'ri-alert-line',
                'info': 'ri-information-line'
            };
            return icons[type] || 'ri-information-line';
        }

        // Enhanced Performance and UX Features
        
        // Lazy loading for better performance
        function setupLazyLoading() {
            const images = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('loading-shimmer');
                        observer.unobserve(img);
                    }
                });
            });
            images.forEach(img => imageObserver.observe(img));
        }

        // Enhanced keyboard navigation
        function setupKeyboardNavigation() {
            document.addEventListener('keydown', (e) => {
                // Alt + M for mobile menu
                if (e.altKey && e.key === 'm') {
                    e.preventDefault();
                    if (window.innerWidth < 1024) {
                        if (sidebar.classList.contains('-translate-x-full')) {
                            openMobileMenu();
                        } else {
                            closeMobileMenu();
                        }
                    }
                }
                
                // Alt + N for notifications
                if (e.altKey && e.key === 'n') {
                    e.preventDefault();
                    toggleNotifications();
                }
                
                // Escape to close any open overlays
                if (e.key === 'Escape') {
                    if (!sidebar.classList.contains('-translate-x-full')) {
                        closeMobileMenu();
                    }
                    if (!notificationsSidebar.classList.contains('translate-x-full')) {
                        toggleNotifications();
                    }
                }
            });
        }

        // Performance optimization: Debounced resize handler
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        const debouncedResize = debounce(handleResize, 100);
        window.addEventListener('resize', debouncedResize);

        // Initialize enhanced features
        document.addEventListener('DOMContentLoaded', () => {
            setupLazyLoading();
            setupKeyboardNavigation();
            
            // Add loading states
            const buttons = document.querySelectorAll('button[type="submit"], a[href*="create"], a[href*="edit"]');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    if (!this.classList.contains('loading')) {
                        this.classList.add('loading');
                        const originalContent = this.innerHTML;
                        this.innerHTML = '<i class="ri-loader-4-line animate-spin mr-2"></i>Loading...';
                        
                        // Reset after 3 seconds as fallback
                        setTimeout(() => {
                            this.classList.remove('loading');
                            this.innerHTML = originalContent;
                        }, 3000);
                    }
                });
            });
        });

        // Global functions
        window.showToast = showToast;
        window.removeToast = removeToast;
        window.toggleSubmenu = toggleSubmenu;
    </script>
</body>
</html>

