@extends('layouts.developer')

@section('title', isset($clonedFrom) ? 'Clonar Rol' : 'Crear Nuevo Rol')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ isset($clonedFrom) ? 'Clonar Rol: ' . $clonedFrom->display_name : 'Crear Nuevo Rol' }}
                        </h1>
                        <p class="text-sm text-gray-600">
                            {{ isset($clonedFrom) ? 'Crea un nuevo rol basado en uno existente' : 'Define un nuevo rol con permisos específicos' }}
                        </p>
                    </div>
                    <a href="{{ route('developer.roles.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        ← Volver
                    </a>
                </div>
            </div>
        </div>

    <form action="{{ route('developer.roles.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <!-- Basic Information -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Información Básica</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nombre del Rol <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', isset($clonedFrom) ? '' : '') }}"
                                   placeholder="ej: content_manager"
                                   pattern="[a-z_]+"
                                   required>
                            <small class="form-text text-muted">
                                Solo letras minúsculas y guiones bajos
                            </small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="display_name">Nombre para Mostrar <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('display_name') is-invalid @enderror" 
                                   id="display_name" 
                                   name="display_name" 
                                   value="{{ old('display_name', isset($clonedFrom) ? $clonedFrom->display_name . ' (Copia)' : '') }}"
                                   placeholder="ej: Gestor de Contenido"
                                   required>
                            @error('display_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Describe las responsabilidades de este rol">{{ old('description', isset($clonedFrom) ? $clonedFrom->description : '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="hierarchy_level">Nivel de Jerarquía <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('hierarchy_level') is-invalid @enderror" 
                                   id="hierarchy_level" 
                                   name="hierarchy_level" 
                                   value="{{ old('hierarchy_level', isset($clonedFrom) ? $clonedFrom->hierarchy_level : '') }}"
                                   min="1" 
                                   max="99"
                                   required>
                            <small class="form-text text-muted">
                                1-99 (Mayor número = mayor jerarquía)
                            </small>
                            @error('hierarchy_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(isset($clonedFrom))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Clonando desde: <strong>{{ $clonedFrom->display_name }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Permisos</h6>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-btn">
                                <i class="fas fa-check-square mr-1"></i>Seleccionar Todos
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-btn">
                                <i class="fas fa-square mr-1"></i>Deseleccionar Todos
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($permissions as $module => $modulePermissions)
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-light py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <i class="fas fa-folder mr-2"></i>
                                                {{ ucfirst($module) }}
                                            </h6>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" 
                                                       class="custom-control-input module-toggle" 
                                                       id="module-{{ $module }}" 
                                                       data-module="{{ $module }}">
                                                <label class="custom-control-label" for="module-{{ $module }}">
                                                    Todos
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @foreach($modulePermissions as $permission)
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" 
                                                   class="custom-control-input permission-checkbox" 
                                                   id="perm-{{ $permission->id }}" 
                                                   name="permissions[]" 
                                                   value="{{ $permission->id }}"
                                                   data-module="{{ $module }}"
                                                   {{ (old('permissions') && in_array($permission->id, old('permissions'))) || 
                                                      (isset($selectedPermissions) && in_array($permission->id, $selectedPermissions)) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="perm-{{ $permission->id }}">
                                                {{ $permission->display_name }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body text-right">
                        <a href="{{ route('developer.roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Crear Rol
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Module toggle functionality
    $('.module-toggle').on('change', function() {
        const module = $(this).data('module');
        const isChecked = $(this).prop('checked');
        $(`.permission-checkbox[data-module="${module}"]`).prop('checked', isChecked);
    });

    // Update module toggles when individual permissions change
    $('.permission-checkbox').on('change', function() {
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

    // Select all permissions
    $('#select-all-btn').on('click', function() {
        $('.permission-checkbox').prop('checked', true);
        $('.module-toggle').prop('checked', true);
    });

    // Deselect all permissions
    $('#deselect-all-btn').on('click', function() {
        $('.permission-checkbox').prop('checked', false);
        $('.module-toggle').prop('checked', false);
    });

    // Initialize module toggles on page load
    updateModuleToggles();

    // Auto-generate name from display_name
    $('#display_name').on('keyup', function() {
        if ($('#name').val() === '') {
            const name = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]/g, '_')
                .replace(/_+/g, '_')
                .replace(/^_|_$/g, '');
            $('#name').val(name);
        }
    });
});
</script>
@endpush
@endsection