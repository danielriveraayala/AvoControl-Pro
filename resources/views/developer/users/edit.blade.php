@extends('layouts.admin')

@section('title', 'Editar Usuario')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Editar Usuario</h1>
                        <p class="text-sm text-gray-600">Modifica la información de: {{ $user->name }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('developer.users.show', $user) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Ver Perfil
                        </a>
                        <a href="{{ route('developer.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← Volver a Usuarios
                        </a>
                    </div>
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

        <!-- Warning for Super Admin Users -->
        @if($user->hasRole('super_admin') && auth()->user()->email !== 'developer@avocontrol.com')
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm">
                            <strong>Advertencia:</strong> No tienes permisos para modificar otros usuarios super admin.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Edit User Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('developer.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Información del Usuario</h3>
                </div>
                
                <div class="px-6 py-6">
                    <!-- User Status -->
                    @if($user->suspended_at)
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Usuario Suspendido</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p><strong>Fecha:</strong> {{ $user->suspended_at->format('d/m/Y H:i') }}</p>
                                        @if($user->suspension_reason)
                                            <p><strong>Razón:</strong> {{ $user->suspension_reason }}</p>
                                        @endif
                                        <button type="button" onclick="activateUser()" class="mt-2 bg-green-600 hover:bg-green-700 text-white py-1 px-3 rounded text-sm">
                                            Activar Usuario
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
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
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                                   placeholder="usuario@ejemplo.com">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password (Optional) -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Nueva Contraseña (opcional)
                            </label>
                            <input type="password" name="password" id="password"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                                   placeholder="Dejar vacío para no cambiar">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirmar Nueva Contraseña
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Confirmar nueva contraseña">
                        </div>
                    </div>

                    <!-- User Metadata -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Creado</label>
                            <div class="text-sm text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Última actualización</label>
                            <div class="text-sm text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                        @if($user->created_by)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Creado por</label>
                                <div class="text-sm text-gray-900">ID: {{ $user->created_by }}</div>
                            </div>
                        @endif
                    </div>

                    <!-- Roles Section -->
                    <div class="mt-8">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Roles Asignados</h4>
                        <p class="text-sm text-gray-600 mb-4">Modifica los roles del usuario. El primer rol seleccionado será el rol primario.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($roles as $role)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                                    <div class="flex items-start">
                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                               class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                               {{ in_array($role->id, old('roles', $userRoleIds)) ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <label for="role_{{ $role->id }}" class="text-sm font-medium text-gray-900 cursor-pointer">
                                                {{ $role->display_name }}
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    {{ $role->name === 'super_admin' ? 'bg-purple-100 text-purple-800' : 
                                                       ($role->name === 'admin' ? 'bg-blue-100 text-blue-800' : 
                                                       'bg-gray-100 text-gray-800') }} ml-2">
                                                    Nivel {{ $role->hierarchy_level }}
                                                </span>
                                                @if(in_array($role->id, $userRoleIds))
                                                    @php $userRole = $user->roles->where('id', $role->id)->first(); @endphp
                                                    @if($userRole && $userRole->pivot->is_primary)
                                                        <span class="ml-1 text-yellow-500">★ Primario</span>
                                                    @endif
                                                @endif
                                            </label>
                                            <p class="text-xs text-gray-500 mt-1">{{ $role->description }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @error('roles')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Current Permissions Summary -->
                    @if($user->roles->count() > 0)
                        <div class="mt-8">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Permisos Actuales</h4>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($user->roles as $role)
                                        <div class="bg-white p-3 rounded border">
                                            <h5 class="font-medium text-sm text-gray-900 mb-2">
                                                {{ $role->display_name }}
                                                @if($role->pivot->is_primary)
                                                    <span class="text-yellow-500">★</span>
                                                @endif
                                            </h5>
                                            <div class="space-y-1">
                                                @foreach($role->permissions->take(5) as $permission)
                                                    <div class="text-xs text-gray-600">• {{ str_replace('_', ' ', $permission->name) }}</div>
                                                @endforeach
                                                @if($role->permissions->count() > 5)
                                                    <div class="text-xs text-gray-400">+{{ $role->permissions->count() - 5 }} permisos más</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                    <div class="flex space-x-3">
                        <button type="button" onclick="generatePassword()" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            Generar Nueva Contraseña
                        </button>
                        @if(!$user->suspended_at && !$user->hasRole('super_admin'))
                            <button type="button" onclick="suspendUser()" class="bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                                Suspender Usuario
                            </button>
                        @endif
                    </div>
                    <div class="space-x-3">
                        <a href="{{ route('developer.users.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-md text-sm font-medium">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            Actualizar Usuario
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Suspend User Modal -->
<div id="suspendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Suspender Usuario</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Razón de suspensión:</label>
                <textarea id="suspensionReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500" placeholder="Motivo de la suspensión..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="closeSuspendModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Cancelar
                </button>
                <button onclick="confirmSuspend()" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                    Suspender
                </button>
            </div>
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
    
    alert('Nueva contraseña generada: ' + password + '\n\nLa contraseña se ocultará en 3 segundos.');
}

function suspendUser() {
    document.getElementById('suspendModal').classList.remove('hidden');
}

function closeSuspendModal() {
    document.getElementById('suspendModal').classList.add('hidden');
}

function confirmSuspend() {
    const reason = document.getElementById('suspensionReason').value;
    
    fetch(`{{ route('developer.users.suspend', $user) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error al suspender usuario');
        console.error('Error:', error);
    });
}

function activateUser() {
    if (confirm('¿Estás seguro de que deseas activar este usuario?')) {
        fetch(`{{ route('developer.users.activate', $user) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al activar usuario');
            console.error('Error:', error);
        });
    }
}
</script>
@endsection