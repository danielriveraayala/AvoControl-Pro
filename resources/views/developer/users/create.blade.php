@extends('layouts.developer')

@section('title', 'Crear Usuario')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Crear Nuevo Usuario</h1>
                        <p class="text-sm text-gray-600">Agrega un nuevo usuario al sistema</p>
                    </div>
                    <a href="{{ route('developer.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        ← Volver a Usuarios
                    </a>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Create User Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('developer.users.store') }}" method="POST">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Información del Usuario</h3>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                                   placeholder="Nombre completo del usuario">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                                   placeholder="usuario@ejemplo.com">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Contraseña <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password" id="password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                                   placeholder="Mínimo 8 caracteres">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirmar Contraseña <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Confirmar contraseña">
                        </div>
                    </div>

                    <!-- Roles Section -->
                    <div class="mt-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Asignación de Roles</h4>
                        <p class="text-sm text-gray-600 mb-4">Selecciona uno o más roles para el usuario. El primer rol seleccionado será el rol primario.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($roles as $role)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                                    <div class="flex items-start">
                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                               class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                               {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <label for="role_{{ $role->id }}" class="text-sm font-medium text-gray-900 cursor-pointer">
                                                {{ $role->display_name }}
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    {{ $role->name === 'super_admin' ? 'bg-purple-100 text-purple-800' : 
                                                       ($role->name === 'admin' ? 'bg-blue-100 text-blue-800' : 
                                                       'bg-gray-100 text-gray-800') }} ml-2">
                                                    Nivel {{ $role->hierarchy_level }}
                                                </span>
                                            </label>
                                            <p class="text-xs text-gray-500 mt-1">{{ $role->description }}</p>
                                            
                                            <!-- Show some key permissions -->
                                            @if($role->permissions->count() > 0)
                                                <div class="mt-2">
                                                    <p class="text-xs text-gray-400">Permisos clave:</p>
                                                    <div class="flex flex-wrap gap-1 mt-1">
                                                        @foreach($role->permissions->take(3) as $permission)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                                {{ str_replace('_', ' ', $permission->name) }}
                                                            </span>
                                                        @endforeach
                                                        @if($role->permissions->count() > 3)
                                                            <span class="text-xs text-gray-400">+{{ $role->permissions->count() - 3 }} más</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @error('roles')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Additional Options -->
                    <div class="mt-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Opciones Adicionales</h4>
                        
                        <div class="space-y-4">
                            <!-- Send Welcome Email -->
                            <div class="flex items-center">
                                <input type="checkbox" name="send_welcome_email" id="send_welcome_email" value="1"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                       {{ old('send_welcome_email') ? 'checked' : '' }}>
                                <label for="send_welcome_email" class="ml-2 text-sm text-gray-900">
                                    Enviar email de bienvenida
                                </label>
                            </div>
                            
                            <p class="text-xs text-gray-500 ml-6">
                                Se enviará un email con las credenciales de acceso al usuario
                            </p>
                        </div>
                    </div>

                    <!-- Role Hierarchy Information -->
                    <div class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Jerarquía de Roles</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li><strong>Super Admin (8):</strong> Acceso completo al sistema y panel de desarrollador</li>
                                        <li><strong>Admin (7):</strong> Gestión completa de la empresa</li>
                                        <li><strong>Contador (6):</strong> Gestión financiera y contable</li>
                                        <li><strong>Vendedor (5):</strong> Gestión de ventas y clientes</li>
                                        <li><strong>Supervisor (4):</strong> Supervisión de operaciones</li>
                                        <li><strong>Operador (3):</strong> Operaciones básicas</li>
                                        <li><strong>Lector (2):</strong> Solo lectura</li>
                                        <li><strong>Invitado (1):</strong> Acceso muy limitado</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                    <button type="button" onclick="generatePassword()" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                        Generar Contraseña
                    </button>
                    <div class="space-x-3">
                        <a href="{{ route('developer.users.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-md text-sm font-medium">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            Crear Usuario
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function generatePassword() {
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    let password = "";
    for (let i = 0; i < 12; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    
    document.getElementById('password').value = password;
    document.getElementById('password_confirmation').value = password;
    
    // Show password briefly
    const passwordField = document.getElementById('password');
    const originalType = passwordField.type;
    passwordField.type = 'text';
    
    setTimeout(() => {
        passwordField.type = originalType;
    }, 3000);
    
    DevAlert.success('Contraseña Generada', 'La contraseña se ha generado automáticamente y se ocultará en 3 segundos.');
}

// Auto-check send welcome email when password is generated
document.getElementById('password').addEventListener('input', function() {
    if (this.value.length > 0) {
        document.getElementById('send_welcome_email').checked = true;
    }
});
</script>
@endsection