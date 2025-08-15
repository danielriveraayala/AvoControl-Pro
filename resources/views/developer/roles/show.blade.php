@extends('layouts.developer')

@section('title', 'Detalles del Rol')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Detalles del Rol: {{ $role->display_name }}</h1>
                        <p class="text-sm text-gray-600">Información completa del rol y sus permisos asignados</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('developer.roles.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← Volver
                        </a>
                        @if(!$role->is_system)
                        <a href="{{ route('developer.roles.edit', $role) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <i class="fas fa-edit mr-2"></i>Editar
                        </a>
                        @endif
                        <a href="{{ route('developer.roles.clone', $role) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            <i class="fas fa-copy mr-2"></i>Clonar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Role Information -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Información del Rol</h3>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nombre del Sistema</dt>
                                <dd class="text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded mt-1">{{ $role->name }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nombre para Mostrar</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ $role->display_name }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                                <dd class="text-sm text-gray-900 mt-1">{{ $role->description ?? 'Sin descripción' }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nivel de Jerarquía</dt>
                                <dd class="mt-1">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-3">
                                            <div class="bg-{{ $role->hierarchy_level >= 90 ? 'red' : ($role->hierarchy_level >= 70 ? 'yellow' : ($role->hierarchy_level >= 50 ? 'blue' : 'green')) }}-500 h-2 rounded-full" 
                                                 style="width: {{ $role->hierarchy_level }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $role->hierarchy_level }}</span>
                                    </div>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tipo</dt>
                                <dd class="mt-1">
                                    @if($role->is_system)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-lock mr-1"></i>Rol del Sistema
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-user-cog mr-1"></i>Rol Personalizado
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Usuarios Asignados</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $role->users->count() }} usuarios
                                    </span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total de Permisos</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        {{ $role->permissions->count() }} permisos
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Users with this role -->
                @if($role->users->count() > 0)
                <div class="bg-white shadow rounded-lg mt-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Usuarios con este Rol</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="space-y-3">
                            @foreach($role->users as $user)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-600 text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                                @if($user->getRawOriginal('pivot_is_primary') ?? false)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Rol Principal
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Permissions -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900">Permisos Asignados</h3>
                            @if(!$role->is_system || $role->name === 'super_admin')
                            <button type="button" 
                                    class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 edit-permissions-btn"
                                    data-role-id="{{ $role->id }}"
                                    data-role-name="{{ $role->display_name }}">
                                <i class="fas fa-key mr-2"></i>Editar Permisos
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="px-6 py-4">
                        @if($role->permissions->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($allPermissions as $module => $modulePermissions)
                                    @php
                                        $roleModulePermissions = $role->permissions->where('module', $module);
                                    @endphp
                                    @if($roleModulePermissions->count() > 0)
                                    <div class="border border-gray-200 rounded-lg">
                                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                            <h4 class="text-sm font-semibold text-gray-900 flex items-center">
                                                <i class="fas fa-folder mr-2 text-gray-600"></i>
                                                {{ ucfirst($module) }}
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $roleModulePermissions->count() }}/{{ $modulePermissions->count() }}
                                                </span>
                                            </h4>
                                        </div>
                                        <div class="px-4 py-3">
                                            <div class="space-y-2">
                                                @foreach($roleModulePermissions as $permission)
                                                <div class="flex items-center">
                                                    <i class="fas fa-check text-green-500 text-xs mr-2"></i>
                                                    <span class="text-sm text-gray-900">{{ $permission->display_name }}</span>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-key text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500">Este rol no tiene permisos asignados</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit Log Section -->
        <div class="bg-white shadow rounded-lg mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Historial de Cambios</h3>
                <p class="text-sm text-gray-600">Registro de todas las modificaciones realizadas a este rol</p>
            </div>
            <div class="px-6 py-4">
                @php
                    $audits = $role->audits()->limit(10)->get();
                @endphp
                
                @if($audits->count() > 0)
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($audits as $index => $audit)
                            <li>
                                <div class="relative pb-8">
                                    @if($index < $audits->count() - 1)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                @if($audit->action === 'created') bg-green-500
                                                @elseif($audit->action === 'updated') bg-blue-500
                                                @elseif($audit->action === 'deleted') bg-red-500
                                                @elseif($audit->action === 'permissions_changed') bg-yellow-500
                                                @else bg-gray-500 @endif">
                                                @if($audit->action === 'created')
                                                    <i class="fas fa-plus text-white text-sm"></i>
                                                @elseif($audit->action === 'updated')
                                                    <i class="fas fa-edit text-white text-sm"></i>
                                                @elseif($audit->action === 'deleted')
                                                    <i class="fas fa-trash text-white text-sm"></i>
                                                @elseif($audit->action === 'permissions_changed')
                                                    <i class="fas fa-key text-white text-sm"></i>
                                                @else
                                                    <i class="fas fa-circle text-white text-sm"></i>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-900">
                                                    <span class="font-medium">{{ $audit->user->name ?? 'Usuario desconocido' }}</span>
                                                    {{ strtolower($audit->formatted_action) }} el rol
                                                </p>
                                                <p class="mt-0.5 text-xs text-gray-500">
                                                    {{ $audit->created_at->format('d/m/Y H:i:s') }}
                                                    @if($audit->ip_address)
                                                        desde {{ $audit->ip_address }}
                                                    @endif
                                                </p>
                                            </div>
                                            
                                            @if($audit->action === 'permissions_changed' && $audit->old_values && $audit->new_values)
                                            <div class="mt-2 text-xs text-gray-600">
                                                @php
                                                    $oldPermissions = collect($audit->old_values['permissions'] ?? []);
                                                    $newPermissions = collect($audit->new_values['permissions'] ?? []);
                                                    $added = $newPermissions->diff($oldPermissions);
                                                    $removed = $oldPermissions->diff($newPermissions);
                                                @endphp
                                                
                                                @if($added->count() > 0)
                                                    <div class="text-green-600">
                                                        + {{ $added->count() }} permisos agregados
                                                    </div>
                                                @endif
                                                
                                                @if($removed->count() > 0)
                                                    <div class="text-red-600">
                                                        - {{ $removed->count() }} permisos removidos
                                                    </div>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    @if($role->audits()->count() > 10)
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-500">
                            Mostrando los últimos 10 cambios de {{ $role->audits()->count() }} total
                        </p>
                    </div>
                    @endif
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-history text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No hay historial de cambios para este rol</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Permissions Modal (Tailwind CSS) -->
<div id="permissionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
        <!-- Modal Header -->
        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900" id="permissionsModalLabel">Editar Permisos</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closePermissionsModal()">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="mt-4">
            <div id="permissions-container">
                <!-- Permissions will be loaded here via AJAX -->
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 mt-4">
            <button type="button" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600" onclick="closePermissionsModal()">
                Cancelar
            </button>
            <button type="button" id="save-permissions-btn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Guardar Permisos
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Edit permissions modal
    let currentRoleId = null;
    
    $('.edit-permissions-btn').on('click', function() {
        currentRoleId = $(this).data('role-id');
        const roleName = $(this).data('role-name');
        
        $('#permissionsModalLabel').text('Editar Permisos - ' + roleName);
        $('#permissions-container').html('<div class="text-center py-8"><i class="fas fa-spinner fa-spin fa-2x text-gray-500"></i></div>');
        showPermissionsModal();
        
        // Load role details and permissions
        const detailsUrl = `/developer/roles/${currentRoleId}/details`;
        console.log('Loading role details from URL:', detailsUrl);
        
        $.ajax({
            url: detailsUrl,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    renderPermissions(response.role);
                } else {
                    Swal.fire('Error', 'Error en la respuesta del servidor', 'error');
                    closePermissionsModal();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                let errorMessage = 'No se pudieron cargar los permisos';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMessage = 'Rol no encontrado';
                } else if (xhr.status === 403) {
                    errorMessage = 'No tienes permisos para realizar esta acción';
                } else if (xhr.status === 500) {
                    errorMessage = 'Error interno del servidor';
                }
                
                Swal.fire('Error', errorMessage, 'error');
                closePermissionsModal();
            }
        });
    });

    // Render permissions checkboxes
    function renderPermissions(role) {
        let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
        
        // Get all available permissions grouped by module
        const allPermissions = @json($allPermissions);
        const rolePermissions = role.permissions;
        const rolePermissionIds = [];
        
        // Extract permission IDs from role permissions
        Object.values(rolePermissions).forEach(modulePerms => {
            modulePerms.forEach(perm => {
                rolePermissionIds.push(perm.id);
            });
        });
        
        // Render each module
        Object.keys(allPermissions).forEach(function(module) {
            html += '<div class="border border-gray-200 rounded-lg permission-module">';
            html += `<div class="bg-gray-50 px-4 py-3 border-b border-gray-200">`;
            html += `<div class="flex justify-between items-center">`;
            html += `<h4 class="text-sm font-semibold text-gray-900 flex items-center">`;
            html += `<i class="fas fa-folder mr-2 text-gray-600"></i>${formatModuleName(module)}`;
            html += `</h4>`;
            html += `<div class="flex items-center">`;
            html += `<input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded module-toggle" `;
            html += `id="module-${module}" data-module="${module}">`;
            html += `<label for="module-${module}" class="ml-2 text-sm text-gray-700">Todos</label>`;
            html += `</div>`;
            html += `</div>`;
            html += `</div>`;
            html += `<div class="px-4 py-3">`;
            html += `<div class="space-y-3">`;
            
            allPermissions[module].forEach(function(permission) {
                const isChecked = rolePermissionIds.includes(permission.id);
                html += `<div class="flex items-center">`;
                html += `<input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded permission-checkbox" `;
                html += `id="perm-${permission.id}" value="${permission.id}" data-module="${module}" `;
                html += isChecked ? 'checked' : '';
                html += `>`;
                html += `<label for="perm-${permission.id}" class="ml-2 text-sm text-gray-900">`;
                html += `${permission.display_name}`;
                html += `</label>`;
                html += `</div>`;
            });
            
            html += `</div>`;
            html += `</div>`;
            html += '</div>';
        });
        
        html += '</div>';
        $('#permissions-container').html(html);
        
        // Update module toggles based on checked permissions
        updateModuleToggles();
    }

    // Format module name
    function formatModuleName(module) {
        const moduleNames = {
            'users': 'Usuarios',
            'roles': 'Roles',
            'lots': 'Lotes',
            'sales': 'Ventas',
            'payments': 'Pagos',
            'suppliers': 'Proveedores',
            'customers': 'Clientes',
            'reports': 'Reportes',
            'configuration': 'Configuración',
            'system': 'Sistema'
        };
        return moduleNames[module] || module;
    }

    // Module toggle functionality
    $(document).on('change', '.module-toggle', function() {
        const module = $(this).data('module');
        const isChecked = $(this).prop('checked');
        
        $(`.permission-checkbox[data-module="${module}"]`).prop('checked', isChecked);
    });

    // Update module toggles when individual permissions change
    $(document).on('change', '.permission-checkbox', function() {
        updateModuleToggles();
    });

    function updateModuleToggles() {
        $('.module-toggle').each(function() {
            const module = $(this).data('module');
            const total = $(`.permission-checkbox[data-module="${module}"]`).length;
            const checked = $(`.permission-checkbox[data-module="${module}"]:checked`).length;
            
            $(this).prop('checked', total === checked && total > 0);
        });
    }

    // Save permissions
    $('#save-permissions-btn').on('click', function() {
        const permissions = [];
        $('.permission-checkbox:checked').each(function() {
            permissions.push($(this).val());
        });
        
        $.ajax({
            url: `/developer/roles/${currentRoleId}/permissions`,
            method: 'POST',
            data: {
                permissions: permissions,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    closePermissionsModal();
                    setTimeout(() => location.reload(), 2000);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire('Error', response.message || 'Error al actualizar permisos', 'error');
            }
        });
    });
});

// Modal helper functions
function showPermissionsModal() {
    document.getElementById('permissionsModal').classList.remove('hidden');
}

function closePermissionsModal() {
    document.getElementById('permissionsModal').classList.add('hidden');
}
</script>

<style>
    .permission-module {
        margin-bottom: 1.5rem;
    }
</style>
@endpush
@endsection