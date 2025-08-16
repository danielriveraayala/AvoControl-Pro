@extends('layouts.developer')

@section('title', 'Editar Plan: ' . $plan->name)

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                            <i class="fas fa-edit text-indigo-600 mr-2"></i>
                            Editar Plan: {{ $plan->name }}
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Modifica la configuración de este plan</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.plans.show', $plan) }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <i class="fas fa-eye mr-1"></i>Ver
                        </a>
                        <a href="{{ route('developer.plans.index') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← <span class="ml-1">Volver</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <form action="{{ route('developer.plans.update', $plan) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información Básica
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Plan Key -->
                        <div>
                            <label for="key" class="block text-sm font-medium text-gray-700">Clave del Plan*</label>
                            <input type="text" name="key" id="key" value="{{ old('key', $plan->key) }}" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="ej: custom_plan_2024">
                            <p class="mt-1 text-xs text-gray-500">Identificador único (solo letras, números y guiones bajos)</p>
                            @error('key')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Plan Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Plan*</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="ej: Plan Personalizado">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="lg:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea name="description" id="description" rows="3"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                      placeholder="Describe las características y beneficios de este plan">{{ old('description', $plan->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-dollar-sign text-green-600 mr-2"></i>Información de Precios
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Price -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Precio*</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="price" id="price" value="{{ old('price', $plan->price) }}" min="0" step="0.01" required
                                       class="pl-7 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="0.00">
                            </div>
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Currency (Fixed to USD) -->
                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700">Moneda*</label>
                            <div class="mt-1 relative">
                                <input type="hidden" name="currency" value="USD">
                                <div class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm">
                                    <i class="fas fa-dollar-sign mr-2"></i>USD - Dólar Americano
                                    <span class="ml-2 text-xs text-gray-500">(Moneda fija para PayPal)</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">La moneda está fijada a USD para compatibilidad con PayPal</p>
                            @if($plan->currency !== 'USD')
                                <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-md">
                                    <p class="text-xs text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Nota: Este plan tenía moneda {{ $plan->currency }}. Se actualizará a USD al guardar.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Billing Cycle (Fixed to Monthly) -->
                        <div>
                            <label for="billing_cycle" class="block text-sm font-medium text-gray-700">Ciclo de Facturación*</label>
                            <div class="mt-1 relative">
                                <input type="hidden" name="billing_cycle" value="monthly">
                                <div class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-700 sm:text-sm">
                                    <i class="fas fa-calendar mr-2"></i>Mensual
                                    <span class="ml-2 text-xs text-gray-500">(Ciclo base fijo)</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">El ciclo base es mensual. Los precios anuales se configuran por separado.</p>
                            @if($plan->billing_cycle !== 'monthly')
                                <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-md">
                                    <p class="text-xs text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Nota: Este plan tenía ciclo {{ $plan->billing_cycle }}. Se actualizará a mensual al guardar.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Annual Pricing (Optional) -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-calendar-alt text-orange-600 mr-2"></i>Precios Anuales (Opcional)
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Configura precios anuales para ofrecer descuentos por pago adelantado</p>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Annual Price -->
                        <div>
                            <label for="annual_price" class="block text-sm font-medium text-gray-700">Precio Anual</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="annual_price" id="annual_price" value="{{ old('annual_price', $plan->annual_price) }}" min="0" step="0.01"
                                       class="pl-7 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="0.00">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Dejar vacío si no se ofrece precio anual</p>
                            @error('annual_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Annual Discount Percentage -->
                        <div>
                            <label for="annual_discount_percentage" class="block text-sm font-medium text-gray-700">Descuento Anual (%)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" name="annual_discount_percentage" id="annual_discount_percentage" value="{{ old('annual_discount_percentage', $plan->annual_discount_percentage ?? 15) }}" min="0" max="100"
                                       class="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="15">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">%</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Porcentaje de descuento aplicado al precio anual</p>
                            @error('annual_discount_percentage')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Current annual pricing info -->
                    @if($plan->hasAnnualPricing())
                        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-md" id="current-annual-pricing">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-green-900">Precios Anuales Configurados</h4>
                                    <div class="mt-2 text-sm text-green-700" id="current-pricing-content">
                                        @php
                                            $currencySymbol = $plan->currency === 'EUR' ? '€' : '$';
                                        @endphp
                                        <p><strong>Precio mensual:</strong> {{ $currencySymbol }}{{ number_format($plan->price, 2) }} {{ $plan->currency }}</p>
                                        <p><strong>Precio anual:</strong> {{ $currencySymbol }}{{ number_format($plan->annual_price, 2) }} {{ $plan->currency }}</p>
                                        <p><strong>Ahorro anual:</strong> {{ $currencySymbol }}{{ number_format($plan->getAnnualSavings(), 2) }} {{ $plan->currency }}</p>
                                        <p><strong>Equivalente mensual:</strong> {{ $currencySymbol }}{{ number_format($plan->getMonthlyEquivalent(), 2) }} {{ $plan->currency }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Auto-calculation helper -->
                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-calculator text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-blue-900">Cálculo Automático</h4>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p id="annual-calculation" class="mb-1">Modifica el precio mensual para ver el cálculo automático</p>
                                    <button type="button" id="auto-calculate" class="text-blue-600 hover:text-blue-800 underline">
                                        Calcular precio anual automáticamente
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PayPal Integration Status -->
            @if($plan->paypal_plan_id)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fab fa-paypal text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Plan Sincronizado con PayPal</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Este plan está sincronizado con PayPal (ID: {{ $plan->paypal_plan_id }})</p>
                                <p class="text-xs mt-1">Los cambios de precio requerirán resincronización con PayPal</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Limits and Trial -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-sliders-h text-purple-600 mr-2"></i>Límites y Configuración
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Trial Days -->
                        <div>
                            <label for="trial_days" class="block text-sm font-medium text-gray-700">Días de Prueba</label>
                            <input type="number" name="trial_days" id="trial_days" value="{{ old('trial_days', $plan->trial_days) }}" min="0" max="365"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="0">
                            <p class="mt-1 text-xs text-gray-500">0 = sin prueba gratuita</p>
                            @error('trial_days')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Max Locations -->
                        <div>
                            <label for="max_locations" class="block text-sm font-medium text-gray-700">Máximo Ubicaciones*</label>
                            <input type="number" name="max_locations" id="max_locations" value="{{ old('max_locations', $plan->max_locations) }}" min="-1"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="1 (-1 = ilimitado)">
                            <p class="mt-1 text-xs text-gray-500">-1 para ilimitado</p>
                            @error('max_locations')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Max Users -->
                        <div>
                            <label for="max_users" class="block text-sm font-medium text-gray-700">Máximo Usuarios</label>
                            <input type="number" name="max_users" id="max_users" value="{{ old('max_users', $plan->max_users) }}" min="-1"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="Ej: 10 (-1 = ilimitado)">
                            <p class="mt-1 text-xs text-gray-500">-1 para ilimitado</p>
                            @error('max_users')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Max Lots Per Month -->
                        <div>
                            <label for="max_lots_per_month" class="block text-sm font-medium text-gray-700">Máximo Lotes/Mes</label>
                            <input type="number" name="max_lots_per_month" id="max_lots_per_month" value="{{ old('max_lots_per_month', $plan->max_lots_per_month) }}" min="-1"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="Ej: 500 (-1 = ilimitado)">
                            <p class="mt-1 text-xs text-gray-500">-1 para ilimitado</p>
                            @error('max_lots_per_month')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Max Storage GB -->
                        <div>
                            <label for="max_storage_gb" class="block text-sm font-medium text-gray-700">Almacenamiento (GB)</label>
                            <input type="number" name="max_storage_gb" id="max_storage_gb" value="{{ old('max_storage_gb', $plan->max_storage_gb) }}" min="-1"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="Ej: 10 (-1 = ilimitado)">
                            <p class="mt-1 text-xs text-gray-500">-1 para ilimitado</p>
                            @error('max_storage_gb')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Selection -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-puzzle-piece text-indigo-600 mr-2"></i>Funcionalidades del Plan
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Selecciona las funcionalidades que incluirá este plan</p>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    @foreach($availableFeatures as $category => $features)
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-semibold text-gray-800 uppercase tracking-wide">{{ ucfirst($category) }}</h4>
                                <div class="flex space-x-2">
                                    <button type="button" onclick="selectAll('{{ $category }}')" class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                        Todos
                                    </button>
                                    <button type="button" onclick="selectNone('{{ $category }}')" class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                        Ninguno
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($features as $key => $label)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="features[]" value="{{ $key }}" 
                                               class="feature-checkbox category-{{ $category }} rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                               {{ in_array($key, old('features', $plan->features ?? [])) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Plan Configuration -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-cog text-gray-600 mr-2"></i>Configuración del Plan
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Plan Status -->
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" 
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                       {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">Plan Activo</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_featured" id="is_featured" value="1"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                       {{ old('is_featured', $plan->is_featured) ? 'checked' : '' }}>
                                <label for="is_featured" class="ml-2 text-sm font-medium text-gray-700">Plan Destacado</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_custom" id="is_custom" value="1"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                       {{ old('is_custom', $plan->is_custom) ? 'checked' : '' }}>
                                <label for="is_custom" class="ml-2 text-sm font-medium text-gray-700">Plan Personalizado</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="show_on_landing" id="show_on_landing" value="1"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                       {{ old('show_on_landing', $plan->show_on_landing) ? 'checked' : '' }}>
                                <label for="show_on_landing" class="ml-2 text-sm font-medium text-gray-700">Mostrar en Landing Page</label>
                            </div>
                        </div>

                        <!-- Appearance -->
                        <div class="space-y-4">
                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700">Color del Plan</label>
                                <input type="color" name="color" id="color" value="{{ old('color', $plan->color) }}"
                                       class="mt-1 block w-20 h-10 border border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700">Icono (FontAwesome)</label>
                                <input type="text" name="icon" id="icon" value="{{ old('icon', $plan->icon) }}"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="fas fa-box">
                            </div>
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700">Orden de Aparición</label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" min="0"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-4">
                    <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.plans.show', $plan) }}" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-eye mr-2"></i>Ver Plan
                        </a>
                        <a href="{{ route('developer.plans.index') }}" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-save mr-2"></i>Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selectAll(category) {
    document.querySelectorAll(`.category-${category}`).forEach(checkbox => {
        checkbox.checked = true;
    });
}

function selectNone(category) {
    document.querySelectorAll(`.category-${category}`).forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Auto-format key field
document.getElementById('key').addEventListener('input', function(e) {
    e.target.value = e.target.value
        .toLowerCase()
        .replace(/[^a-z0-9_]/g, '_')
        .replace(/_+/g, '_')
        .replace(/^_|_$/g, '');
});

// Currency symbols and names
const currencyData = {
    'USD': { symbol: '$', name: 'USD' },
    'EUR': { symbol: '€', name: 'EUR' },
    'MXN': { symbol: '$', name: 'MXN' }
};

// Update current annual pricing section (Fixed to USD)
function updateCurrentAnnualPricing() {
    const currentPricingContent = document.getElementById('current-pricing-content');
    if (!currentPricingContent) return;
    
    const currencySymbol = '$';
    const currencyName = 'USD';
    
    // Get original values from the plan
    const originalMonthlyPrice = {{ $plan->price }};
    const originalAnnualPrice = {{ $plan->annual_price ?: 0 }};
    const originalAnnualSavings = {{ $plan->hasAnnualPricing() ? $plan->getAnnualSavings() : 0 }};
    const originalMonthlyEquivalent = {{ $plan->hasAnnualPricing() ? $plan->getMonthlyEquivalent() : 0 }};
    
    currentPricingContent.innerHTML = `
        <p><strong>Precio mensual:</strong> ${currencySymbol}${originalMonthlyPrice.toFixed(2)} ${currencyName}</p>
        <p><strong>Precio anual:</strong> ${currencySymbol}${originalAnnualPrice.toFixed(2)} ${currencyName}</p>
        <p><strong>Ahorro anual:</strong> ${currencySymbol}${originalAnnualSavings.toFixed(2)} ${currencyName}</p>
        <p><strong>Equivalente mensual:</strong> ${currencySymbol}${originalMonthlyEquivalent.toFixed(2)} ${currencyName}</p>
    `;
}

// Annual pricing calculation (Fixed to USD)
function updateAnnualCalculation() {
    const monthlyPrice = parseFloat(document.getElementById('price').value) || 0;
    const discountPercentage = parseInt(document.getElementById('annual_discount_percentage').value) || 15;
    const currencySymbol = '$';
    const currencyName = 'USD';
    
    if (monthlyPrice > 0) {
        const yearlyTotal = monthlyPrice * 12;
        const discountAmount = yearlyTotal * (discountPercentage / 100);
        const annualPriceCalculated = yearlyTotal - discountAmount;
        
        // Round up to the nearest integer to maintain whole prices
        const annualPrice = Math.ceil(annualPriceCalculated);
        const monthlyEquivalent = annualPrice / 12;
        
        // Calculate actual discount percentage after rounding
        const actualDiscountAmount = yearlyTotal - annualPrice;
        const actualDiscountPercentage = (actualDiscountAmount / yearlyTotal) * 100;
        
        document.getElementById('annual-calculation').innerHTML = `
            <strong>Precio mensual:</strong> ${currencySymbol}${monthlyPrice.toFixed(0)} ${currencyName}<br>
            <strong>Total anual sin descuento:</strong> ${currencySymbol}${yearlyTotal.toFixed(0)} ${currencyName}<br>
            <strong>Descuento aplicado:</strong> -${currencySymbol}${actualDiscountAmount.toFixed(0)} ${currencyName} (${actualDiscountPercentage.toFixed(1)}%)<br>
            <strong>Precio anual sugerido:</strong> ${currencySymbol}${annualPrice.toFixed(0)} ${currencyName}<br>
            <strong>Equivalente mensual:</strong> ${currencySymbol}${monthlyEquivalent.toFixed(2)} ${currencyName}
        `;
    } else {
        document.getElementById('annual-calculation').textContent = 'Modifica el precio mensual para ver el cálculo automático';
    }
    
    // Also update current annual pricing section
    updateCurrentAnnualPricing();
}

// Auto-calculate button
document.getElementById('auto-calculate').addEventListener('click', function() {
    const monthlyPrice = parseFloat(document.getElementById('price').value) || 0;
    const discountPercentage = parseInt(document.getElementById('annual_discount_percentage').value) || 15;
    
    if (monthlyPrice > 0) {
        const yearlyTotal = monthlyPrice * 12;
        const annualPriceCalculated = yearlyTotal * (1 - (discountPercentage / 100));
        // Round up to maintain whole prices
        const annualPrice = Math.ceil(annualPriceCalculated);
        document.getElementById('annual_price').value = annualPrice.toFixed(0);
        updateAnnualCalculation();
    } else {
        alert('Por favor ingresa primero el precio mensual');
    }
});

// Update calculation when prices change
document.getElementById('price').addEventListener('input', updateAnnualCalculation);
document.getElementById('annual_discount_percentage').addEventListener('input', updateAnnualCalculation);

// Initial calculation
updateAnnualCalculation();
</script>
@endpush