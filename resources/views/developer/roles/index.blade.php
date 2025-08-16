@extends('layouts.developer')

@section('title', 'Gestión de Roles')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">Gestión de Roles y Permisos</h1>
                        <p class="text-xs sm:text-sm text-gray-600">Administra los roles del sistema y sus permisos asociados</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
                        <a href="{{ route('developer.index') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← <span class="ml-1">Dashboard</span>
                        </a>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->getHighestHierarchyLevel() > 1)
                        <a href="{{ route('developer.roles.create') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <span class="mr-1">+</span> Crear Nuevo Rol
                        </a>
                        @endif
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

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                {{ session('warning') }}
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-user-shield text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Total Roles</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $roles->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-lock text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Roles del Sistema</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $roles->where('is_system', true)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-users-cog text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Roles Personalizados</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $roles->where('is_system', false)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-key text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Total Permisos</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $permissions->flatten()->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Roles Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">Roles del Sistema</h3>
            </div>

            <div class="overflow-x-auto p-2 sm:p-3">
                <table class="table table-striped min-w-full divide-y divide-gray-200" id="rolesTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Descripción</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Jerarquía</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Usuarios</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Permisos</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Tipo</th>
                            <th class="px-3 sm:px-6 py-2 sm:py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($roles as $role)
                        <tr>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($role->name === 'super_admin')
                                        <i class="fas fa-crown text-yellow-500 mr-2 text-sm"></i>
                                    @elseif($role->hierarchy_level >= 90)
                                        <i class="fas fa-star text-blue-500 mr-2 text-sm"></i>
                                    @elseif($role->hierarchy_level >= 70)
                                        <i class="fas fa-user-tie text-indigo-500 mr-2 text-sm"></i>
                                    @else
                                        <i class="fas fa-user text-gray-400 mr-2 text-sm"></i>
                                    @endif
                                    <div>
                                        <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $role->display_name }}</div>
                                        <div class="text-xs text-gray-500 sm:hidden">{{ $role->users->count() }} users</div>
                                        <div class="text-xs text-gray-500 hidden sm:block">{{ $role->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 hidden md:table-cell">
                                <div class="text-sm text-gray-900">{{ $role->description ?? 'Sin descripción' }}</div>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap hidden sm:table-cell">
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-{{ $role->hierarchy_level >= 90 ? 'red' : ($role->hierarchy_level >= 70 ? 'yellow' : ($role->hierarchy_level >= 50 ? 'blue' : 'green')) }}-500 h-2.5 rounded-full"
                                         style="width: {{ $role->hierarchy_level }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600 mt-1">{{ $role->hierarchy_level }}</span>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $role->users->count() }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center hidden sm:table-cell">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                    {{ $role->permissions->count() }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center hidden lg:table-cell">
                                @if($role->is_system)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Sistema
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Personalizado
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-1 sm:space-x-2">
                                    @if(auth()->user()->canManageRole($role))
                                        <a href="{{ route('developer.roles.show', $role) }}"
                                           class="text-indigo-600 hover:text-indigo-900 p-1"
                                           title="Ver detalles">
                                            <i class="fas fa-eye text-xs sm:text-sm"></i>
                                        </a>

                                        @if(!$role->is_system || $role->name === 'super_admin')
                                        <button type="button"
                                                class="text-yellow-600 hover:text-yellow-900 edit-permissions-btn p-1"
                                                data-role-id="{{ $role->id }}"
                                                data-role-name="{{ $role->display_name }}"
                                                title="Editar permisos">
                                            <i class="fas fa-key text-xs sm:text-sm"></i>
                                        </button>
                                        @endif

                                        @if(!$role->is_system)
                                        <a href="{{ route('developer.roles.edit', $role) }}"
                                           class="text-blue-600 hover:text-blue-900 p-1"
                                           title="Editar rol">
                                            <i class="fas fa-edit text-xs sm:text-sm"></i>
                                        </a>
                                        @endif

                                        <a href="{{ route('developer.roles.clone', $role) }}"
                                           class="text-green-600 hover:text-green-900 p-1 hidden sm:inline-block"
                                           title="Clonar rol">
                                            <i class="fas fa-copy text-xs sm:text-sm"></i>
                                        </a>

                                        @if(!$role->is_system)
                                        <button type="button"
                                                class="text-red-600 hover:text-red-900 delete-role-btn p-1"
                                                data-role-id="{{ $role->id }}"
                                                data-role-name="{{ $role->display_name }}"
                                                data-users-count="{{ $role->users->count() }}"
                                                title="Eliminar rol">
                                            <i class="fas fa-trash text-xs sm:text-sm"></i>
                                        </button>
                                        @endif
                                    @else
                                        <!-- Read-only view for roles user cannot manage -->
                                        <span class="text-gray-400 p-1" title="Solo lectura - Nivel de jerarquía insuficiente">
                                            <i class="fas fa-eye-slash text-xs sm:text-sm"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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

@push('styles')
<link href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css" rel="stylesheet">
<style>
    .permission-module {
        margin-bottom: 1.5rem;
    }
    .permission-module-header {
        background-color: #f8f9fa;
        padding: 0.75rem;
        border-radius: 0.25rem;
        margin-bottom: 0.5rem;
        cursor: pointer;
    }
    .permission-module-header:hover {
        background-color: #e9ecef;
    }
    .permission-item {
        padding: 0.5rem 1rem;
    }
    .permission-item:hover {
        background-color: #f8f9fa;
    }

    /* DataTable custom styles for Tailwind */
    .dataTables_wrapper {
        font-family: inherit;
    }
    .dataTables_length,
    .dataTables_filter,
    .dataTables_info,
    .dataTables_paginate {
        margin: 0.5rem 0;
    }
    .dataTables_length select,
    .dataTables_filter input {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin: 0 0.125rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        background: white;
        color: #374151;
        text-decoration: none;
    }
    .dataTables_paginate .paginate_button:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }
    .dataTables_paginate .paginate_button.current {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable only if not already initialized
    if (!$.fn.DataTable.isDataTable('#rolesTable')) {
        $('#rolesTable').DataTable({
        "order": [[ 2, "desc" ]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "columnDefs": [
            { "orderable": false, "targets": 6 }
        ],
        "responsive": true,
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        "dom": '<"flex flex-col md:flex-row md:items-center md:justify-between mb-4"<"mb-4 md:mb-0"l><"mb-4 md:mb-0"f>>rt<"flex flex-col md:flex-row md:items-center md:justify-between mt-4"<"mb-4 md:mb-0"i><"mb-4 md:mb-0"p>>'
    });
    }

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
        const allPermissions = @json($permissions);
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

    // Delete role
    $('.delete-role-btn').on('click', function() {
        const roleId = $(this).data('role-id');
        const roleName = $(this).data('role-name');
        const usersCount = $(this).data('users-count');

        if (usersCount > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No se puede eliminar',
                text: `El rol "${roleName}" tiene ${usersCount} usuario(s) asignado(s). Debe reasignar los usuarios antes de eliminar el rol.`
            });
            return;
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará el rol "${roleName}". Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/developer/roles/${roleId}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(() => location.reload(), 2000);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error', response.message || 'Error al eliminar el rol', 'error');
                    }
                });
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
@endpush
@endsection
