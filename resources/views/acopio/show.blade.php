@extends('layouts.admin')

@section('title', "Acopio - {$quality}")
@section('page-title', "Acopio - {$quality}")

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('acopio.index') }}">Acopio</a></li>
    <li class="breadcrumb-item active">{{ $quality }}</li>
@endsection

@section('content')

<div class="content">
    <div class="container-fluid">

        <!-- Estadísticas de esta calidad -->
        <div class="row mb-4">
            <div class="col-lg-2 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $stats->total_lotes }}</h3>
                        <p>Lotes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($stats->peso_total, 1) }}<sup>kg</sup></h3>
                        <p>Peso Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-weight"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($stats->peso_disponible, 1) }}<sup>kg</sup></h3>
                        <p>Disponible</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>${{ number_format($stats->precio_promedio, 2) }}</h3>
                        <p>Precio Promedio/kg</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>${{ number_format($stats->costo_total, 0) }}</h3>
                        <p>Valor Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Acciones Rápidas</h5>
                                <small class="text-muted">Operaciones para esta calidad</small>
                            </div>
                            <div class="btn-group">
                                <a type="button" class="btn btn-link" href="{{ route('acopio.index') }}">
                                    <i class="fas fa-return"></i> Regresar
                                </a>
                                <button type="button" class="btn btn-success" onclick="crearVenta()">
                                    <i class="fas fa-shopping-cart"></i> Crear Venta
                                </button>
                                <button type="button" class="btn btn-info" onclick="verMovimientos()">
                                    <i class="fas fa-history"></i> Ver Movimientos
                                </button>
                                <button type="button" class="btn btn-primary" onclick="exportarReporte()">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progreso de Ventas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Estado del Inventario</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $disponiblePct = $stats->peso_total > 0 ? ($stats->peso_disponible / $stats->peso_total) * 100 : 0;
                            $vendidoPct = $stats->peso_total > 0 ? ($stats->peso_vendido / $stats->peso_total) * 100 : 0;
                        @endphp
                        <div class="progress progress-lg mb-3">
                            <div class="progress-bar bg-success"
                                 style="width: {{ $disponiblePct }}%"
                                 title="Disponible: {{ number_format($stats->peso_disponible, 2) }} kg">
                                {{ number_format($disponiblePct, 1) }}% Disponible
                            </div>
                            <div class="progress-bar bg-info"
                                 style="width: {{ $vendidoPct }}%"
                                 title="Vendido: {{ number_format($stats->peso_vendido, 2) }} kg">
                                {{ number_format($vendidoPct, 1) }}% Vendido
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-md-6">
                                <div class="text-success">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>{{ number_format($stats->peso_disponible, 2) }} kg</strong> Disponible
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-info">
                                    <i class="fas fa-shopping-cart"></i>
                                    <strong>{{ number_format($stats->peso_vendido, 2) }} kg</strong> Vendido
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Lotes -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            Lotes de Calidad {{ $quality }}
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Proveedor</th>
                                        <th>Fecha Ingreso</th>
                                        <th>Peso</th>
                                        <th>Disponible</th>
                                        <th>Precio/kg</th>
                                        <th>Estado</th>
                                        <th>Asignaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lotes as $lote)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $lote->lot_code }}</strong>
                                            <br><small class="text-muted">{{ $lote->created_at->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            @if($lote->supplier)
                                                <strong>{{ $lote->supplier->name }}</strong>
                                                @if($lote->supplier->city)
                                                    <br><small class="text-muted">{{ $lote->supplier->city }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">Anónimo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $lote->entry_date->format('d/m/Y') }}</span>
                                            <br><small class="text-muted">{{ $lote->entry_date->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ number_format($lote->total_weight, 2) }} kg</strong>
                                        </td>
                                        <td>
                                            <strong class="text-success">{{ number_format($lote->weight_available, 2) }} kg</strong>
                                            @if($lote->weight_sold > 0)
                                                <br><small class="text-muted">Vendido: {{ number_format($lote->weight_sold, 2) }} kg</small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>${{ number_format($lote->purchase_price_per_kg, 2) }}</strong>
                                        </td>
                                        <td>
                                            @switch($lote->status)
                                                @case('active')
                                                    <span class="badge badge-success">Activo</span>
                                                    @break
                                                @case('partial')
                                                    <span class="badge badge-warning">Parcial</span>
                                                    @break
                                                @case('sold')
                                                    <span class="badge badge-info">Vendido</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ $lote->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($lote->saleAllocations->count() > 0)
                                                <span class="badge badge-primary">
                                                    {{ $lote->saleAllocations->count() }} asignaciones
                                                </span>
                                                <br><small class="text-muted">
                                                    {{ number_format($lote->saleAllocations->sum('allocated_weight'), 2) }} kg asignados
                                                </small>
                                            @else
                                                <span class="text-muted">Sin asignaciones</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-info"
                                                        onclick="verLote({{ $lote->id }})"
                                                        title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-primary"
                                                        onclick="editarLote({{ $lote->id }})"
                                                        title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <h4>No hay lotes de esta calidad</h4>
                                                <p>No se encontraron lotes de calidad {{ $quality }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($lotes->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-center">
                            {{ $lotes->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function crearVenta() {
    window.location.href = `{{ route('sales.index') }}`;
}

function verMovimientos() {
    window.location.href = `{{ route('acopio.movimientos') }}?quality_grade={{ $quality }}`;
}

function exportarReporte() {
    window.location.href = `{{ route('acopio.reporte') }}?calidad={{ $quality }}`;
}

function verLote(lotId) {
    // Abrir modal de lote desde la funcionalidad existente
    if (typeof openViewLotModal === 'function') {
        openViewLotModal(lotId);
    } else {
        window.location.href = `/lots/${lotId}`;
    }
}

function editarLote(lotId) {
    // Abrir modal de edición desde la funcionalidad existente
    if (typeof openEditLotModal === 'function') {
        openEditLotModal(lotId);
    } else {
        window.location.href = `/lots/${lotId}/edit`;
    }
}
</script>

<style>
.badge-lg {
    padding: 0.5rem 0.75rem;
    font-size: 1.1rem;
}

.progress-lg {
    height: 2rem;
}

.progress-lg .progress-bar {
    font-size: 0.9rem;
    font-weight: bold;
}
</style>
@endsection
