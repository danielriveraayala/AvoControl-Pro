@extends('layouts.developer')

@section('title', 'Perfil de Usuario')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-12 w-12">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                <span class="text-lg font-bold text-white">
                                    {{ substr($user->name, 0, 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                            <p class="text-sm text-gray-600">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('developer.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Editar Usuario
                        </a>
                        <a href="{{ route('developer.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← Volver a Usuarios
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Status Banner -->
        @if($user->suspended_at)
            <div class="bg-red-100 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Usuario Suspendido</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p><strong>Fecha de suspensión:</strong> {{ $user->suspended_at->format('d/m/Y H:i') }}</p>
                            @if($user->suspension_reason)
                                <p><strong>Razón:</strong> {{ $user->suspension_reason }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @elseif($user->hasRole('super_admin'))
            <div class="bg-purple-100 border-l-4 border-purple-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-purple-800">Usuario Super Administrador</h3>
                        <div class="mt-2 text-sm text-purple-700">
                            <p>Este usuario tiene acceso completo al sistema incluyendo el panel de desarrollador.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- User Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Información Básica</h3>
                    </div>
                    <div class="px-6 py-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nombre Completo</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                <dd class="mt-1">
                                    @if($user->suspended_at)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Suspendido
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Activo
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email Verificado</dt>
                                <dd class="mt-1">
                                    @if($user->email_verified_at)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Verificado el {{ $user->email_verified_at->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            No verificado
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Fecha de Registro</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Última Actualización</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            @if($user->password_changed_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Última Cambio de Contraseña</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->password_changed_at->format('d/m/Y H:i') }}</dd>
                                </div>
                            @endif
                            @if($user->created_by)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Creado por</dt>
                                    <dd class="mt-1 text-sm text-gray-900">Usuario ID: {{ $user->created_by }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Roles and Permissions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Roles y Permisos</h3>
                    </div>
                    <div class="px-6 py-6">
                        @if($user->roles->count() > 0)
                            <div class="space-y-6">
                                <!-- Roles -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-3">Roles Asignados</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                                {{ $role->name === 'super_admin' ? 'bg-purple-100 text-purple-800' : 
                                                   ($role->name === 'admin' ? 'bg-blue-100 text-blue-800' : 
                                                   ($role->name === 'vendedor' ? 'bg-green-100 text-green-800' : 
                                                   'bg-gray-100 text-gray-800')) }}">
                                                {{ $role->display_name }}
                                                <span class="ml-1 px-1.5 py-0.5 text-xs bg-white bg-opacity-50 rounded">
                                                    Nivel {{ $role->hierarchy_level }}
                                                </span>
                                                @if($role->pivot->is_primary)
                                                    <span class="ml-1 text-yellow-600">★</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Permissions -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-3">Permisos</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($user->roles as $role)
                                            <div class="border border-gray-200 rounded-lg p-4">
                                                <h5 class="font-medium text-sm text-gray-900 mb-2">
                                                    {{ $role->display_name }}
                                                    @if($role->pivot->is_primary)
                                                        <span class="text-yellow-500 text-xs">★ Primario</span>
                                                    @endif
                                                </h5>
                                                <div class="space-y-1 max-h-32 overflow-y-auto">
                                                    @foreach($role->permissions as $permission)
                                                        <div class="text-xs text-gray-600 flex items-center">
                                                            <svg class="w-3 h-3 text-green-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            {{ str_replace('_', ' ', $permission->name) }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Sin roles asignados</h3>
                                <p class="mt-1 text-sm text-gray-500">Este usuario no tiene roles asignados actualmente.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Activity Overview -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Actividad en el Sistema</h3>
                    </div>
                    <div class="px-6 py-6">
                        <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <dt class="text-sm font-medium text-gray-500">Lotes Creados</dt>
                                <dd class="mt-1 text-2xl font-semibold text-blue-600">{{ $activityData['lots_created'] }}</dd>
                            </div>
                            <div class="text-center">
                                <dt class="text-sm font-medium text-gray-500">Ventas Realizadas</dt>
                                <dd class="mt-1 text-2xl font-semibold text-green-600">{{ $activityData['sales_created'] }}</dd>
                            </div>
                            <div class="text-center">
                                <dt class="text-sm font-medium text-gray-500">Pagos Procesados</dt>
                                <dd class="mt-1 text-2xl font-semibold text-purple-600">{{ $activityData['payments_processed'] }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Acciones Rápidas</h3>
                    </div>
                    <div class="px-6 py-6 space-y-3">
                        <a href="{{ route('developer.users.edit', $user) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium text-center block">
                            Editar Información
                        </a>
                        
                        @if($user->suspended_at)
                            <button onclick="activateUser()" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                                Activar Usuario
                            </button>
                        @else
                            @if(!$user->hasRole('super_admin'))
                                <button onclick="suspendUser()" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                                    Suspender Usuario
                                </button>
                            @endif
                        @endif
                        
                        <button onclick="resetPassword()" class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            Restablecer Contraseña
                        </button>
                        
                        <button onclick="assignRoles()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            Gestionar Roles
                        </button>
                        
                        @if(!$user->hasRole('super_admin') && $user->id !== auth()->id())
                            <button onclick="deleteUser()" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                                Eliminar Usuario
                            </button>
                        @endif
                    </div>
                </div>

                <!-- User Statistics -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Estadísticas</h3>
                    </div>
                    <div class="px-6 py-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Días en el sistema</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->created_at->diffInDays(now()) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Último acceso</span>
                            <span class="text-sm font-medium text-gray-900">{{ $activityData['last_login']->diffForHumans() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Roles asignados</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->roles->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Permisos totales</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->permissions()->count() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Security Information -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Información de Seguridad</h3>
                    </div>
                    <div class="px-6 py-6 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">ID del Usuario</span>
                            <span class="text-sm font-mono text-gray-900">{{ $user->id }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Nivel de jerarquía</span>
                            <span class="text-sm font-medium text-gray-900">{{ $user->getHighestHierarchyLevel() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Acceso a panel dev</span>
                            <span class="text-sm font-medium {{ $user->hasRole('super_admin') ? 'text-green-600' : 'text-red-600' }}">
                                {{ $user->hasRole('super_admin') ? 'Sí' : 'No' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function suspendUser() {
    const reason = prompt('Ingresa la razón de suspensión:');
    if (reason) {
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
        });
    }
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
        });
    }
}

function resetPassword() {
    const newPassword = prompt('Ingresa la nueva contraseña (mínimo 8 caracteres):');
    if (newPassword && newPassword.length >= 8) {
        const confirmPassword = prompt('Confirma la nueva contraseña:');
        if (newPassword === confirmPassword) {
            fetch(`{{ route('developer.users.reset-password', $user) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    new_password: newPassword,
                    new_password_confirmation: confirmPassword
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            });
        } else {
            alert('Las contraseñas no coinciden');
        }
    } else {
        alert('La contraseña debe tener al menos 8 caracteres');
    }
}

function assignRoles() {
    window.location.href = '{{ route("developer.users.edit", $user) }}#roles';
}

function deleteUser() {
    if (confirm('⚠️ ¿Estás seguro de que deseas eliminar este usuario?\n\nEsta acción no se puede deshacer.')) {
        fetch(`{{ route('developer.users.destroy', $user) }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.href = '{{ route("developer.users.index") }}';
            } else {
                alert('Error al eliminar usuario');
            }
        });
    }
}
</script>
@endsection