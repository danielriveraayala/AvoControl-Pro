@extends('layouts.developer')

@section('title', 'Gestión de Tenants')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col space-y-4 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Gestión de Tenants</h1>
                        <p class="text-xs sm:text-sm text-gray-600">Administra suscripciones y planes de empresas</p>
                    </div>
                    <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.index') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← Dashboard
                        </a>
                        <button onclick="createTenant()" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            + Nuevo Tenant
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-full">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m5 0v-5a2 2 0 00-2-2H8a2 2 0 00-2 2v5m3 0h4z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-lg font-semibold text-gray-900">{{ $stats['total'] }}</p>
                        <p class="text-xs text-gray-600">Total</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-full">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-lg font-semibold text-gray-900">{{ $stats['active'] }}</p>
                        <p class="text-xs text-gray-600">Activos</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-full">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-lg font-semibold text-gray-900">{{ $stats['trial'] }}</p>
                        <p class="text-xs text-gray-600">Trial</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-full">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-lg font-semibold text-gray-900">{{ $stats['suspended'] }}</p>
                        <p class="text-xs text-gray-600">Suspendidos</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-gray-100 rounded-full">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-lg font-semibold text-gray-900">{{ $stats['expired'] }}</p>
                        <p class="text-xs text-gray-600">Expirados</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="p-4 sm:p-6">
                <form method="GET" action="{{ route('developer.tenants.index') }}" class="space-y-4 sm:space-y-0 sm:flex sm:space-x-4 sm:items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" name="search" value="{{ $search }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Nombre, dominio, contacto...">
                    </div>
                    <div class="sm:w-48">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los estados</option>
                            <option value="trial" {{ $status === 'trial' ? 'selected' : '' }}>Trial</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Activo</option>
                            <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspendido</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                            <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expirado</option>
                        </select>
                    </div>
                    <div class="sm:w-48">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
                        <select name="plan" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los planes</option>
                            @foreach($plans as $planOption)
                                <option value="{{ $planOption->id }}" {{ $plan == $planOption->id ? 'selected' : '' }}>
                                    {{ $planOption->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Filtrar
                        </button>
                        <a href="{{ route('developer.tenants.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tenants List -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($subscriptions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tenant
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Plan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Uso
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Creado
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($subscriptions as $subscription)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="p-2 bg-blue-100 rounded-full">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m5 0v-5a2 2 0 00-2-2H8a2 2 0 00-2 2v5m3 0h4z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $subscription->tenant_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $subscription->tenant_domain }}.avocontrol.com</div>
                                                <div class="text-xs text-gray-400">{{ $subscription->contact_name }} ({{ $subscription->contact_email }})</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $subscription->plan->display_name ?? 'Sin plan' }}
                                        </span>
                                        @if($subscription->plan)
                                            <div class="text-xs text-gray-500 mt-1">${{ number_format($subscription->plan->price, 2) }}/mes</div>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $subscription->status_color === 'green' ? 'bg-green-100 text-green-800' : 
                                               ($subscription->status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($subscription->status_color === 'red' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                            {{ $subscription->status_display }}
                                        </span>
                                        @if($subscription->isTrial() && $subscription->trial_days_remaining > 0)
                                            <div class="text-xs text-gray-500 mt-1">{{ $subscription->trial_days_remaining }} días restantes</div>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center space-x-2">
                                            <div class="text-xs">
                                                <div>{{ $subscription->current_users }} usuarios</div>
                                                <div class="text-gray-500">{{ $subscription->lots_this_month }} lotes/mes</div>
                                            </div>
                                            @if($subscription->isOverLimits())
                                                <span class="text-red-500">⚠️</span>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ $subscription->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs">{{ $subscription->created_at->diffForHumans() }}</div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('developer.tenants.show', $subscription) }}" class="text-blue-600 hover:text-blue-900">Ver</a>
                                            <a href="{{ route('developer.tenants.edit', $subscription) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                            @if($subscription->isSuspended())
                                                <button onclick="activateTenant({{ $subscription->id }})" class="text-green-600 hover:text-green-900">Activar</button>
                                            @elseif($subscription->isActive())
                                                <button onclick="suspendTenant({{ $subscription->id }})" class="text-yellow-600 hover:text-yellow-900">Suspender</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($subscriptions->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $subscriptions->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m5 0v-5a2 2 0 00-2-2H8a2 2 0 00-2 2v5m3 0h4z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay tenants registrados</h3>
                    <p class="mt-1 text-sm text-gray-500">Comienza creando tu primer tenant.</p>
                    <div class="mt-6">
                        <button onclick="createTenant()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Crear Primer Tenant
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function createTenant() {
    window.location.href = "{{ route('developer.tenants.create') }}";
}

function suspendTenant(tenantId) {
    Swal.fire({
        title: 'Suspender Tenant',
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
            DevAlert.loading('Suspendiendo tenant...', 'Por favor espera');
            
            fetch(`/developer/tenants/${tenantId}/suspend`, {
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

function activateTenant(tenantId) {
    DevAlert.confirm(
        '¿Activar Tenant?',
        '¿Estás seguro de que deseas activar este tenant? Tendrá acceso completo al sistema.',
        'Sí, activar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Activando tenant...', 'Por favor espera');
            
            fetch(`/developer/tenants/${tenantId}/activate`, {
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
</script>
@endpush
@endsection