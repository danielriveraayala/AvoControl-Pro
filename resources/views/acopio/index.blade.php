@extends('layouts.admin')

@section('title', 'Inventario por Calidad')
@section('page-title', 'Inventario por Calidad')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Acopio</li>
@endsection

@section('content')

<div class="content">
    <div class="container-fluid">

        <!-- Alertas de Déficit de Inventario -->
        @if(isset($alertas) && count($alertas) > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> ¡ALERTA! Déficit de Inventario Detectado</h5>
                    <p class="mb-2">Se han detectado ventas comprometidas sin inventario suficiente:</p>
                    <ul class="mb-0">
                        @foreach($alertas as $alerta)
                        <li>
                            <strong>{{ $alerta['calidad'] }}:</strong>
                            Déficit de <strong class="text-danger">{{ number_format($alerta['deficit'], 2) }} kg</strong>
                            (Disponible: {{ number_format($alerta['disponible'], 2) }} kg,
                            Comprometido: {{ number_format($alerta['comprometido'], 2) }} kg)
                        </li>
                        @endforeach
                    </ul>
                    <hr>
                    <p class="mb-0"><strong>Acción requerida:</strong> Registre nuevos lotes o cancele ventas para resolver el déficit.</p>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Estadísticas Generales -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_lotes'] }}</h3>
                        <p>Lotes Totales</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($stats['peso_total'], 1) }}<sup>kg</sup></h3>
                        <p>Peso Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-weight"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>${{ number_format($stats['valor_total'], 0) }}</h3>
                        <p>Valor Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($stats['peso_disponible'], 1) }}<sup>kg</sup></h3>
                        <p>Disponible</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acopio de Aguacate -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-layer-group"></i>
                            Acopio de Aguacate
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-info" onclick="refreshAcopio()">
                                <i class="fas fa-sync-alt"></i> Actualizar
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="showReporte()">
                                <i class="fas fa-chart-bar"></i> Reporte
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Calidad</th>
                                        <th>Lotes</th>
                                        <th>Peso Total</th>
                                        <th>Peso Disponible</th>
                                        <th>% Disponible</th>
                                        <th>Precio Promedio</th>
                                        <th>Valor Total</th>
                                        <th>Estado Pagos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($acopio as $item)
                                    <tr>
                                        <td>
                                            @php
                                                $qualityName = $item->qualityGrade ? $item->qualityGrade->name : 'Sin calidad';
                                                $badgeClass = match($qualityName) {
                                                    'Primeras' => 'success',
                                                    'Segunda' => 'warning',
                                                    'Tercera' => 'info',
                                                    'Cuarta' => 'primary',
                                                    'Industrial' => 'secondary',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }} badge-lg">
                                                <i class="fas fa-star"></i> {{ $qualityName }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $item->total_lotes }}</strong>
                                            <small class="text-muted d-block">lotes</small>
                                        </td>
                                        <td>
                                            <strong>{{ number_format($item->peso_total, 2) }} kg</strong>
                                        </td>
                                        <td>
                                            @if(isset($item->tiene_deficit) && $item->tiene_deficit)
                                                <strong class="text-danger">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    {{ number_format($item->peso_disponible, 2) }} kg
                                                </strong>
                                                <small class="text-danger d-block">
                                                    DÉFICIT: {{ number_format($item->peso_comprometido - $item->peso_disponible, 2) }} kg
                                                </small>
                                            @else
                                                <strong>{{ number_format($item->peso_disponible, 2) }} kg</strong>
                                                @if($item->peso_vendido > 0)
                                                    <small class="text-muted d-block">
                                                        Vendido: {{ number_format($item->peso_vendido, 2) }} kg
                                                    </small>
                                                @endif
                                                @if(isset($item->peso_comprometido) && $item->peso_comprometido > 0 && $item->peso_comprometido != $item->peso_vendido)
                                                    <small class="text-info d-block">
                                                        Comprometido: {{ number_format($item->peso_comprometido, 2) }} kg
                                                    </small>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $disponiblePct = $item->peso_total > 0 ? ($item->peso_disponible / $item->peso_total) * 100 : 0;
                                            @endphp
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-success" style="width: {{ $disponiblePct }}%"></div>
                                            </div>
                                            <small>{{ number_format($disponiblePct, 1) }}%</small>
                                        </td>
                                        <td>
                                            <strong>${{ number_format($item->precio_promedio, 2) }}</strong>
                                            <small class="text-muted d-block">por kg</small>
                                        </td>
                                        <td>
                                            <strong class="text-primary">
                                                ${{ number_format($item->costo_total, 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            @if($item->total_adeudado > 0)
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock"></i>
                                                    ${{ number_format($item->total_adeudado, 0) }}
                                                </span>
                                            @else
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle"></i>
                                                    Pagado
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $qualityName = $item->qualityGrade ? $item->qualityGrade->name : 'Sin calidad';
                                            @endphp
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('acopio.show', $qualityName) }}"
                                                   class="btn btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-success"
                                                        onclick="crearVenta('{{ $qualityName }}')">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <h4>No hay inventario disponible</h4>
                                                <p>Registre algunos lotes para ver el acopio aquí</p>
                                                <a href="{{ route('lots.create') }}" class="btn btn-success">
                                                    <i class="fas fa-plus"></i> Registrar Lote
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movimientos Recientes -->
        @if($movimientos->isNotEmpty())
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Movimientos Recientes (Últimos 30 días)
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('acopio.movimientos') }}" class="btn btn-sm btn-primary">
                                Ver Todos
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm table-striped">
                                <thead class="sticky-top bg-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Calidad</th>
                                        <th>Cliente</th>
                                        <th>Peso</th>
                                        <th>Lote Origen</th>
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($movimientos as $mov)
                                    <tr>
                                        <td>
                                            <small>{{ $mov->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-sm badge-secondary">
                                                {{ $mov->lot->qualityGrade ? $mov->lot->qualityGrade->name : 'Sin calidad' }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $mov->saleItem->sale->customer->name }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ number_format($mov->allocated_weight, 2) }} kg</strong>
                                        </td>
                                        <td>
                                            <code>{{ $mov->lot->lot_code }}</code>
                                        </td>
                                        <td>
                                            <span class="text-success">
                                                ${{ number_format($mov->saleItem->price_per_kg, 2) }}/kg
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

<script>
function refreshAcopio() {
    location.reload();
}

function crearVenta(calidad) {
    // Redirigir a crear venta con la calidad preseleccionada
    window.location.href = `{{ route('sales.create') }}?calidad=${calidad}`;
}

function showReporte() {
    // Abrir modal de reporte o redirigir
    window.location.href = `{{ route('acopio.reporte') }}`;
}
</script>

<style>
.badge-lg {
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}
</style>
@endsection
