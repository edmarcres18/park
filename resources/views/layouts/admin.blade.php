<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        },
                        'sidebar': {
                            50: '#f8fafc',
                            900: '#0f172a',
                            800: '#1e293b'
                        }
                    },
                    animation: {
                        'slide-in': 'slide-in 0.3s ease-out',
                        'fade-in': 'fade-in 0.2s ease-out'
                    }
                }
            }
        }
    </script>
    <style>
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
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
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

        /* Responsive Design Improvements */
        @media (max-width: 640px) {
            .sidebar-text { display: none; }
            aside { width: 4rem !important; }
            nav a, nav button { justify-content: center !important; padding: 0.75rem !important; }
            nav a span, nav button span { display: none; }
            nav .ml-auto, nav .mr-4 { display: none; }
            .glass-effect { backdrop-filter: blur(5px); }
        }

        @media (max-width: 768px) {
            .header-search { display: none !important; }
            .profile-info { display: none !important; }
            h1 { font-size: 1.5rem !important; }
            .main-content { padding: 1rem !important; }
        }

        @media (max-width: 1024px) {
            .lg\:hidden { display: block !important; }
            .lg\:translate-x-0 { transform: translateX(-100%) !important; }
            .notifications-sidebar { width: 100vw !important; }
        }

        @media (min-width: 1025px) {
            #sidebar { transform: translateX(0) !important; }
        }

        /* Flexible Grid System */
        .responsive-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        /* Touch-friendly buttons for mobile */
        @media (max-width: 768px) {
            button, .btn { min-height: 44px; min-width: 44px; }
        }

        /* Improved scrollbars for mobile */
        @media (max-width: 768px) {
            .overflow-auto::-webkit-scrollbar { display: none; }
            .overflow-auto { -ms-overflow-style: none; scrollbar-width: none; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 font-sans antialiased overflow-hidden lg:overflow-auto">
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
    <div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

    <div class="flex h-screen relative">
        <!-- Left Sidebar -->
        <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-64 sm:w-72 bg-gradient-to-b from-sidebar-900 to-sidebar-800 text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out shadow-2xl flex flex-col">
            <!-- Logo Section -->
            <div class="flex items-center justify-between p-6 border-b border-slate-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center overflow-hidden">
                        @if(!empty($siteSettings->brand_logo))
                            <img src="{{ asset('storage/' . $siteSettings->brand_logo) }}" alt="Brand Logo" class="w-10 h-10 object-contain rounded-xl">
                        @else
                            <i class="ri-admin-line text-xl text-white"></i>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">{{ $siteSettings->app_name ?? config('app.name', 'ParkSmart') }}</h1>
                        <p class="text-xs text-slate-400">Admin Control</p>
                    </div>
                </div>
                <button id="close-sidebar" class="lg:hidden text-slate-400 hover:text-white transition-colors">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>

            <!-- Navigation Menu -->
            <nav class="mt-6 px-4 space-y-1 flex-1 overflow-y-auto pb-20">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" class="group flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                    <i class="ri-dashboard-3-line text-xl mr-4"></i>
                    <span class="font-medium">Dashboard</span>
                    <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <i class="ri-arrow-right-s-line"></i>
                    </div>
                </a>

                <!-- Plate Records -->
                <div class="space-y-1">
                    <div class="group">
                    <button class="w-full flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.plates.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}" onclick="toggleSubmenu('plate-submenu')">
                            <i class="ri-car-line text-xl mr-4"></i>
                            <span class="font-medium">Plate Records</span>
                            <div class="ml-auto">
                                <i class="ri-arrow-down-s-line transform transition-transform duration-200 {{ request()->routeIs('admin.plates.*') ? 'rotate-180' : '' }}" id="plate-submenu-icon"></i>
                            </div>
                        </button>
                        <div id="plate-submenu" class="ml-8 space-y-1 mt-2 {{ request()->routeIs('admin.plates.*') ? '' : 'hidden' }}">
                            <a href="{{ route('admin.plates.index') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.plates.index') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-eye-line text-sm mr-3"></i>
                                <span class="text-sm">View All Plates</span>
                            </a>
                            <a href="{{ route('admin.plates.create') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.plates.create') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-add-line text-sm mr-3"></i>
                                <span class="text-sm">Add New Plate</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Parking Rates -->
                <div class="space-y-1">
                    <div class="group">
                        <button class="w-full flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.rates.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}" onclick="toggleSubmenu('rates-submenu')">
                            <i class="ri-settings-2-line text-xl mr-4"></i>
                            <span class="font-medium">Parking Rates</span>
                            <div class="ml-auto">
                                <i class="ri-arrow-down-s-line transform transition-transform duration-200 {{ request()->routeIs('admin.rates.*') ? 'rotate-180' : '' }}" id="rates-submenu-icon"></i>
                            </div>
                        </button>
                        <div id="rates-submenu" class="ml-8 space-y-1 mt-2 {{ request()->routeIs('admin.rates.*') ? '' : 'hidden' }}">
                            <a href="{{ route('admin.rates.index') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.rates.index') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-list-check text-sm mr-3"></i>
                                <span class="text-sm">View All Rates</span>
                            </a>
                            <a href="{{ route('admin.rates.create') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.rates.create') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-add-line text-sm mr-3"></i>
                                <span class="text-sm">Add New Rate</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Parking Sessions -->
                <div class="space-y-1">
                    <div class="group">
                        <button class="w-full flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.sessions.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}" onclick="toggleSubmenu('sessions-submenu')">
                            <i class="ri-time-line text-xl mr-4"></i>
                            <span class="font-medium">Parking Sessions</span>
                            <div class="ml-auto">
                                <i class="ri-arrow-down-s-line transform transition-transform duration-200 {{ request()->routeIs('admin.sessions.*') ? 'rotate-180' : '' }}" id="sessions-submenu-icon"></i>
                            </div>
                        </button>
                        <div id="sessions-submenu" class="ml-8 space-y-1 mt-2 {{ request()->routeIs('admin.sessions.*') ? '' : 'hidden' }}">
                            <a href="{{ route('admin.sessions.index') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.sessions.index') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-list-check text-sm mr-3"></i>
                                <span class="text-sm">All Sessions</span>
                            </a>
                            <a href="{{ route('admin.sessions.create') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.sessions.create') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-add-line text-sm mr-3"></i>
                                <span class="text-sm">Start New Session</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Ticketing -->
                <div class="space-y-1">
                    <div class="group">
                        <button class="w-full flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.tickets.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}" onclick="toggleSubmenu('ticketing-submenu')">
                            <i class="ri-ticket-2-line text-xl mr-4"></i>
                            <span class="font-medium">Ticketing</span>
                            <div class="ml-auto">
                                <i class="ri-arrow-down-s-line transform transition-transform duration-200 {{ request()->routeIs('admin.tickets.*') ? 'rotate-180' : '' }}" id="ticketing-submenu-icon"></i>
                            </div>
                        </button>
                        <div id="ticketing-submenu" class="ml-8 space-y-1 mt-2 {{ request()->routeIs('admin.tickets.*') ? '' : 'hidden' }}">
                            <a href="{{ route('admin.tickets.index') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.tickets.index') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-list-check text-sm mr-3"></i>
                                <span class="text-sm">All Tickets</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Reports & Sales -->
                <div class="space-y-1">
                    <div class="group">
                        <button class="w-full flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}" onclick="toggleSubmenu('reports-submenu')">
                            <i class="ri-bar-chart-2-line text-xl mr-4"></i>
                            <span class="font-medium">Reports & Sales</span>
                            <div class="ml-auto">
                                <i class="ri-arrow-down-s-line transform transition-transform duration-200 {{ request()->routeIs('admin.reports.*') ? 'rotate-180' : '' }}" id="reports-submenu-icon"></i>
                            </div>
                        </button>
                        <div id="reports-submenu" class="ml-8 space-y-1 mt-2 {{ request()->routeIs('admin.reports.*') ? '' : 'hidden' }}">
                            <a href="{{ route('admin.reports.sales') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.reports.sales') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-calendar-line text-sm mr-3"></i>
                                <span class="text-sm">Daily Sales</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Management -->
                <div class="space-y-1">
                    <div class="group">
                        <button class="w-full flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}" onclick="toggleSubmenu('users-submenu')">
                            <i class="ri-team-line text-xl mr-4"></i>
                            <span class="font-medium">User Management</span>
                            <div class="ml-auto">
                                <i class="ri-arrow-down-s-line transform transition-transform duration-200 {{ request()->routeIs('admin.users.*') ? 'rotate-180' : '' }}" id="users-submenu-icon"></i>
                            </div>
                        </button>
                        <div id="users-submenu" class="ml-8 space-y-1 mt-2 {{ request()->routeIs('admin.users.*') ? '' : 'hidden' }}">
                            <a href="{{ route('admin.users.create') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.users.create') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-user-add-line text-sm mr-3"></i>
                                <span class="text-sm">Create New User</span>
                            </a>
                            <a href="{{ route('admin.users.pending') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.users.pending') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-user-add-line text-sm mr-3"></i>
                                <span class="text-sm">Pending Attendants</span>
                                @php
                                    $pendingCount = \App\Models\User::getPendingAttendantsCount();
                                @endphp
                                @if($pendingCount > 0)
                                    <span class="ml-auto bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">{{ $pendingCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.users.index') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-user-line text-sm mr-3"></i>
                                <span class="text-sm">Active Attendants</span>
                                @php
                                    $activeCount = \App\Models\User::getActiveAttendantsCount();
                                @endphp
                                @if($activeCount > 0)
                                    <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full">{{ $activeCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.users.rejected') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.users.rejected') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-user-forbid-line text-sm mr-3"></i>
                                <span class="text-sm">Rejected Registrations</span>
                                @php
                                    $rejectedCount = \App\Models\User::getRejectedAttendantsCount();
                                @endphp
                                @if($rejectedCount > 0)
                                    <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $rejectedCount }}</span>
                                @endif
                            </a>
                        </div>
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
                <div class="space-y-1">
                    <div class="group">
                        <button class="w-full flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-gradient-to-r hover:from-blue-600 hover:to-purple-600 rounded-xl transition-all duration-200 {{ request()->routeIs('profile.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.activity-logs.*') || request()->routeIs('admin.location-monitor.*') || request()->routeIs('admin.ticket-config.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}"  onclick="toggleSubmenu('settings-submenu')">
                            <i class="ri-settings-3-line text-xl mr-4"></i>
                            <span class="font-medium">Settings</span>
                            <div class="ml-auto">
                                <i class="ri-arrow-down-s-line transform transition-transform duration-200 {{ request()->routeIs('profile.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.activity-logs.*') || request()->routeIs('admin.location-monitor.*') || request()->routeIs('admin.ticket-config.*') ? 'rotate-180' : '' }}" id="settings-submenu-icon"></i>
                            </div>
                        </button>
                        <div id="settings-submenu" class="ml-8 space-y-1 mt-2 {{ request()->routeIs('profile.*') || request()->routeIs('admin.settings.*') || request()->routeIs('admin.activity-logs.*') || request()->routeIs('admin.location-monitor.*') || request()->routeIs('admin.ticket-config.*') ? '' : 'hidden' }}">
                            <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('profile.edit') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-user-settings-line text-sm mr-3"></i>
                                <span class="text-sm">My Profile</span>
                            </a>
                            <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.settings.index') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-apps-line text-sm mr-3"></i>
                                <span class="text-sm">Site Settings</span>
                            </a>
                            <a href="{{ route('admin.settings.create') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.settings.create') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-add-line text-sm mr-3"></i>
                                <span class="text-sm">Add Setting</span>
                            </a>
                            <a href="{{ route('admin.ticket-config.edit') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.ticket-config.edit') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-ticket-line text-sm mr-3"></i>
                                <span class="text-sm">Ticket Config</span>
                            </a>
                            <a href="{{ route('admin.activity-logs.index') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.activity-logs.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-history-line text-sm mr-3"></i>
                                <span class="text-sm">Activity Logs</span>
                            </a>
                            <a href="{{ route('admin.location-monitor.index') }}" class="flex items-center px-4 py-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.location-monitor.*') ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : '' }}">
                                <i class="ri-map-pin-line text-sm mr-3"></i>
                                <span class="text-sm">Location Monitor</span>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Bottom Section -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-700">
                <div class="flex items-center space-x-3 mb-4">
                    <img src="https://ui-avatars.com/api/?name=Admin+User&background=3b82f6&color=fff&rounded=true" alt="Admin" class="w-10 h-10 rounded-full ring-2 ring-blue-500">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white">{{ Auth::user()->name ?? 'Admin User' }}</p>
                        <p class="text-xs text-slate-400">Administrator</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200">
                        <i class="ri-logout-box-line mr-2"></i>
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
<div id="main-content-wrapper" class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out">
            <!-- Top Header -->
            <header class="glass-effect border-b border-white/20 px-4 lg:px-8 py-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg bg-white/50 hover:bg-white/80 transition-colors">
                            <i class="ri-menu-line text-xl text-slate-700"></i>
                        </button>
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-bold text-slate-800">@yield('title', 'Dashboard')</h1>
                            <p class="text-sm text-slate-600 mt-1">@yield('subtitle', 'Welcome back, manage your parking system')</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <div class="hidden md:block relative header-search">
                            <input type="text" placeholder="Search..." class="w-64 lg:w-72 xl:w-80 pl-10 pr-4 py-2 bg-white/60 border border-white/40 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <i class="ri-search-line absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        </div>

                        <!-- Notifications -->
                        <button id="notifications-btn" class="relative p-2 bg-white/60 hover:bg-white/80 rounded-xl transition-colors">
                            <i class="ri-notification-3-line text-xl text-slate-700"></i>
                            @php
                                $unreadNotifications = Auth::user()->unreadNotifications;
                                $unreadCount = $unreadNotifications->count();
                            @endphp
                            <span id="notifications-badge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center {{ $unreadCount > 0 ? '' : 'hidden' }}">{{ $unreadCount }}</span>
                        </button>

                        <!-- Profile -->
                        <div class="hidden md:flex items-center space-x-3 bg-white/60 rounded-xl px-3 py-2 profile-info">
                            <img src="https://ui-avatars.com/api/?name=Admin+User&background=3b82f6&color=fff&rounded=true" alt="Admin" class="w-8 h-8 rounded-full">
                            <div class="text-sm">
                                <p class="font-medium text-slate-800">{{ Auth::user()->name ?? 'Admin User' }}</p>
                                <p class="text-slate-600">Administrator</p>
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

        <!-- Right Sidebar for Notifications -->
        <aside id="notifications-sidebar" class="fixed inset-y-0 right-0 z-50 w-80 bg-white/95 backdrop-blur-lg border-l border-white/20 shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out lg:w-96">
            <div class="h-full flex flex-col">
                <!-- Header -->
                <div class="p-6 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-slate-800">Notifications</h2>
                        <button id="close-notifications" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                            <i class="ri-close-line text-xl text-slate-600"></i>
                        </button>
                    </div>
                    <p class="text-sm text-slate-600 mt-1">Stay updated with recent activities</p>
                </div>

                <!-- Notifications List -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4" id="notifications-list">
                    @php
                        $notifications = Auth::user()->notifications()->latest()->take(10)->get();
                    @endphp

                    @forelse($notifications as $notification)
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 hover:shadow-md transition-shadow {{ $notification->read_at ? 'opacity-75' : '' }}">
                            <div class="flex items-start space-x-3">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="ri-user-add-line text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="font-semibold text-slate-800">New User Registered</p>
                                        @if(!$notification->read_at)
                                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-slate-600 mt-1">
                                        {{ $notification->data['name'] ?? 'Unknown User' }} joined the platform
                                    </p>
                                    <p class="text-xs text-slate-500 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                                    @if(!$notification->read_at)
                                        <button onclick="window.markAsRead('{{ $notification->id }}')" class="text-xs text-blue-600 hover:text-blue-800 underline mt-2">
                                            Mark as read
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="ri-notification-off-line text-2xl text-slate-400"></i>
                            </div>
                            <p class="text-slate-500 text-sm">No notifications yet</p>
                            <p class="text-slate-400 text-xs mt-1">You'll see notifications here when they arrive</p>
                        </div>
                    @endforelse
                </div>

                <!-- Footer Actions -->
                <div class="p-4 border-t border-slate-200">
                    <a href="{{ route('admin.notifications.index') }}" class="block w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-2 px-4 rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium text-center">
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

