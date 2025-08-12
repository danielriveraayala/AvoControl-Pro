@extends('layouts.developer')

@section('title', 'Gestión de Notificaciones')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Gestión de Notificaciones</h1>
                        <p class="text-sm text-gray-600">Administra todas las notificaciones del sistema (email y push)</p>
                    </div>
                    <a href="{{ route('developer.config.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        ← Volver a Configuración
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Aquí puedes ver y gestionar todas las notificaciones del sistema. Las notificaciones se envían automáticamente según las tareas programadas en el cron.
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filtros de Búsqueda</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label for="filter-type" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select id="filter-type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Todos los tipos</option>
                            <option value="inventory">Inventario</option>
                            <option value="sales">Ventas</option>
                            <option value="payment">Pagos</option>
                            <option value="system">Sistema</option>
                            <option value="report">Reportes</option>
                            <option value="test">Prueba</option>
                        </select>
                    </div>
                    <div>
                        <label for="filter-priority" class="block text-sm font-medium text-gray-700 mb-1">Prioridad</label>
                        <select id="filter-priority" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Todas las prioridades</option>
                            <option value="low">Baja</option>
                            <option value="normal">Normal</option>
                            <option value="high">Alta</option>
                            <option value="critical">Crítica</option>
                        </select>
                    </div>
                    <div>
                        <label for="filter-channels" class="block text-sm font-medium text-gray-700 mb-1">Canal</label>
                        <select id="filter-channels" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Todos los canales</option>
                            <option value="email">Email</option>
                            <option value="push">Push</option>
                            <option value="database">Base de datos</option>
                        </select>
                    </div>
                    <div>
                        <label for="filter-status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select id="filter-status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Todos los estados</option>
                            <option value="pending">Pendiente</option>
                            <option value="sent">Enviado</option>
                            <option value="failed">Fallido</option>
                            <option value="scheduled">Programado</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="filter-date-from" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                        <input type="date" id="filter-date-from" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="filter-date-to" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                        <input type="date" id="filter-date-to" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="button" id="clear-filters" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-md transition-colors">
                            Limpiar Filtros
                        </button>
                        <button type="button" id="refresh-table" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 19H6a2 2 0 01-2-2V7a2 2 0 012-2h6m5-2l2.5 2.5L16 9"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Notificaciones</dt>
                                <dd class="text-lg font-medium text-gray-900" id="total-notifications">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Enviadas</dt>
                                <dd class="text-lg font-medium text-gray-900" id="sent-notifications">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pendientes</dt>
                                <dd class="text-lg font-medium text-gray-900" id="pending-notifications">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-red-100 rounded-full">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Fallidas</dt>
                                <dd class="text-lg font-medium text-gray-900" id="failed-notifications">-</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Lista de Notificaciones</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="notificationsTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mensaje</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Canales</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- DataTables content -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Notification Details Modal -->
<div id="notificationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Detalles de la Notificación</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal()">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mt-4" id="notificationModalBody">
                <!-- Notification details will be loaded here -->
            </div>
            <div class="mt-6 flex justify-end">
                <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-md" onclick="closeModal()">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let notificationsTable;

document.addEventListener('DOMContentLoaded', function() {
    initializeNotificationsTable();
    loadNotificationsStats();
    
    // Filter change events
    const filters = ['filter-type', 'filter-priority', 'filter-channels', 'filter-status', 'filter-date-from', 'filter-date-to'];
    filters.forEach(filterId => {
        document.getElementById(filterId).addEventListener('change', function() {
            if (notificationsTable) {
                notificationsTable.ajax.reload();
            }
        });
    });
    
    // Clear filters
    document.getElementById('clear-filters').addEventListener('click', function() {
        ['filter-type', 'filter-priority', 'filter-channels', 'filter-status', 'filter-date-from', 'filter-date-to'].forEach(id => {
            document.getElementById(id).value = '';
        });
        if (notificationsTable) {
            notificationsTable.ajax.reload();
        }
    });
    
    // Refresh table
    document.getElementById('refresh-table').addEventListener('click', function() {
        if (notificationsTable) {
            notificationsTable.ajax.reload();
            loadNotificationsStats();
        }
    });
});

function initializeNotificationsTable() {
    // Wait for jQuery and DataTables to be available
    if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
        console.log('Waiting for DataTables to load...');
        setTimeout(initializeNotificationsTable, 100);
        return;
    }
    
    notificationsTable = $('#notificationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("developer.config.notifications-manager.data") }}',
            data: function(d) {
                d.type = document.getElementById('filter-type').value;
                d.priority = document.getElementById('filter-priority').value;
                d.channels = document.getElementById('filter-channels').value;
                d.status = document.getElementById('filter-status').value;
                d.date_from = document.getElementById('filter-date-from').value;
                d.date_to = document.getElementById('filter-date-to').value;
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'type', name: 'type', orderable: false },
            { data: 'title', name: 'title' },
            { data: 'message', name: 'message' },
            { data: 'user', name: 'user', orderable: false },
            { data: 'priority', name: 'priority', orderable: false },
            { data: 'channels', name: 'channels', orderable: false },
            { data: 'status', name: 'status', orderable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25,
        order: [[8, 'desc']],
        drawCallback: function() {
            loadNotificationsStats();
        }
    });
}

function createFallbackTable() {
    // Create a simple table without DataTables for fallback
    document.querySelector('#notificationsTable tbody').innerHTML = `
        <tr>
            <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293L16 16a2 2 0 01-1.414.586H9.414A2 2 0 018 16l-2.707-2.707A1 1 0 004.586 13H2"/>
                    </svg>
                    <p>No hay notificaciones disponibles en este momento</p>
                    <p class="text-sm">Las notificaciones aparecerán aquí cuando se generen automáticamente por el sistema</p>
                </div>
            </td>
        </tr>
    `;
}

function loadNotificationsStats() {
    // Update stats - for now just show placeholders
    document.getElementById('total-notifications').textContent = '0';
    document.getElementById('sent-notifications').textContent = '0';
    document.getElementById('pending-notifications').textContent = '0';
    document.getElementById('failed-notifications').textContent = '0';
    
    // This would typically be an API call to get real statistics
    // fetch('/developer/config/notifications-manager/stats')
    //     .then(response => response.json())
    //     .then(data => {
    //         document.getElementById('total-notifications').textContent = data.total || '0';
    //         document.getElementById('sent-notifications').textContent = data.sent || '0';
    //         document.getElementById('pending-notifications').textContent = data.pending || '0';
    //         document.getElementById('failed-notifications').textContent = data.failed || '0';
    //     });
}

function viewNotification(id) {
    // Show modal
    document.getElementById('notificationModal').classList.remove('hidden');
    
    // Show loading
    document.getElementById('notificationModalBody').innerHTML = `
        <div class="flex justify-center items-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Cargando detalles...</span>
        </div>
    `;
    
    // Here you would fetch the notification details via AJAX
    // For now, showing a placeholder
    setTimeout(() => {
        document.getElementById('notificationModalBody').innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-500">ID:</span>
                        <span class="ml-2 text-sm text-gray-900">${id}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Fecha de creación:</span>
                        <span class="ml-2 text-sm text-gray-900">--</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Tipo:</span>
                        <span class="ml-2 text-sm text-gray-900">--</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Prioridad:</span>
                        <span class="ml-2 text-sm text-gray-900">--</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Usuario:</span>
                        <span class="ml-2 text-sm text-gray-900">--</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Canales:</span>
                        <span class="ml-2 text-sm text-gray-900">--</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Estado:</span>
                        <span class="ml-2 text-sm text-gray-900">--</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Intentos de envío:</span>
                        <span class="ml-2 text-sm text-gray-900">--</span>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200 pt-4">
                <div class="mb-4">
                    <span class="text-sm font-medium text-gray-500">Título:</span>
                    <p class="mt-1 text-sm text-gray-900">Título de la notificación</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Mensaje:</span>
                    <p class="mt-1 text-sm text-gray-900">Contenido completo del mensaje de la notificación...</p>
                </div>
            </div>
        `;
    }, 1000);
}

function closeModal() {
    document.getElementById('notificationModal').classList.add('hidden');
}

function deleteNotification(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta notificación? Esta acción no se puede deshacer.')) {
        fetch(`{{ route('developer.config.notifications-manager.delete', '') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', 'Notificación eliminada correctamente');
                notificationsTable.ajax.reload();
                loadNotificationsStats();
            } else {
                showNotification('error', data.message || 'Error al eliminar la notificación');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Error al eliminar la notificación');
        });
    }
}

function showNotification(type, message) {
    // Create notification element
    const notification = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    
    notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-md shadow-lg z-50 transform transition-transform duration-300 translate-x-full`;
    notification.innerHTML = `
        <div class="flex items-center">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Slide in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}
</script>
@endpush

@push('styles')
<!-- DataTables with Tailwind styling -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
/* DataTables Tailwind integration */
.dataTables_wrapper {
    @apply text-sm;
}
.dataTables_length select,
.dataTables_filter input {
    @apply border border-gray-300 rounded-md px-3 py-2 text-sm;
}
.dataTables_paginate .paginate_button {
    @apply px-3 py-1 mx-1 text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50;
}
.dataTables_paginate .paginate_button.current {
    @apply bg-blue-500 text-white border-blue-500;
}
.dataTables_info {
    @apply text-gray-600 text-sm;
}
</style>
@endpush

@push('scripts')
<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
@endpush