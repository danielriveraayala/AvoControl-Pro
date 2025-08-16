<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Developer Panel')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-code text-xl md:text-2xl text-indigo-600 mr-2 md:mr-3"></i>
                        <h1 class="text-lg md:text-xl font-bold text-gray-900 truncate">
                            <span class="hidden lg:inline">Developer Panel</span>
                            <span class="lg:hidden">Dev Panel</span>
                        </h1>
                    </div>

                    <!-- Navigation Links (Desktop & Tablet) -->
                    <div class="hidden lg:ml-10 lg:flex lg:space-x-4 xl:space-x-6">
                        <a href="{{ route('developer.index') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm {{ request()->routeIs('developer.index') ? 'border-indigo-500 text-indigo-600' : '' }}">
                            Dashboard
                        </a>

                        <!-- Management Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center {{ request()->routeIs('developer.users.*') || request()->routeIs('developer.roles.*') || request()->routeIs('developer.tenants.*') ? 'border-indigo-500 text-indigo-600' : '' }}">
                                Management
                                <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                <div class="py-1">
                                    <a href="{{ route('developer.users.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('developer.users.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                        <i class="fas fa-users mr-2"></i>Users
                                    </a>
                                    <a href="{{ route('developer.roles.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('developer.roles.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                        <i class="fas fa-user-shield mr-2"></i>Roles
                                    </a>
                                    <a href="{{ route('developer.tenants.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('developer.tenants.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                        <i class="fas fa-building mr-2"></i>Tenants
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center {{ request()->routeIs('developer.subscriptions.*') || request()->routeIs('developer.paypal.*') || request()->routeIs('developer.billing.*') || request()->routeIs('developer.plans.*') ? 'border-indigo-500 text-indigo-600' : '' }}">
                                Billing
                                <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                <div class="py-1">
                                    <a href="{{ route('developer.plans.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('developer.plans.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                        <i class="fas fa-layer-group mr-2"></i>Gestión de Planes
                                    </a>
                                    <a href="{{ route('developer.subscriptions.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('developer.subscriptions.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                        <i class="fas fa-credit-card mr-2"></i>Suscripciones
                                    </a>
                                    <a href="{{ route('developer.paypal.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('developer.paypal.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                        <i class="fab fa-paypal mr-2"></i>PayPal Config
                                    </a>
                                    <a href="{{ route('developer.billing.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('developer.billing.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                        <i class="fas fa-chart-line mr-2"></i>Métricas
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- System Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center {{ request()->routeIs('developer.config.*') || request()->routeIs('developer.backups.*') || request()->routeIs('developer.logs') ? 'border-indigo-500 text-indigo-600' : '' }}">
                                System
                                <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                <div class="py-1">
                                    <a href="{{ route('developer.config.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('developer.config.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                        <i class="fas fa-cog mr-2"></i>Config
                                    </a>
                                    <a href="{{ route('developer.backups.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('developer.backups.*') ? 'bg-gray-100 text-gray-900' : '' }}">
                                        <i class="fas fa-database mr-2"></i>Backups
                                    </a>
                                    <a href="{{ route('developer.logs') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('developer.logs') ? 'bg-gray-100 text-gray-900' : '' }}">
                                        <i class="fas fa-file-alt mr-2"></i>Logs
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <!-- Mobile/Tablet Menu Button -->
                    <button type="button" class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" id="mobile-menu-button">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- System Status -->
                    <div class="hidden xl:flex items-center space-x-2">
                        @php
                            $maintenanceActive = \Cache::get('frontend_maintenance', false);
                        @endphp

                        @if($maintenanceActive)
                            <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                            <span class="text-sm text-yellow-600 font-medium">Frontend Maintenance</span>
                        @else
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-500">System Online</span>
                        @endif
                    </div>

                    <!-- User Menu -->
                    <div class="relative">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div class="text-right hidden sm:block">
                                <div class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-gray-500">Super Admin</div>
                            </div>
                            <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Back to App -->
                    <a href="{{ route('dashboard') }}" class="hidden md:inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-arrow-left mr-1 md:mr-2"></i>
                        <span class="hidden xl:inline">Back to App</span>
                        <span class="xl:hidden">App</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile/Tablet Menu -->
        <div class="lg:hidden" id="mobile-menu" style="display: none;">
            <div class="px-2 pt-2 pb-3 space-y-1 md:px-3 bg-gray-50 border-t border-gray-200 max-h-screen overflow-y-auto">
                <a href="{{ route('developer.index') }}" class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('developer.index') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                    <i class="fas fa-tachometer-alt mr-3 w-5 text-center"></i>Dashboard
                </a>
                <a href="{{ route('developer.users.index') }}" class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('developer.users.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                    <i class="fas fa-users mr-3 w-5 text-center"></i>Users
                </a>
                <a href="{{ route('developer.roles.index') }}" class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('developer.roles.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                    <i class="fas fa-user-shield mr-3 w-5 text-center"></i>Roles
                </a>
                <a href="{{ route('developer.config.index') }}" class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('developer.config.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                    <i class="fas fa-cog mr-3 w-5 text-center"></i>Config
                </a>
                <a href="{{ route('developer.tenants.index') }}" class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('developer.tenants.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                    <i class="fas fa-building mr-3 w-5 text-center"></i>Tenants
                </a>

                <!-- Billing Section -->
                <div class="border-t border-gray-200 pt-2 mt-2">
                    <div class="px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Billing</div>
                    <a href="{{ route('developer.plans.index') }}" class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('developer.plans.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-layer-group mr-3 w-5 text-center"></i>Gestión de Planes
                    </a>
                    <a href="{{ route('developer.subscriptions.index') }}" class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('developer.subscriptions.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-credit-card mr-3 w-5 text-center"></i>Suscripciones
                    </a>
                    <a href="{{ route('developer.paypal.index') }}" class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('developer.paypal.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                        <i class="fab fa-paypal mr-3 w-5 text-center"></i>PayPal Config
                    </a>
                    <a href="{{ route('developer.billing.index') }}" class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('developer.billing.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-chart-line mr-3 w-5 text-center"></i>Métricas
                    </a>
                </div>

                <!-- System Section -->
                <div class="border-t border-gray-200 pt-2 mt-2">
                    <div class="px-3 py-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">System</div>
                    <a href="{{ route('developer.config.index') }}" class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('developer.config.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-cog mr-3 w-5 text-center"></i>Config
                    </a>
                    <a href="{{ route('developer.backups.index') }}" class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('developer.backups.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-database mr-3 w-5 text-center"></i>Backups
                    </a>
                    <a href="{{ route('developer.logs') }}" class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('developer.logs') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                        <i class="fas fa-file-alt mr-3 w-5 text-center"></i>Logs
                    </a>
                </div>
                <div class="border-t border-gray-200 pt-3">
                    <!-- System Status (Mobile) -->
                    <div class="flex items-center justify-center py-2 mb-2">
                        @php
                            $maintenanceActive = \Cache::get('frontend_maintenance', false);
                        @endphp

                        @if($maintenanceActive)
                            <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse mr-2"></div>
                            <span class="text-sm text-yellow-600 font-medium">Frontend Maintenance</span>
                        @else
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse mr-2"></div>
                            <span class="text-sm text-gray-500">System Online</span>
                        @endif
                    </div>

                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-base font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md">
                        <i class="fas fa-arrow-left mr-3 w-5 text-center"></i>Back to App
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen">
        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: '{{ session('success') }}',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                });
            </script>
        @endif

        @if(session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: '{{ session('error') }}',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#ef4444'
                    });
                });
            </script>
        @endif

        @if(session('warning'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia',
                        text: '{{ session('warning') }}',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#f59e0b'
                    });
                });
            </script>
        @endif

        @if(session('info'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'info',
                        title: 'Información',
                        text: '{{ session('info') }}',
                        timer: 4000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                });
            </script>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-8 sm:mt-12">
        <div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col space-y-4 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
                <div class="text-xs sm:text-sm text-gray-500 text-center sm:text-left">
                    &copy; {{ date('Y') }} AvoControl Pro. Developer Panel by Daniel Rivera Ayala.
                </div>
                <div class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4 text-xs sm:text-sm text-gray-500 text-center sm:text-right">
                    <span>Laravel {{ app()->version() }}</span>
                    <span>PHP {{ PHP_VERSION }}</span>
                    <span>Built with ❤️</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')

    <!-- Mobile Menu Toggle Script ---->
    <script>
        // Mobile menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    const isHidden = mobileMenu.style.display === 'none';
                    if (isHidden) {
                        mobileMenu.style.display = 'block';
                        // Change hamburger to X icon
                        this.innerHTML = `
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        `;
                    } else {
                        mobileMenu.style.display = 'none';
                        // Change back to hamburger icon
                        this.innerHTML = `
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        `;
                    }
                });
            }
        });
    </script>

    <!-- Global SweetAlert Functions -->
    <script>
        // Global SweetAlert helper functions
        window.DevAlert = {
            success: function(title, text = '', timer = 3000) {
                return Swal.fire({
                    icon: 'success',
                    title: title,
                    text: text,
                    timer: timer,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            },

            error: function(title, text = '') {
                return Swal.fire({
                    icon: 'error',
                    title: title,
                    text: text,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#ef4444'
                });
            },

            warning: function(title, text = '') {
                return Swal.fire({
                    icon: 'warning',
                    title: title,
                    text: text,
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#f59e0b'
                });
            },

            info: function(title, text = '', timer = 4000) {
                return Swal.fire({
                    icon: 'info',
                    title: title,
                    text: text,
                    timer: timer,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            },

            confirm: function(title, text = '', confirmText = 'Sí, continuar', cancelText = 'Cancelar') {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: confirmText,
                    cancelButtonText: cancelText
                });
            },

            confirmDanger: function(title, text = '', confirmText = 'Sí, eliminar', cancelText = 'Cancelar') {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: confirmText,
                    cancelButtonText: cancelText
                });
            },

            loading: function(title = 'Procesando...', text = 'Por favor espera') {
                return Swal.fire({
                    title: title,
                    text: text,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },

            close: function() {
                Swal.close();
            }
        };

        // Helper function for AJAX responses
        window.handleAjaxResponse = function(response) {
            if (response.success) {
                DevAlert.success('¡Éxito!', response.message);
            } else {
                DevAlert.error('Error', response.message);
            }
        };

        // Helper function for fetch errors
        window.handleFetchError = function(error) {
            console.error('Error:', error);
            DevAlert.error('Error de conexión', 'No se pudo conectar con el servidor. Por favor, intenta de nuevo.');
        };
    </script>

    @stack('scripts')
</body>
</html>
