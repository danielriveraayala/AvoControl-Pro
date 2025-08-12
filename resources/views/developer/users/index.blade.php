@extends('layouts.developer')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">Gestión de Usuarios</h1>
                        <p class="text-xs sm:text-sm text-gray-600">Administra todos los usuarios del sistema</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.index') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← <span class="ml-1">Dashboard</span>
                        </a>
                        <a href="{{ route('developer.users.create') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <span class="mr-1">+</span> Nuevo Usuario
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

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-4 sm:mb-6">
            <div class="px-4 sm:px-6 py-4">
                <form method="GET" action="{{ route('developer.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Nombre o email">
                    </div>
                    
                    <!-- Role Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                        <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendidos</option>
                        </select>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex space-x-2">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            Filtrar
                        </button>
                        <a href="{{ route('developer.users.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-md text-sm font-medium text-center">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                    Lista de Usuarios ({{ $users->total() }} total)
                </h3>
            </div>
            
            <!-- Mobile Cards (visible on small screens) -->
            <div class="block sm:hidden">
                @forelse($users as $user)
                    <div class="border-b border-gray-200 px-4 py-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-700">
                                            {{ substr($user->name, 0, 2) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : 
                                   ($user->role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <a href="{{ route('developer.users.show', $user) }}" class="inline-flex items-center px-2 py-1 border border-transparent text-xs rounded text-blue-600 bg-blue-100 hover:bg-blue-200">
                                Ver
                            </a>
                            <a href="{{ route('developer.users.edit', $user) }}" class="inline-flex items-center px-2 py-1 border border-transparent text-xs rounded text-yellow-600 bg-yellow-100 hover:bg-yellow-200">
                                Editar
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center">
                        <p class="text-gray-500">No se encontraron usuarios.</p>
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table (hidden on small screens) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Roles
                            </th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="hidden lg:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Última Actividad
                            </th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 sm:h-10 w-8 sm:w-10">
                                            <div class="h-8 sm:h-10 w-8 sm:w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-xs sm:text-sm font-medium text-gray-700">
                                                    {{ substr($user->name, 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-3 sm:ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-xs sm:text-sm text-gray-500">{{ $user->email }}</div>
                                            @if($user->hasRole('super_admin'))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    Super Admin
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                                                {{ $role->name === 'super_admin' ? 'bg-purple-100 text-purple-800' : 
                                                   ($role->name === 'admin' ? 'bg-blue-100 text-blue-800' : 
                                                   ($role->name === 'vendedor' ? 'bg-green-100 text-green-800' : 
                                                   'bg-gray-100 text-gray-800')) }}">
                                                {{ $role->display_name }}
                                                @if($role->pivot->is_primary)
                                                    <span class="ml-1">★</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    @if($user->suspended_at)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Suspendido
                                        </span>
                                        @if($user->suspension_reason)
                                            <div class="text-xs text-gray-500 mt-1">{{ $user->suspension_reason }}</div>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Activo
                                        </span>
                                    @endif
                                </td>
                                
                                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $user->updated_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-xs">{{ $user->updated_at->diffForHumans() }}</div>
                                </td>
                                
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex flex-wrap gap-1 sm:gap-2">
                                        <a href="{{ route('developer.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-900 text-xs sm:text-sm">
                                            Ver
                                        </a>
                                        <a href="{{ route('developer.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900 text-xs sm:text-sm">
                                            Editar
                                        </a>
                                        
                                        @if($user->suspended_at)
                                            <button onclick="activateUser({{ $user->id }})" class="text-green-600 hover:text-green-900 text-xs sm:text-sm">
                                                Activar
                                            </button>
                                        @else
                                            @if(!$user->hasRole('super_admin'))
                                                <button onclick="suspendUser({{ $user->id }})" class="text-yellow-600 hover:text-yellow-900 text-xs sm:text-sm">
                                                    Suspender
                                                </button>
                                            @endif
                                        @endif
                                        
                                        <button onclick="resetPassword({{ $user->id }})" class="text-purple-600 hover:text-purple-900 text-xs sm:text-sm">
                                            Reset
                                        </button>
                                        
                                        @if(!$user->hasRole('super_admin') && $user->id !== auth()->id())
                                            <button onclick="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900 text-xs sm:text-sm">
                                                Eliminar
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No se encontraron usuarios con los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($users->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                    {{ $users->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>


@push('scripts')
<script>
function suspendUser(userId) {
    Swal.fire({
        title: 'Suspender Usuario',
        text: 'Ingresa la razón de la suspensión:',
        input: 'textarea',
        inputPlaceholder: 'Motivo de la suspensión...',
        showCancelButton: true,
        confirmButtonText: 'Suspender',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#f59e0b',
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar una razón para la suspensión';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Suspendiendo usuario...', 'Por favor espera');
            
            fetch(`/developer/users/${userId}/suspend`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reason: result.value })
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                handleAjaxResponse(data);
                if (data.success) {
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => {
                DevAlert.close();
                handleFetchError(error);
            });
        }
    });
}

function activateUser(userId) {
    DevAlert.confirm(
        '¿Activar Usuario?',
        '¿Estás seguro de que deseas activar este usuario? Tendrá acceso completo al sistema.',
        'Sí, activar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Activando usuario...', 'Por favor espera');
            
            fetch(`/developer/users/${userId}/activate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                handleAjaxResponse(data);
                if (data.success) {
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => {
                DevAlert.close();
                handleFetchError(error);
            });
        }
    });
}

function resetPassword(userId) {
    Swal.fire({
        title: 'Restablecer Contraseña',
        html: `
            <div class="text-left">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nueva contraseña:</label>
                <input type="password" id="newPassword" class="w-full px-3 py-2 border border-gray-300 rounded-md mb-4" placeholder="Mínimo 8 caracteres">
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar contraseña:</label>
                <input type="password" id="confirmPassword" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Confirmar contraseña">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Restablecer',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#8b5cf6',
        focusConfirm: false,
        preConfirm: () => {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!newPassword || !confirmPassword) {
                Swal.showValidationMessage('Ambas contraseñas son requeridas');
                return false;
            }
            
            if (newPassword.length < 8) {
                Swal.showValidationMessage('La contraseña debe tener al menos 8 caracteres');
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                Swal.showValidationMessage('Las contraseñas no coinciden');
                return false;
            }
            
            return { newPassword, confirmPassword };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Restableciendo contraseña...', 'Por favor espera');
            
            fetch(`/developer/users/${userId}/reset-password`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    new_password: result.value.newPassword,
                    new_password_confirmation: result.value.confirmPassword
                })
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                handleAjaxResponse(data);
            })
            .catch(error => {
                DevAlert.close();
                handleFetchError(error);
            });
        }
    });
}

function deleteUser(userId) {
    DevAlert.confirmDanger(
        '¿Eliminar Usuario?',
        '⚠️ Esta acción no se puede deshacer. El usuario será eliminado permanentemente del sistema.',
        'Sí, eliminar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Eliminando usuario...', 'Por favor espera');
            
            fetch(`/developer/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                DevAlert.close();
                if (response.ok) {
                    DevAlert.success('Usuario eliminado', 'El usuario ha sido eliminado exitosamente');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    DevAlert.error('Error', 'No se pudo eliminar el usuario');
                }
            })
            .catch(error => {
                DevAlert.close();
                handleFetchError(error);
            });
        }
    });
}
</script>
@endpush
@endsection