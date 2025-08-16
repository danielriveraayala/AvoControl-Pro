@extends('layouts.developer')

@section('title', 'Crear Nuevo Tenant')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                            <span class="hidden sm:inline">🏢 </span>Crear Nuevo Tenant
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Registra una nueva empresa en el sistema</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.tenants.index') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← <span class="ml-1">Volver a Tenants</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 sm:mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 sm:mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Create Tenant Form -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <form method="POST" action="{{ route('developer.tenants.store') }}">
                @csrf
                <div class="px-4 sm:px-6 py-6">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Company Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre de la Empresa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                                   placeholder="Ej: Centro de Acopio San Juan"
                                   required>
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                Slug (URL) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('slug') border-red-500 @enderror"
                                   placeholder="ej: centro-acopio-san-juan"
                                   required>
                            @error('slug')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Solo letras minúsculas, números y guiones. Se usará para la URL del tenant.</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email de Contacto <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                                   placeholder="contacto@empresa.com"
                                   required>
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Teléfono
                            </label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                                   placeholder="+52 443 123 4567">
                            @error('phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Plan -->
                        <div>
                            <label for="plan" class="block text-sm font-medium text-gray-700 mb-2">
                                Plan de Suscripción <span class="text-red-500">*</span>
                            </label>
                            <select name="plan" id="plan" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('plan') border-red-500 @enderror"
                                    required>
                                <option value="">Seleccionar plan...</option>
                                <option value="trial" {{ old('plan') == 'trial' ? 'selected' : '' }}>Trial (7 días gratis)</option>
                                <option value="basic" {{ old('plan') == 'basic' ? 'selected' : '' }}>Básico ($29/mes)</option>
                                <option value="premium" {{ old('plan') == 'premium' ? 'selected' : '' }}>Premium ($79/mes)</option>
                                <option value="enterprise" {{ old('plan') == 'enterprise' ? 'selected' : '' }}>Enterprise ($199/mes)</option>
                                <option value="custom" {{ old('plan') == 'custom' ? 'selected' : '' }}>Personalizado</option>
                            </select>
                            @error('plan')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Estado Inicial <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror"
                                    required>
                                <option value="">Seleccionar estado...</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Activo</option>
                                <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                                <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspendido</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Domain Configuration -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuración de Dominio</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Domain -->
                            <div>
                                <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">
                                    Dominio Personalizado
                                </label>
                                <input type="text" name="domain" id="domain" value="{{ old('domain') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('domain') border-red-500 @enderror"
                                       placeholder="empresa.com">
                                @error('domain')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Opcional. Si no se especifica, se usará el subdominio.</p>
                            </div>

                            <!-- Subdomain -->
                            <div>
                                <label for="subdomain" class="block text-sm font-medium text-gray-700 mb-2">
                                    Subdominio
                                </label>
                                <div class="flex">
                                    <input type="text" name="subdomain" id="subdomain" value="{{ old('subdomain') }}"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('subdomain') border-red-500 @enderror"
                                           placeholder="empresa">
                                    <span class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 bg-gray-50 text-sm text-gray-500 rounded-r-md">
                                        .avocontrol.pro
                                    </span>
                                </div>
                                @error('subdomain')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Se genera automáticamente desde el slug si no se especifica.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuración Inicial</h3>
                        <div class="space-y-4">
                            <!-- Create Admin User -->
                            <div class="flex items-start">
                                <input type="checkbox" name="create_admin" id="create_admin" value="1"
                                       class="mt-0.5 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                       {{ old('create_admin', true) ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <label for="create_admin" class="text-sm text-gray-900">
                                        Crear usuario administrador automáticamente
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Se creará un usuario admin con el email especificado y se enviará una invitación
                                    </p>
                                </div>
                            </div>

                            <!-- Auto Setup -->
                            <div class="flex items-start">
                                <input type="checkbox" name="auto_setup" id="auto_setup" value="1"
                                       class="mt-0.5 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                       {{ old('auto_setup', true) ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <label for="auto_setup" class="text-sm text-gray-900">
                                        Configuración automática del tenant
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Se aplicarán las configuraciones predeterminadas del plan seleccionado
                                    </p>
                                </div>
                            </div>

                            <!-- Send Welcome Email -->
                            <div class="flex items-start">
                                <input type="checkbox" name="send_welcome" id="send_welcome" value="1"
                                       class="mt-0.5 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                       {{ old('send_welcome', true) ? 'checked' : '' }}>
                                <div class="ml-3">
                                    <label for="send_welcome" class="text-sm text-gray-900">
                                        Enviar email de bienvenida
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Se enviará un email de bienvenida con las credenciales e instrucciones de acceso
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Plan Information -->
                    <div class="mt-6 sm:mt-8 bg-blue-50 border-l-4 border-blue-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Información de Planes</h3>
                                <div class="mt-2 text-xs sm:text-sm text-blue-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li><strong>Trial:</strong> 7 días gratis, 1 usuario, 50 lotes máximo</li>
                                        <li><strong>Básico:</strong> $29/mes, 5 usuarios, 500 lotes/mes</li>
                                        <li><strong>Premium:</strong> $79/mes, 25 usuarios, 2,000 lotes/mes</li>
                                        <li><strong>Enterprise:</strong> $199/mes, 100 usuarios, lotes ilimitados</li>
                                        <li><strong>Personalizado:</strong> Configuración y precios específicos</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row sm:justify-between gap-3 sm:gap-0">
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-3 order-2 sm:order-1">
                        <button type="button" onclick="generateSlug()" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            <span class="hidden sm:inline">🔗 </span>Generar Slug desde Nombre
                        </button>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-3 order-1 sm:order-2">
                        <a href="{{ route('developer.tenants.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-md text-sm font-medium text-center">
                            <span class="hidden sm:inline">← </span>Cancelar
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            <span class="hidden sm:inline">💾 </span>Crear Tenant
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function generateSlug() {
    const name = document.getElementById('name').value;
    if (!name) {
        alert('Por favor ingresa primero el nombre de la empresa');
        return;
    }
    
    const slug = name.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim('-');
    
    document.getElementById('slug').value = slug;
    
    // Auto-generate subdomain if not set
    const subdomainField = document.getElementById('subdomain');
    if (!subdomainField.value) {
        subdomainField.value = slug;
    }
}

// Auto-generate slug when name changes
document.getElementById('name').addEventListener('input', function() {
    const slugField = document.getElementById('slug');
    if (!slugField.value) {
        generateSlug();
    }
});
</script>
@endsection