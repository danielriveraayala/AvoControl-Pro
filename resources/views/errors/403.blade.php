@extends('layouts.app')

@section('title', 'Acceso Denegado')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo/Icon -->
        <div class="flex justify-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m8-7V9a4 4 0 00-8 0v2m0 0V9a4 4 0 014-4 4 4 0 014 4v2m-8 0h8a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6a2 2 0 012-2z"></path>
                </svg>
            </div>
        </div>
        
        <!-- Title -->
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Acceso Denegado
        </h2>
        
        <!-- Error Code -->
        <p class="mt-2 text-center text-sm text-gray-600">
            Error 403
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <!-- Message -->
            <div class="text-center">
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        Permisos Insuficientes
                    </h3>
                    <p class="text-sm text-gray-600">
                        No tienes los permisos necesarios para acceder a esta página o realizar esta acción.
                    </p>
                </div>

                <!-- User Info -->
                @auth
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">Usuario:</span> {{ auth()->user()->name }}
                    </p>
                    <p class="text-sm text-gray-700 mt-1">
                        <span class="font-medium">Roles:</span> 
                        @if(auth()->user()->roles->count() > 0)
                            {{ auth()->user()->roles->pluck('display_name')->join(', ') }}
                        @else
                            {{ ucfirst(auth()->user()->role) }}
                        @endif
                    </p>
                </div>
                @endauth

                <!-- Action Buttons -->
                <div class="space-y-3">
                    <!-- Dashboard Button -->
                    <a href="{{ route('dashboard') }}" 
                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Ir al Dashboard
                    </a>

                    <!-- Go Back Button -->
                    <button onclick="window.history.back()" 
                            class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Regresar
                    </button>

                    <!-- Contact Admin (if not admin) -->
                    @auth
                    @if(!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole('admin'))
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 mb-2">
                            ¿Necesitas acceso a esta función?
                        </p>
                        <p class="text-xs text-gray-400">
                            Contacta al administrador del sistema para solicitar permisos adicionales.
                        </p>
                    </div>
                    @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection