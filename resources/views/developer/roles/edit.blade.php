@extends('layouts.developer')

@section('title', 'Editar Rol')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">Editar Rol: {{ $role->display_name }}</h1>
                        <p class="text-xs sm:text-sm text-gray-600">Modifica la información y permisos del rol</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.roles.show', $role) }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← <span class="ml-1">Volver</span>
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

        <form action="{{ route('developer.roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                <!-- Basic Information -->
                <div class="lg:col-span-1">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900">Información Básica</h3>
                        </div>
                        <div class="px-4 sm:px-6 py-4 space-y-4 sm:space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nombre del Rol</label>
                                <input type="text" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500" 
                                       value="{{ $role->name }}"
                                       disabled>
                                <p class="mt-1 text-sm text-gray-500">El nombre del rol no se puede modificar</p>
                            </div>

                            <div>
                                <label for="display_name" class="block text-sm font-medium text-gray-700">
                                    Nombre para Mostrar <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('display_name') border-red-500 @enderror" 
                                       id="display_name" 
                                       name="display_name" 
                                       value="{{ old('display_name', $role->display_name) }}"
                                       required>
                                @error('display_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                                <textarea class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3"
                                          placeholder="Describe las responsabilidades de este rol">{{ old('description', $role->description) }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="hierarchy_level" class="block text-sm font-medium text-gray-700">
                                    Nivel de Jerarquía <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('hierarchy_level') border-red-500 @enderror" 
                                       id="hierarchy_level" 
                                       name="hierarchy_level" 
                                       value="{{ old('hierarchy_level', $role->hierarchy_level) }}"
                                       min="1" 
                                       max="99"
                                       required>
                                <p class="mt-1 text-sm text-gray-500">1-99 (Mayor número = mayor jerarquía)</p>
                                @error('hierarchy_level')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            Editando: <strong>{{ $role->display_name }}</strong>
                                        </p>
                                        <p class="text-xs text-blue-600 mt-1">
                                            {{ $role->users->count() }} usuarios asignados
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-0">Permisos</h3>
                                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 w-full sm:w-auto">
                                    <button type="button" class="inline-flex items-center justify-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700" id="select-all-btn">
                                        <i class="fas fa-check-square mr-1"></i>Todos
                                    </button>
                                    <button type="button" class="inline-flex items-center justify-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700" id="deselect-all-btn">
                                        <i class="fas fa-square mr-1"></i>Ninguno
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 sm:px-6 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                                @foreach($permissions as $module => $modulePermissions)
                                @php
                                    $rolePermissionIds = $role->permissions->pluck('id')->toArray();
                                @endphp
                                <div class="border border-gray-200 rounded-lg">
                                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                        <div class="flex justify-between items-center">
                                            <h4 class="text-sm font-semibold text-gray-900 flex items-center">
                                                <i class="fas fa-folder mr-2 text-gray-600"></i>
                                                {{ ucfirst($module) }}
                                            </h4>
                                            <div class="flex items-center">
                                                <input type="checkbox" 
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded module-toggle" 
                                                       id="module-{{ $module }}" 
                                                       data-module="{{ $module }}">
                                                <label for="module-{{ $module }}" class="ml-2 text-sm text-gray-700">
                                                    Todos
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-4 py-3">
                                        <div class="space-y-3">
                                            @foreach($modulePermissions as $permission)
                                            <div class="flex items-center">
                                                <input type="checkbox" 
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded permission-checkbox" 
                                                       id="perm-{{ $permission->id }}" 
                                                       name="permissions[]" 
                                                       value="{{ $permission->id }}"
                                                       data-module="{{ $module }}"
                                                       {{ in_array($permission->id, $rolePermissionIds) ? 'checked' : '' }}>
                                                <label for="perm-{{ $permission->id }}" class="ml-2 text-sm text-gray-900">
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
            <div class="mt-6">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 sm:px-6 py-4">
                        <div class="flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                            <a href="{{ route('developer.roles.show', $role) }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
});
</script>
@endpush
@endsection