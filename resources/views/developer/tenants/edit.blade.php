@extends('layouts.developer')

@section('title', 'Editar Tenant')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col space-y-4 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 sm:h-12 sm:w-12">
                            <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-gradient-to-r from-indigo-500 to-blue-600 flex items-center justify-center">
                                <span class="text-sm sm:text-lg font-bold text-white">
                                    {{ substr($tenant->name, 0, 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4 min-w-0">
                            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">Editar Tenant</h1>
                            <p class="text-xs sm:text-sm text-gray-600 truncate">{{ $tenant->name }} - {{ $tenant->slug }}.avocontrol.pro</p>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.tenants.show', $tenant) }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver a Detalles
                        </a>
                        <a href="{{ route('developer.tenants.index') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            Lista de Tenants
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Banner -->
        @if($tenant->status == 'suspended')
            <div class="bg-red-100 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Tenant Suspendido</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>⚠️ Este tenant está actualmente suspendido. Los cambios se aplicarán pero el tenant permanecerá suspendido hasta ser reactivado.</p>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($tenant->plan == 'trial')
            <div class="bg-yellow-100 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Plan de Prueba</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>⏰ Este tenant está en período de prueba.
                            @if($tenant->trial_ends_at)
                                Expira el {{ $tenant->trial_ends_at->format('d/m/Y') }}
                            @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Edit Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('developer.tenants.update', $tenant) }}" method="POST" id="tenantForm">
                @csrf
                @method('PUT')
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Información del Tenant
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Modifica los datos básicos del tenant y configuración</p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column: Basic Information -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-4 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Información Básica
                                </h4>
                                
                                <!-- Name -->
                                <div class="mb-4">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m5 0v-5a2 2 0 00-2-2H8a2 2 0 00-2 2v5m3 0h4z"></path>
                                        </svg>
                                        Nombre de la Empresa
                                    </label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $tenant->name) }}" 
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                                           placeholder="Nombre de la empresa..." required>
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Slug -->
                                <div class="mb-4">
                                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                        </svg>
                                        Identificador Único (Slug)
                                    </label>
                                    <input type="text" name="slug" id="slug" value="{{ old('slug', $tenant->slug) }}" 
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono" 
                                           placeholder="identificador-unico" required>
                                    <div class="mt-1 flex items-center text-xs text-gray-500">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        URL: <span class="font-mono font-medium" id="slugPreview">{{ $tenant->slug }}</span>.avocontrol.pro
                                    </div>
                                    @error('slug')
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                        </svg>
                                        Email de Contacto
                                    </label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $tenant->email) }}" 
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                                           placeholder="contacto@empresa.com" required>
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    Teléfono (Opcional)
                                </label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $tenant->phone) }}" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                                       placeholder="+52 443 123 4567">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Dirección Física (Opcional)
                                </label>
                                <textarea name="address" id="address" rows="3" 
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                                          placeholder="Calle, número, colonia, ciudad, estado...">{{ old('address', $tenant->address) }}</textarea>
                                @error('address')
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Right Column: Plan & Status -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-4 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Plan y Estado
                                </h4>

                                <!-- Plan -->
                                <div class="mb-4">
                                    <label for="plan" class="block text-sm font-medium text-gray-700 mb-1">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m5 0v-5a2 2 0 00-2-2H8a2 2 0 00-2 2v5m3 0h4z"></path>
                                        </svg>
                                        Plan de Suscripción
                                    </label>
                                    <select name="plan" id="plan" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan }}" {{ old('plan', $tenant->plan) == $plan ? 'selected' : '' }}>
                                                @if($plan == 'trial')
                                                    🆓 Prueba (7 días gratis)
                                                @elseif($plan == 'basic')
                                                    🥉 Básico ($29/mes)
                                                @elseif($plan == 'premium')
                                                    🥈 Premium ($79/mes)
                                                @elseif($plan == 'enterprise')
                                                    🥇 Enterprise ($199/mes)
                                                @elseif($plan == 'corporate')
                                                    🏢 Corporate (Personalizado)
                                                @else
                                                    {{ ucfirst($plan) }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('plan')
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Estado del Tenant
                                    </label>
                                    <select name="status" id="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                        <option value="active" {{ old('status', $tenant->status) == 'active' ? 'selected' : '' }}>
                                            ✅ Activo (Operativo)
                                        </option>
                                        <option value="inactive" {{ old('status', $tenant->status) == 'inactive' ? 'selected' : '' }}>
                                            ⏸️ Inactivo (Pausado)
                                        </option>
                                        <option value="suspended" {{ old('status', $tenant->status) == 'suspended' ? 'selected' : '' }}>
                                            ⛔ Suspendido (Bloqueado)
                                        </option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Current Stats -->
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-4 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Estadísticas Actuales
                                </h4>
                                
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">👥 Usuarios registrados:</span>
                                        <span class="font-medium text-gray-900">{{ $tenant->users()->count() }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">📊 Suscripciones:</span>
                                        <span class="font-medium text-gray-900">{{ $tenant->subscriptions()->count() }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">📅 Creado:</span>
                                        <span class="font-medium text-gray-900">{{ $tenant->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    @if($tenant->trial_ends_at)
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">⏰ Trial expira:</span>
                                        <span class="font-medium {{ $tenant->trial_ends_at->isPast() ? 'text-red-600' : 'text-yellow-600' }}">
                                            {{ $tenant->trial_ends_at->format('d/m/Y') }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex flex-col space-y-3 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
                        <!-- Left side - Additional Actions -->
                        <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3">
                            <a href="http://{{ $tenant->slug }}.avocontrol.pro" target="_blank" 
                               class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                Ver Sitio
                            </a>
                            
                            <button type="button" onclick="resetForm()" 
                                    class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reestablecer
                            </button>
                        </div>

                        <!-- Right side - Primary Actions -->
                        <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3">
                            <a href="{{ route('developer.tenants.show', $tenant) }}" 
                               class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancelar
                            </a>
                            
                            <button type="submit" id="submitBtn"
                                    class="inline-flex items-center justify-center px-4 sm:px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span id="submitText">Actualizar Tenant</span>
                                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white hidden" id="submitSpinner" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Update slug preview in real-time
document.getElementById('slug').addEventListener('input', function(e) {
    document.getElementById('slugPreview').textContent = e.target.value || '{{ $tenant->slug }}';
});

// Form submission handling
document.getElementById('tenantForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    
    // Show loading state
    submitBtn.disabled = true;
    submitText.textContent = 'Actualizando...';
    submitSpinner.classList.remove('hidden');
    
    // Prepare form data
    const formData = new FormData(this);
    
    // Submit via fetch
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            if (typeof DevAlert !== 'undefined') {
                DevAlert.success('¡Éxito!', data.message);
            } else {
                alert('✅ ' + data.message);
            }
            
            // Redirect to show page after success
            setTimeout(() => {
                window.location.href = '{{ route("developer.tenants.show", $tenant) }}';
            }, 1500);
        } else {
            throw new Error(data.message || 'Error desconocido');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Show error message
        if (typeof DevAlert !== 'undefined') {
            DevAlert.error('Error', error.message || 'Ha ocurrido un error al actualizar el tenant');
        } else {
            alert('❌ Error: ' + (error.message || 'Ha ocurrido un error al actualizar el tenant'));
        }
        
        // Reset button state
        submitBtn.disabled = false;
        submitText.textContent = 'Actualizar Tenant';
        submitSpinner.classList.add('hidden');
    });
});

// Reset form function
function resetForm() {
    if (confirm('¿Estás seguro de que deseas reestablecer todos los campos a sus valores originales?')) {
        document.getElementById('tenantForm').reset();
        
        // Reset slug preview
        document.getElementById('slugPreview').textContent = '{{ $tenant->slug }}';
        
        // Show confirmation
        if (typeof DevAlert !== 'undefined') {
            DevAlert.info('Formulario Restablecido', 'Los campos han sido restaurados a sus valores originales');
        } else {
            alert('📝 Formulario restablecido a valores originales');
        }
    }
}

// Add some visual feedback for form interactions
document.querySelectorAll('input, select, textarea').forEach(element => {
    element.addEventListener('focus', function() {
        this.parentElement.classList.add('ring-2', 'ring-blue-500', 'ring-opacity-50');
    });
    
    element.addEventListener('blur', function() {
        this.parentElement.classList.remove('ring-2', 'ring-blue-500', 'ring-opacity-50');
    });
});

// Auto-generate slug from name if slug is empty
document.getElementById('name').addEventListener('input', function(e) {
    const slugField = document.getElementById('slug');
    
    if (!slugField.value || slugField.value === '{{ $tenant->slug }}') {
        const slug = e.target.value
            .toLowerCase()
            .replace(/[^\w\s-]/g, '') // Remove special characters
            .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
            .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
        
        slugField.value = slug;
        document.getElementById('slugPreview').textContent = slug || '{{ $tenant->slug }}';
    }
});
</script>
@endpush
@endsection
