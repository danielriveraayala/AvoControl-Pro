@extends('layouts.developer')

@section('title', 'Gestión de Respaldos')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col space-y-4 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Gestión de Respaldos</h1>
                        <p class="text-xs sm:text-sm text-gray-600">Administra respaldos del sistema y restauraciones</p>
                    </div>
                    <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.index') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← Dashboard
                        </a>
                        <button onclick="createBackup()" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            + Crear Respaldo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Last Backup Info -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V8z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Último Respaldo</h3>
                            @if($lastBackup)
                                <p class="text-sm text-gray-600">{{ $lastBackup['created_at']->diffForHumans() }}</p>
                                <p class="text-xs text-gray-500">{{ $lastBackup['size_human'] }}</p>
                            @else
                                <p class="text-sm text-red-600">No hay respaldos</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Storage Info -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Almacenamiento</h3>
                            <p class="text-sm text-gray-600">{{ $systemInfo['storage']['available_space'] }} disponible</p>
                            <p class="text-xs text-gray-500">de {{ $systemInfo['storage']['total_space'] }} total</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Info -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Base de Datos</h3>
                            <p class="text-sm text-gray-600">{{ $systemInfo['database']['database'] }}</p>
                            <p class="text-xs text-gray-500">{{ $systemInfo['database']['tables_count'] }} tablas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Acciones Rápidas</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button onclick="createBackup('database')" class="flex items-center p-4 border-2 border-blue-200 rounded-lg hover:border-blue-300 transition-colors">
                        <div class="p-2 bg-blue-100 rounded-full mr-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h4 class="font-medium text-gray-900">Respaldo de BD</h4>
                            <p class="text-sm text-gray-600">Solo base de datos</p>
                        </div>
                    </button>

                    <button onclick="createBackup('files')" class="flex items-center p-4 border-2 border-green-200 rounded-lg hover:border-green-300 transition-colors">
                        <div class="p-2 bg-green-100 rounded-full mr-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h4 class="font-medium text-gray-900">Respaldo de Archivos</h4>
                            <p class="text-sm text-gray-600">Solo archivos importantes</p>
                        </div>
                    </button>

                    <button onclick="createBackup('full')" class="flex items-center p-4 border-2 border-purple-200 rounded-lg hover:border-purple-300 transition-colors">
                        <div class="p-2 bg-purple-100 rounded-full mr-3">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div class="text-left">
                            <h4 class="font-medium text-gray-900">Respaldo Completo</h4>
                            <p class="text-sm text-gray-600">BD + Archivos</p>
                        </div>
                    </button>
                </div>

                <div class="mt-4 flex space-x-3">
                    <button onclick="cleanupBackups()" class="bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                        🧹 Limpiar Respaldos Antiguos
                    </button>
                    <button onclick="refreshBackupList()" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                        🔄 Actualizar Lista
                    </button>
                </div>
            </div>
        </div>

        <!-- Backup Files List -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Respaldos Disponibles ({{ count($backups) }})
                    </h3>
                    <div class="text-sm text-gray-500">
                        Actualizado: {{ now()->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                @if(count($backups) > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Archivo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tamaño
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha Creación
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="backupTableBody">
                            @foreach($backups as $backup)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="p-2 rounded-full {{ $backup['type'] === 'full' ? 'bg-purple-100 text-purple-600' : ($backup['type'] === 'database' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if($backup['type'] === 'full')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                    @elseif($backup['type'] === 'database')
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7"></path>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                                    @endif
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $backup['filename'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $backup['type'] === 'full' ? 'bg-purple-100 text-purple-800' : 
                                               ($backup['type'] === 'database' ? 'bg-blue-100 text-blue-800' : 
                                               'bg-green-100 text-green-800') }}">
                                            {{ ucfirst($backup['type']) }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $backup['size_human'] }}
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ $backup['created_at']->format('d/m/Y H:i') }}</div>
                                        <div class="text-xs">{{ $backup['created_at']->diffForHumans() }}</div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="downloadBackup('{{ $backup['filename'] }}')" class="text-blue-600 hover:text-blue-900">
                                                Descargar
                                            </button>
                                            <button onclick="restoreBackup('{{ $backup['filename'] }}')" class="text-green-600 hover:text-green-900">
                                                Restaurar
                                            </button>
                                            <button onclick="deleteBackup('{{ $backup['filename'] }}')" class="text-red-600 hover:text-red-900">
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V8z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay respaldos</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza creando tu primer respaldo del sistema.</p>
                        <div class="mt-6">
                            <button onclick="createBackup()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Crear Primer Respaldo
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function createBackup(type = null) {
    let defaultType = 'full';
    let defaultDescription = 'Respaldo manual desde panel de desarrollador';
    
    if (type) {
        defaultType = type;
        defaultDescription = `Respaldo ${type} desde panel de desarrollador`;
    }
    
    Swal.fire({
        title: 'Crear Nuevo Respaldo',
        html: `
            <div class="text-left">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de respaldo:</label>
                <select id="backupType" class="w-full px-3 py-2 border border-gray-300 rounded-md mb-4">
                    <option value="full" ${defaultType === 'full' ? 'selected' : ''}>Completo (BD + Archivos)</option>
                    <option value="database" ${defaultType === 'database' ? 'selected' : ''}>Solo Base de Datos</option>
                    <option value="files" ${defaultType === 'files' ? 'selected' : ''}>Solo Archivos</option>
                </select>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción:</label>
                <textarea id="backupDescription" class="w-full px-3 py-2 border border-gray-300 rounded-md" rows="3" placeholder="Descripción del respaldo...">${defaultDescription}</textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Crear Respaldo',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3b82f6',
        focusConfirm: false,
        preConfirm: () => {
            const type = document.getElementById('backupType').value;
            const description = document.getElementById('backupDescription').value;
            
            if (!description.trim()) {
                Swal.showValidationMessage('La descripción es requerida');
                return false;
            }
            
            return { type, description };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Creando respaldo...', 'Este proceso puede tomar varios minutos');
            
            fetch('{{ route("developer.backups.create") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: result.value.type,
                    description: result.value.description
                })
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                if (data.success) {
                    DevAlert.success('Respaldo Creado', data.message);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    DevAlert.error('Error', data.message);
                }
            })
            .catch(error => {
                DevAlert.close();
                handleFetchError(error);
            });
        }
    });
}

function downloadBackup(filename) {
    DevAlert.loading('Preparando descarga...', 'Por favor espera');
    
    // Create a temporary link to download the file
    const link = document.createElement('a');
    link.href = `{{ route("developer.backups.download", "") }}/${filename}`;
    link.download = filename;
    
    // Trigger download
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(() => {
        DevAlert.close();
        DevAlert.success('Descarga Iniciada', 'El archivo se está descargando');
    }, 1000);
}

function restoreBackup(filename) {
    DevAlert.confirmDanger(
        '⚠️ ¿Restaurar Respaldo?',
        `Esta acción sobrescribirá los datos actuales del sistema con el respaldo "${filename}". Esta operación NO se puede deshacer.\n\n¿Estás completamente seguro?`,
        'Sí, restaurar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Restaurando respaldo...', 'Este proceso puede tomar varios minutos. NO cerrar la ventana.');
            
            fetch(`{{ route("developer.backups.restore", "") }}/${filename}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                if (data.success) {
                    DevAlert.success('Restauración Completada', data.message);
                    setTimeout(() => location.reload(), 3000);
                } else {
                    DevAlert.error('Error en Restauración', data.message);
                }
            })
            .catch(error => {
                DevAlert.close();
                handleFetchError(error);
            });
        }
    });
}

function deleteBackup(filename) {
    DevAlert.confirmDanger(
        '¿Eliminar Respaldo?',
        `¿Estás seguro de que deseas eliminar el respaldo "${filename}"? Esta acción no se puede deshacer.`,
        'Sí, eliminar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Eliminando respaldo...', 'Por favor espera');
            
            fetch(`{{ route("developer.backups.delete", "") }}/${filename}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                if (data.success) {
                    DevAlert.success('Respaldo Eliminado', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    DevAlert.error('Error', data.message);
                }
            })
            .catch(error => {
                DevAlert.close();
                handleFetchError(error);
            });
        }
    });
}

function cleanupBackups() {
    Swal.fire({
        title: 'Limpiar Respaldos Antiguos',
        html: `
            <div class="text-left">
                <label class="block text-sm font-medium text-gray-700 mb-2">Mantener respaldos de los últimos:</label>
                <select id="daysToKeep" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="7">7 días</option>
                    <option value="15">15 días</option>
                    <option value="30" selected>30 días</option>
                    <option value="60">60 días</option>
                    <option value="90">90 días</option>
                </select>
                <p class="text-sm text-gray-500 mt-2">Los respaldos más antiguos serán eliminados permanentemente.</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Limpiar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#f59e0b',
        focusConfirm: false,
        preConfirm: () => {
            return document.getElementById('daysToKeep').value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Limpiando respaldos...', 'Por favor espera');
            
            fetch('{{ route("developer.backups.cleanup") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ days: result.value })
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                handleAjaxResponse(data);
                if (data.success) {
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .catch(error => {
                DevAlert.close();
                handleFetchError(error);
            });
        }
    });
}

function refreshBackupList() {
    DevAlert.loading('Actualizando lista...', 'Por favor espera');
    setTimeout(() => {
        location.reload();
    }, 1000);
}
</script>
@endpush
@endsection