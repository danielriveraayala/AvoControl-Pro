@extends('layouts.developer')

@section('title', 'Ver Plan: ' . $plan->name)

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                            <i class="fas fa-eye text-blue-600 mr-2"></i>
                            {{ $plan->name }}
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Detalles del plan de suscripción</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <a href="{{ route('developer.plans.edit', $plan) }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 self-start">
                            <i class="fas fa-edit mr-1"></i>Editar
                        </a>
                        <a href="{{ route('developer.plans.index') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 self-start">
                            ← <span class="ml-1">Volver</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plan Overview Card -->
        <div class="bg-white shadow rounded-lg mb-6" style="border-left: 4px solid {{ $plan->color ?? '#3B82F6' }};">
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Plan Icon & Name -->
                    <div class="text-center lg:text-left">
                        @if($plan->icon)
                            <div class="w-16 h-16 mx-auto lg:mx-0 mb-4 rounded-full flex items-center justify-center" style="background-color: {{ $plan->color ?? '#3B82F6' }}20;">
                                <i class="{{ $plan->icon }} text-2xl" style="color: {{ $plan->color ?? '#3B82F6' }};"></i>
                            </div>
                        @endif
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h2>
                        <p class="text-gray-600">{{ $plan->description ?? 'Sin descripción' }}</p>
                    </div>

                    <!-- Pricing -->
                    <div class="text-center">
                        <div class="text-4xl font-bold text-gray-900 mb-2">
                            @if($plan->price == 0)
                                <span class="text-green-600">Gratis</span>
                            @else
                                <span class="text-sm text-gray-500">$</span>{{ number_format($plan->price, 0) }}
                                <span class="text-lg text-gray-500">{{ $plan->currency }}</span>
                            @endif
                        </div>
                        <p class="text-gray-600">
                            por {{ $plan->billing_cycle === 'yearly' ? 'año' : 'mes' }}
                        </p>
                        @if($plan->trial_days > 0)
                            <div class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $plan->trial_days }} días gratis
                            </div>
                        @endif
                    </div>

                    <!-- Status & Actions -->
                    <div class="text-center lg:text-right">
                        <div class="flex flex-col space-y-3">
                            <!-- Status Badges -->
                            <div class="flex flex-wrap justify-center lg:justify-end gap-2">
                                @if($plan->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i>Inactivo
                                    </span>
                                @endif

                                @if($plan->is_featured)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-star mr-1"></i>Destacado
                                    </span>
                                @endif

                                @if($plan->is_custom)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-palette mr-1"></i>Personalizado
                                    </span>
                                @endif
                            </div>

                            <!-- PayPal Status -->
                            @if($plan->paypal_plan_id)
                                <div class="text-sm text-blue-600">
                                    <i class="fab fa-paypal mr-1"></i>Sincronizado con PayPal
                                </div>
                            @else
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-unlink mr-1"></i>No sincronizado
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Plan Details -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>Detalles del Plan
                    </h3>
                </div>
                <div class="px-6 py-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Clave del Plan</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono bg-gray-50 px-2 py-1 rounded">{{ $plan->key }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Moneda</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $plan->currency }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Ciclo de Facturación</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($plan->billing_cycle) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Orden de Aparición</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $plan->sort_order ?? 'No definido' }}</dd>
                        </div>
                        @if($plan->paypal_plan_id)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">ID PayPal</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono bg-blue-50 px-2 py-1 rounded">{{ $plan->paypal_plan_id }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Creado</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $plan->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Actualizado</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $plan->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Limits & Configuration -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-sliders-h text-purple-600 mr-2"></i>Límites y Configuración
                    </h3>
                </div>
                <div class="px-6 py-6">
                    <dl class="grid grid-cols-1 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Días de Prueba</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($plan->trial_days > 0)
                                    {{ $plan->trial_days }} días
                                @else
                                    Sin prueba gratuita
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Máximo Usuarios</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $plan->getLimitDisplay('max_users') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Máximo Lotes/Mes</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $plan->getLimitDisplay('max_lots_per_month') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Almacenamiento</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($plan->max_storage_gb === -1)
                                    Ilimitado
                                @elseif($plan->max_storage_gb)
                                    {{ $plan->max_storage_gb }} GB
                                @else
                                    No disponible
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Máximo Ubicaciones</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $plan->getLimitDisplay('max_locations') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Color del Plan</dt>
                            <dd class="mt-1 flex items-center">
                                <div class="w-4 h-4 rounded mr-2" style="background-color: {{ $plan->color ?? '#3B82F6' }};"></div>
                                <span class="text-sm text-gray-900 font-mono">{{ $plan->color ?? '#3B82F6' }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        @if($plan->features && count($plan->features) > 0)
            <div class="bg-white shadow rounded-lg mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-puzzle-piece text-indigo-600 mr-2"></i>Funcionalidades Incluidas
                    </h3>
                </div>
                <div class="px-6 py-6">
                    @php
                        $availableFeatures = \App\Models\SubscriptionPlan::getAvailableFeatures();
                        $planFeatures = $plan->features ?? [];
                        $featuresGrouped = [];
                        
                        foreach ($availableFeatures as $category => $categoryFeatures) {
                            foreach ($categoryFeatures as $key => $label) {
                                if (in_array($key, $planFeatures)) {
                                    $featuresGrouped[$category][] = $label;
                                }
                            }
                        }
                    @endphp

                    @if(count($featuresGrouped) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($featuresGrouped as $category => $features)
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-800 uppercase tracking-wide mb-3">{{ ucfirst($category) }}</h4>
                                    <ul class="space-y-2">
                                        @foreach($features as $feature)
                                            <li class="flex items-center text-sm text-gray-600">
                                                <i class="fas fa-check text-green-500 mr-2"></i>
                                                {{ $feature }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No hay funcionalidades específicas configuradas para este plan.</p>
                    @endif
                </div>
            </div>
        @endif

        <!-- Subscriptions Using This Plan -->
        <div class="bg-white shadow rounded-lg mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-users text-green-600 mr-2"></i>Suscripciones Activas
                </h3>
            </div>
            <div class="px-6 py-6">
                @php
                    $subscriptionsCount = $plan->subscriptions()->count();
                    $activeSubscriptionsCount = $plan->subscriptions()->where('status', 'active')->count();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $subscriptionsCount }}</div>
                        <div class="text-sm text-blue-800">Total Suscripciones</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $activeSubscriptionsCount }}</div>
                        <div class="text-sm text-green-800">Suscripciones Activas</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-600">{{ $subscriptionsCount - $activeSubscriptionsCount }}</div>
                        <div class="text-sm text-gray-800">Otras</div>
                    </div>
                </div>

                @if($subscriptionsCount > 0)
                    <div class="mt-4 text-center">
                        <a href="{{ route('developer.subscriptions.index') }}?plan={{ $plan->key }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-external-link-alt mr-2"></i>Ver todas las suscripciones de este plan
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white shadow rounded-lg mt-6">
            <div class="px-6 py-4">
                <div class="flex flex-col sm:flex-row sm:justify-between space-y-3 sm:space-y-0 sm:space-x-3">
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                        @if(!$plan->paypal_plan_id)
                            <button type="button" onclick="syncWithPayPal()" class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fab fa-paypal mr-2"></i>Sincronizar con PayPal
                            </button>
                        @else
                            <button type="button" onclick="unsyncFromPayPal()" class="inline-flex justify-center items-center px-4 py-2 border border-yellow-300 shadow-sm text-sm font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100">
                                <i class="fas fa-unlink mr-2"></i>Desincronizar de PayPal
                            </button>
                        @endif
                        
                        <button type="button" onclick="duplicatePlan()" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-copy mr-2"></i>Duplicar Plan
                        </button>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.plans.edit', $plan) }}" class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <i class="fas fa-edit mr-2"></i>Editar Plan
                        </a>
                        
                        @if($plan->canBeDeleted())
                            <button type="button" onclick="deletePlan()" class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                <i class="fas fa-trash mr-2"></i>Eliminar Plan
                            </button>
                        @else
                            <button type="button" disabled class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                                <i class="fas fa-lock mr-2"></i>No se puede eliminar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function syncWithPayPal() {
    DevAlert.confirm(
        '¿Sincronizar con PayPal?',
        'Esto creará este plan en PayPal y permitirá procesar suscripciones.',
        'Sí, sincronizar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Sincronizando...', 'Creando plan en PayPal');
            
            fetch(`{{ route('developer.plans.sync-paypal', $plan) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
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
                handleFetchError(error);
            });
        }
    });
}

function unsyncFromPayPal() {
    DevAlert.confirmDanger(
        '¿Desincronizar de PayPal?',
        'Esto eliminará el plan de PayPal. Las suscripciones activas podrían verse afectadas.',
        'Sí, desincronizar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Desincronizndo...', 'Eliminando plan de PayPal');
            
            fetch(`{{ route('developer.plans.unsync-paypal', $plan) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
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
                handleFetchError(error);
            });
        }
    });
}

function duplicatePlan() {
    DevAlert.confirm(
        '¿Duplicar plan?',
        'Se creará una copia de este plan que podrás modificar.',
        'Sí, duplicar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Duplicando...', 'Creando copia del plan');
            
            fetch(`{{ route('developer.plans.duplicate', $plan) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
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
                handleFetchError(error);
            });
        }
    });
}

function deletePlan() {
    DevAlert.confirmDanger(
        '¿Eliminar plan?',
        'Esta acción no se puede deshacer. El plan será eliminado permanentemente.',
        'Sí, eliminar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Eliminando...', 'Eliminando plan del sistema');
            
            fetch(`{{ route('developer.plans.destroy', $plan) }}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                if (data.success) {
                    DevAlert.success('¡Plan eliminado!', data.message).then(() => {
                        window.location.href = '{{ route('developer.plans.index') }}';
                    });
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
</script>
@endpush