@extends('layouts.developer')

@section('title', 'Gestión de Roles')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Gestión de Roles y Permisos</h1>
                    <p class="mb-0 text-muted">Administra los roles del sistema y sus permisos asociados</p>
                </div>
                <a href="{{ route('developer.roles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Crear Nuevo Rol
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Roles
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $roles->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Roles del Sistema
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $roles->where('is_system', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-lock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Roles Personalizados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $roles->where('is_system', false)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users-cog fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Permisos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $permissions->flatten()->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-key fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Roles del Sistema</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="rolesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th>Descripción</th>
                            <th>Jerarquía</th>
                            <th>Usuarios</th>
                            <th>Permisos</th>
                            <th>Tipo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($role->name === 'super_admin')
                                        <i class="fas fa-crown text-warning mr-2"></i>
                                    @elseif($role->hierarchy_level >= 90)
                                        <i class="fas fa-star text-primary mr-2"></i>
                                    @elseif($role->hierarchy_level >= 70)
                                        <i class="fas fa-user-tie text-info mr-2"></i>
                                    @else
                                        <i class="fas fa-user text-secondary mr-2"></i>
                                    @endif
                                    <div>
                                        <div class="font-weight-bold">{{ $role->display_name }}</div>
                                        <small class="text-muted">{{ $role->name }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $role->description ?? 'Sin descripción' }}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $role->hierarchy_level >= 90 ? 'danger' : ($role->hierarchy_level >= 70 ? 'warning' : ($role->hierarchy_level >= 50 ? 'info' : 'success')) }}" 
                                         role="progressbar" 
                                         style="width: {{ $role->hierarchy_level }}%"
                                         aria-valuenow="{{ $role->hierarchy_level }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ $role->hierarchy_level }}
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary">{{ $role->users->count() }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $role->permissions->count() }}</span>
                            </td>
                            <td class="text-center">
                                @if($role->is_system)
                                    <span class="badge badge-secondary">Sistema</span>
                                @else
                                    <span class="badge badge-success">Personalizado</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('developer.roles.show', $role) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(!$role->is_system || $role->name === 'super_admin')
                                    <button type="button" 
                                            class="btn btn-sm btn-warning edit-permissions-btn"
                                            data-role-id="{{ $role->id }}"
                                            data-role-name="{{ $role->display_name }}"
                                            title="Editar permisos">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    @endif
                                    
                                    @if(!$role->is_system)
                                    <a href="{{ route('developer.roles.edit', $role) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="Editar rol">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    
                                    <a href="{{ route('developer.roles.clone', $role) }}" 
                                       class="btn btn-sm btn-success" 
                                       title="Clonar rol">
                                        <i class="fas fa-copy"></i>
                                    </a>
                                    
                                    @if(!$role->is_system)
                                    <button type="button" 
                                            class="btn btn-sm btn-danger delete-role-btn"
                                            data-role-id="{{ $role->id }}"
                                            data-role-name="{{ $role->display_name }}"
                                            data-users-count="{{ $role->users->count() }}"
                                            title="Eliminar rol">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

<!-- Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1" role="dialog" aria-labelledby="permissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permissionsModalLabel">Editar Permisos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="permissions-container">
                    <!-- Permissions will be loaded here via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="save-permissions-btn">
                    <i class="fas fa-save mr-2"></i>Guardar Permisos
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet">
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
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#rolesTable').DataTable({
        "order": [[ 2, "desc" ]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "columnDefs": [
            { "orderable": false, "targets": 6 }
        ]
    });

    // Edit permissions modal
    let currentRoleId = null;
    
    $('.edit-permissions-btn').on('click', function() {
        currentRoleId = $(this).data('role-id');
        const roleName = $(this).data('role-name');
        
        $('#permissionsModalLabel').text('Editar Permisos - ' + roleName);
        $('#permissions-container').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
        $('#permissionsModal').modal('show');
        
        // Load role details and permissions
        $.get(`/developer/roles/${currentRoleId}/details`)
            .done(function(response) {
                if (response.success) {
                    renderPermissions(response.role);
                }
            })
            .fail(function(error) {
                Swal.fire('Error', 'No se pudieron cargar los permisos', 'error');
                $('#permissionsModal').modal('hide');
            });
    });

    // Render permissions checkboxes
    function renderPermissions(role) {
        let html = '<div class="row">';
        
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
            html += '<div class="col-md-6 permission-module">';
            html += `<div class="card">`;
            html += `<div class="card-header permission-module-header" data-module="${module}">`;
            html += `<div class="d-flex justify-content-between align-items-center">`;
            html += `<h6 class="mb-0"><i class="fas fa-folder mr-2"></i>${formatModuleName(module)}</h6>`;
            html += `<div class="custom-control custom-switch">`;
            html += `<input type="checkbox" class="custom-control-input module-toggle" id="module-${module}" data-module="${module}">`;
            html += `<label class="custom-control-label" for="module-${module}">Todos</label>`;
            html += `</div>`;
            html += `</div>`;
            html += `</div>`;
            html += `<div class="card-body">`;
            
            allPermissions[module].forEach(function(permission) {
                const isChecked = rolePermissionIds.includes(permission.id);
                html += `<div class="permission-item">`;
                html += `<div class="custom-control custom-checkbox">`;
                html += `<input type="checkbox" class="custom-control-input permission-checkbox" `;
                html += `id="perm-${permission.id}" value="${permission.id}" data-module="${module}" `;
                html += isChecked ? 'checked' : '';
                html += `>`;
                html += `<label class="custom-control-label" for="perm-${permission.id}">`;
                html += `${permission.display_name}`;
                html += `</label>`;
                html += `</div>`;
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
                    $('#permissionsModal').modal('hide');
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
</script>
@endpush
@endsection