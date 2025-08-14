@extends('layouts.developer')

@section('title', 'Gestión de Planes')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                            <i class="fas fa-layer-group text-blue-600 mr-2"></i>
                            Gestión de Planes
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Administra los planes de suscripción del sistema</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <a href="{{ route('developer.index') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 self-start">
                            ← <span class="ml-1">Dashboard</span>
                        </a>
                        <a href="{{ route('developer.plans.create') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 self-start">
                            <i class="fas fa-plus mr-1 sm:mr-2"></i>
                            <span class="hidden sm:inline">Crear</span> Plan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
            @forelse($plans as $plan)
                <div class="bg-white shadow rounded-lg border {{ $plan->is_featured ? 'border-yellow-400 ring-2 ring-yellow-200' : 'border-gray-200' }}">
                    <!-- Plan Header -->
                    <div class="px-6 py-4 border-b border-gray-200" style="background: linear-gradient(135deg, {{ $plan->color }}22, {{ $plan->color }}11);">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <i class="{{ $plan->icon }} text-2xl mr-3" style="color: {{ $plan->color }};"></i>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $plan->name }}</h3>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-2xl font-bold" style="color: {{ $plan->color }};">{{ $plan->formatted_price }}</span>
                                        @if($plan->trial_days > 0)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                                {{ $plan->trial_days }} días gratis
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-1">
                                @if($plan->is_featured)
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                                        <i class="fas fa-star mr-1"></i>Destacado
                                    </span>
                                @endif
                                @if($plan->is_custom)
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">
                                        <i class="fas fa-cog mr-1"></i>Personalizado
                                    </span>
                                @endif
                                <span class="px-2 py-1 text-xs rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $plan->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>
                        @if($plan->description)
                            <p class="text-sm text-gray-600">{{ $plan->description }}</p>
                        @endif
                    </div>

                    <!-- Plan Limits -->
                    <div class="px-6 py-4">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Límites</h4>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Usuarios:</span>
                                <span class="font-medium">{{ $plan->getLimitDisplay('max_users') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Lotes/mes:</span>
                                <span class="font-medium">{{ $plan->getLimitDisplay('max_lots_per_month') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Almacenamiento:</span>
                                <span class="font-medium">{{ $plan->getLimitDisplay('max_storage_gb') }}{{ $plan->max_storage_gb > 0 ? ' GB' : '' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ubicaciones:</span>
                                <span class="font-medium">{{ $plan->max_locations }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Plan Features -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Funcionalidades</h4>
                        @if(count($plan->features ?? []) > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($plan->features, 0, 6) as $feature)
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">
                                        {{ $feature }}
                                    </span>
                                @endforeach
                                @if(count($plan->features) > 6)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded">
                                        +{{ count($plan->features) - 6 }} más
                                    </span>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-gray-500">Sin funcionalidades definidas</p>
                        @endif
                    </div>

                    <!-- PayPal Status -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">PayPal</h4>
                                <div class="flex items-center mt-1">
                                    @if($plan->paypal_plan_id)
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                            <i class="fas fa-check mr-1"></i>Sincronizado
                                        </span>
                                        <span class="ml-2 text-xs text-gray-500">{{ substr($plan->paypal_plan_id, 0, 15) }}...</span>
                                    @else
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">
                                            <i class="fas fa-clock mr-1"></i>No sincronizado
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                @if($plan->paypal_plan_id)
                                    <button onclick="unsyncFromPayPal({{ $plan->id }})" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded">
                                        <i class="fas fa-unlink mr-1"></i>Desincronizar
                                    </button>
                                @elseif($plan->price > 0)
                                    <button onclick="syncWithPayPal({{ $plan->id }})" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded">
                                        <i class="fas fa-sync mr-1"></i>Sincronizar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('developer.plans.show', $plan) }}" class="flex-1 min-w-0 inline-flex items-center justify-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs rounded">
                                <i class="fas fa-eye mr-1"></i>Ver
                            </a>
                            <a href="{{ route('developer.plans.edit', $plan) }}" class="flex-1 min-w-0 inline-flex items-center justify-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded">
                                <i class="fas fa-edit mr-1"></i>Editar
                            </a>
                            <button onclick="toggleStatus({{ $plan->id }})" class="flex-1 min-w-0 inline-flex items-center justify-center px-3 py-2 {{ $plan->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white text-xs rounded">
                                <i class="fas fa-{{ $plan->is_active ? 'pause' : 'play' }} mr-1"></i>{{ $plan->is_active ? 'Pausar' : 'Activar' }}
                            </button>
                            <div class="relative">
                                <button onclick="toggleDropdown({{ $plan->id }})" class="inline-flex items-center justify-center px-3 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-xs rounded">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="dropdown-{{ $plan->id }}" class="hidden absolute right-0 bottom-full mb-1 w-48 bg-white rounded-md shadow-lg z-10 border">
                                    <button onclick="duplicatePlan({{ $plan->id }})" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-t-md">
                                        <i class="fas fa-copy mr-2"></i>Duplicar Plan
                                    </button>
                                    @if($plan->canBeDeleted())
                                        <button onclick="deletePlan({{ $plan->id }}, '{{ $plan->name }}')" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-b-md">
                                            <i class="fas fa-trash mr-2"></i>Eliminar Plan
                                        </button>
                                    @else
                                        <div class="px-4 py-2 text-sm text-gray-400 cursor-not-allowed rounded-b-md">
                                            <i class="fas fa-lock mr-2"></i>No se puede eliminar
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-12 bg-white rounded-lg shadow">
                        <i class="fas fa-layer-group text-gray-400 text-6xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay planes configurados</h3>
                        <p class="text-gray-500 mb-6">Crea tu primer plan de suscripción para comenzar</p>
                        <a href="{{ route('developer.plans.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i>Crear Primer Plan
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Quick Stats -->
        @if($plans->count() > 0)
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Estadísticas de Planes</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $plans->count() }}</div>
                            <div class="text-sm text-gray-600">Total de Planes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $plans->where('is_active', true)->count() }}</div>
                            <div class="text-sm text-gray-600">Planes Activos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600">{{ $plans->where('is_featured', true)->count() }}</div>
                            <div class="text-sm text-gray-600">Planes Destacados</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $plans->whereNotNull('paypal_plan_id')->count() }}</div>
                            <div class="text-sm text-gray-600">Sincronizados PayPal</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleDropdown(planId) {
    const dropdown = document.getElementById(`dropdown-${planId}`);
    // Close all other dropdowns
    document.querySelectorAll('[id^="dropdown-"]').forEach(d => {
        if (d.id !== `dropdown-${planId}`) {
            d.classList.add('hidden');
        }
    });
    dropdown.classList.toggle('hidden');
}

function toggleStatus(planId) {
    DevAlert.confirm(
        '¿Cambiar estado del plan?',
        '¿Estás seguro de que quieres cambiar el estado de este plan?',
        'Sí, cambiar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Actualizando...', 'Cambiando estado del plan');
            
            fetch(`{{ url('/developer/plans') }}/${planId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                if (data.success) {
                    DevAlert.success('¡Éxito!', data.message).then(() => {
                        location.reload();
                    });
                } else {
                    DevAlert.error('Error', data.message);
                }
            })
            .catch(error => {
                DevAlert.close();
                DevAlert.error('Error', 'Error al actualizar el estado del plan');
            });
        }
    });
}

function duplicatePlan(planId) {
    DevAlert.confirm(
        '¿Duplicar plan?',
        'Se creará una copia de este plan que podrás editar.',
        'Sí, duplicar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Duplicando...', 'Creando copia del plan');
            
            fetch(`{{ url('/developer/plans') }}/${planId}/duplicate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                if (data.success) {
                    DevAlert.success('¡Plan duplicado!', data.message).then(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    DevAlert.error('Error', data.message);
                }
            })
            .catch(error => {
                DevAlert.close();
                DevAlert.error('Error', 'Error al duplicar el plan');
            });
        }
    });
}

function deletePlan(planId, planName) {
    DevAlert.confirm(
        '¿Eliminar plan?',
        `¿Estás seguro de que quieres eliminar el plan "${planName}"? Esta acción no se puede deshacer.`,
        'Sí, eliminar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Eliminando...', 'Eliminando plan del sistema');
            
            fetch(`{{ url('/developer/plans') }}/${planId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                if (data.success) {
                    DevAlert.success('¡Plan eliminado!', data.message).then(() => {
                        location.reload();
                    });
                } else {
                    DevAlert.error('Error', data.message);
                }
            })
            .catch(error => {
                DevAlert.close();
                DevAlert.error('Error', 'Error al eliminar el plan');
            });
        }
    });
}

function syncWithPayPal(planId) {
    DevAlert.confirm(
        '¿Sincronizar con PayPal?',
        'Se creará este plan en PayPal para permitir suscripciones.',
        'Sí, sincronizar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Sincronizando...', 'Creando plan en PayPal');
            
            fetch(`{{ url('/developer/plans') }}/${planId}/sync-paypal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                if (data.success) {
                    DevAlert.success('¡Sincronizado!', data.message).then(() => {
                        location.reload();
                    });
                } else {
                    DevAlert.error('Error', data.message);
                }
            })
            .catch(error => {
                DevAlert.close();
                DevAlert.error('Error', 'Error al sincronizar con PayPal');
            });
        }
    });
}

function unsyncFromPayPal(planId) {
    DevAlert.confirm(
        '¿Desincronizar de PayPal?',
        'Se eliminará la conexión con PayPal pero el plan seguirá existiendo en ambos sistemas. No se podrán crear nuevas suscripciones hasta volver a sincronizar.',
        'Sí, desincronizar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Desincronizando...', 'Removiendo conexión con PayPal');
            
            fetch(`{{ url('/developer/plans') }}/${planId}/unsync-paypal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                if (data.success) {
                    DevAlert.success('¡Desincronizado!', data.message).then(() => {
                        location.reload();
                    });
                } else {
                    DevAlert.error('Error', data.message);
                }
            })
            .catch(error => {
                DevAlert.close();
                DevAlert.error('Error', 'Error al desincronizar de PayPal');
            });
        }
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('[onclick*="toggleDropdown"]')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(d => {
            d.classList.add('hidden');
        });
    }
});
</script>
@endpush