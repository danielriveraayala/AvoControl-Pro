@extends('layouts.developer')

@section('title', 'Crear Nuevo Plan')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                            <i class="fas fa-plus text-blue-600 mr-2"></i>
                            Crear Nuevo Plan
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Configura un nuevo plan de suscripción</p>
                    </div>
                    <a href="{{ route('developer.plans.index') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 self-start">
                        ← <span class="ml-1">Volver</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Create Form -->
        <form action="{{ route('developer.plans.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Basic Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información Básica
                    </h3>
                </div>
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Plan Key -->
                        <div>
                            <label for="key" class="block text-sm font-medium text-gray-700">Clave del Plan*</label>
                            <input type="text" name="key" id="key" value="{{ old('key') }}" required
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
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
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
                                      placeholder="Describe las características y beneficios de este plan">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-dollar-sign text-green-600 mr-2"></i>Información de Precios
                    </h3>
                </div>
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Price -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Precio*</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" name="price" id="price" value="{{ old('price', '0') }}" min="0" step="0.01" required
                                       class="pl-7 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="0.00">
                            </div>
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Currency -->
                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700">Moneda*</label>
                            <select name="currency" id="currency" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD - Dólar Americano</option>
                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                <option value="MXN" {{ old('currency') == 'MXN' ? 'selected' : '' }}>MXN - Peso Mexicano</option>
                            </select>
                            @error('currency')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Billing Cycle -->
                        <div>
                            <label for="billing_cycle" class="block text-sm font-medium text-gray-700">Ciclo de Facturación*</label>
                            <select name="billing_cycle" id="billing_cycle" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="monthly" {{ old('billing_cycle', 'monthly') == 'monthly' ? 'selected' : '' }}>Mensual</option>
                                <option value="yearly" {{ old('billing_cycle') == 'yearly' ? 'selected' : '' }}>Anual</option>
                            </select>
                            @error('billing_cycle')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Limits and Trial -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-sliders-h text-purple-600 mr-2"></i>Límites y Configuración
                    </h3>
                </div>
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <!-- Trial Days -->
                        <div>
                            <label for="trial_days" class="block text-sm font-medium text-gray-700">Días de Prueba</label>
                            <input type="number" name="trial_days" id="trial_days" value="{{ old('trial_days', '0') }}" min="0" max="365"
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
                            <input type="number" name="max_locations" id="max_locations" value="{{ old('max_locations', '1') }}" min="1"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="1">
                            @error('max_locations')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Max Users -->
                        <div>
                            <label for="max_users" class="block text-sm font-medium text-gray-700">Máximo Usuarios</label>
                            <input type="number" name="max_users" id="max_users" value="{{ old('max_users') }}" min="-1"
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
                            <input type="number" name="max_lots_per_month" id="max_lots_per_month" value="{{ old('max_lots_per_month') }}" min="-1"
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
                            <input type="number" name="max_storage_gb" id="max_storage_gb" value="{{ old('max_storage_gb') }}" min="-1"
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
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-puzzle-piece text-indigo-600 mr-2"></i>Funcionalidades del Plan
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Selecciona las funcionalidades que incluirá este plan</p>
                </div>
                <div class="px-6 py-6">
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
                                               {{ in_array($key, old('features', [])) ? 'checked' : '' }}>
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
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-cog text-gray-600 mr-2"></i>Configuración del Plan
                    </h3>
                </div>
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Plan Status -->
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" 
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">Plan Activo</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_featured" id="is_featured" value="1"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                       {{ old('is_featured') ? 'checked' : '' }}>
                                <label for="is_featured" class="ml-2 text-sm font-medium text-gray-700">Plan Destacado</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_custom" id="is_custom" value="1"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                       {{ old('is_custom', true) ? 'checked' : '' }}>
                                <label for="is_custom" class="ml-2 text-sm font-medium text-gray-700">Plan Personalizado</label>
                            </div>
                        </div>

                        <!-- Appearance -->
                        <div class="space-y-4">
                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700">Color del Plan</label>
                                <input type="color" name="color" id="color" value="{{ old('color', '#3B82F6') }}"
                                       class="mt-1 block w-20 h-10 border border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700">Icono (FontAwesome)</label>
                                <input type="text" name="icon" id="icon" value="{{ old('icon', 'fas fa-box') }}"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="fas fa-box">
                            </div>
                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700">Orden de Aparición</label>
                                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', '50') }}" min="0"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4">
                    <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.plans.index') }}" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-plus mr-2"></i>Crear Plan
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

// Preview color
document.getElementById('color').addEventListener('change', function(e) {
    // Could add color preview functionality here
});
</script>
@endpush