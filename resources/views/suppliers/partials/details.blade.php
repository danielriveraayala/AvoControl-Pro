@php
    $totalLots = $supplier->lots->count();
    $totalPurchases = $supplier->getTotalPurchasesAttribute();
    $totalPaid = $supplier->getTotalPaidAttribute();
    $actualBalance = $totalPurchases - $totalPaid;
    $totalWeight = $supplier->lots->sum('total_weight');
    $avgPricePerKg = $supplier->lots->avg('purchase_price_per_kg');
    
    // Group lots by quality for the progress bar
    $lotsByQuality = $supplier->lots->groupBy(function($lot) {
        return $lot->qualityGrade ? $lot->qualityGrade->name : 'Sin Calidad';
    })->map(function($qualityLots) {
        $totalWeight = $qualityLots->sum('total_weight');
        $quality = $qualityLots->first()->qualityGrade;
        return [
            'name' => $quality ? $quality->name : 'Sin Calidad',
            'color' => $quality ? $quality->color : '#6c757d',
            'weight' => $totalWeight,
            'lots_count' => $qualityLots->count(),
            'percentage' => $totalWeight > 0 ? ($totalWeight / max($totalWeight, 1)) * 100 : 0
        ];
    });
    
    // Recalculate percentages based on total weight
    $lotsByQuality = $lotsByQuality->map(function($quality) use ($totalWeight) {
        $quality['percentage'] = $totalWeight > 0 ? ($quality['weight'] / $totalWeight) * 100 : 0;
        return $quality;
    });
@endphp

<!-- Supplier Header -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-truck text-primary"></i> 
                    {{ $supplier->name }}
                    @if($supplier->status === 'active')
                        <span class="badge badge-success ml-2">Activo</span>
                    @else
                        <span class="badge badge-danger ml-2">Inactivo</span>
                    @endif
                </h4>
                <p class="text-muted mb-0">
                    <i class="fas fa-leaf text-success"></i> Proveedor de Aguacate
                </p>
            </div>
            <div class="text-right">
                <small class="text-muted">Proveedor desde</small>
                <div class="font-weight-bold">{{ $supplier->created_at->format('d/m/Y') }}</div>
                <small class="text-muted">({{ $supplier->created_at->diffForHumans() }})</small>
            </div>
        </div>
    </div>
</div>

<!-- Balance Alert -->
@if($actualBalance > 0)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Saldo Pendiente</strong>
        Este proveedor tiene un saldo pendiente de ${{ number_format($actualBalance, 2) }}.
        @if($actualBalance != $supplier->balance_owed)
            <br><small class="text-muted">
                <i class="fas fa-info-circle"></i> 
                Saldo registrado en BD: ${{ number_format($supplier->balance_owed, 2) }} - Calculando desde pagos reales...
            </small>
        @endif
    </div>
@endif

<!-- Main Metrics -->
<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-primary">
                <i class="fas fa-boxes"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total de Lotes</span>
                <span class="info-box-number">{{ $totalLots }}</span>
                <div class="progress">
                    <div class="progress-bar bg-primary" style="width: 85%"></div>
                </div>
                <span class="progress-description">Lotes recibidos</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success">
                <i class="fas fa-dollar-sign"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Compras</span>
                <span class="info-box-number">${{ number_format($totalPurchases, 2) }}</span>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: 90%"></div>
                </div>
                <span class="progress-description">En compras realizadas</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning">
                <i class="fas fa-weight"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Kilos</span>
                <span class="info-box-number">{{ number_format($totalWeight, 0) }}</span>
                <div class="progress">
                    <div class="progress-bar bg-warning" style="width: 85%"></div>
                </div>
                <span class="progress-description">Kg acopiados</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info">
                <i class="fas fa-chart-line"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Precio Promedio</span>
                <span class="info-box-number">${{ number_format($avgPricePerKg, 2) }}</span>
                <div class="progress">
                    <div class="progress-bar bg-info" style="width: 70%"></div>
                </div>
                <span class="progress-description">Por kilogramo</span>
            </div>
        </div>
    </div>
</div>

<!-- Contact Details and Information -->
<div class="row mt-3">
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-address-card"></i> Información de Contacto
                </h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-5">Persona de Contacto:</dt>
                    <dd class="col-sm-7">{{ $supplier->contact_person ?: 'No especificado' }}</dd>
                    
                    <dt class="col-sm-5">Teléfono:</dt>
                    <dd class="col-sm-7">
                        @if($supplier->phone)
                            <a href="tel:{{ $supplier->phone }}" class="text-primary">
                                <i class="fas fa-phone"></i> {{ $supplier->phone }}
                            </a>
                        @else
                            <span class="text-muted">No especificado</span>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-5">Email:</dt>
                    <dd class="col-sm-7">
                        @if($supplier->email)
                            <a href="mailto:{{ $supplier->email }}" class="text-primary">
                                <i class="fas fa-envelope"></i> {{ $supplier->email }}
                            </a>
                        @else
                            <span class="text-muted">No especificado</span>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-5">Dirección:</dt>
                    <dd class="col-sm-7">{{ $supplier->address ?: 'No especificada' }}</dd>
                    
                    <dt class="col-sm-5">Ciudad:</dt>
                    <dd class="col-sm-7">{{ $supplier->city ?: 'No especificada' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i> Análisis de Rendimiento
                </h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-6">Saldo Pendiente:</dt>
                    <dd class="col-sm-6">
                        @if($actualBalance > 0)
                            <strong class="text-warning">${{ number_format($actualBalance, 2) }}</strong>
                        @else
                            <strong class="text-success">$0.00</strong>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-6">Promedio por Lote:</dt>
                    <dd class="col-sm-6">
                        @php
                            $avgLotValue = $totalLots > 0 ? $totalPurchases / $totalLots : 0;
                        @endphp
                        <strong>${{ number_format($avgLotValue, 2) }}</strong>
                    </dd>
                    
                    <dt class="col-sm-6">Precio por Kg:</dt>
                    <dd class="col-sm-6">
                        <strong>${{ number_format($avgPricePerKg, 2) }}</strong>
                    </dd>
                    
                    <dt class="col-sm-6">Estado de Pagos:</dt>
                    <dd class="col-sm-6">
                        @if($actualBalance <= 0)
                            <span class="badge badge-success">Al Corriente</span>
                        @else
                            <span class="badge badge-warning">Saldo Pendiente</span>
                        @endif
                    </dd>
                </dl>
                
                <!-- Progress bar for acopio volume by quality -->
                <div class="mt-3">
                    <label class="text-sm">Volumen de Acopio por Calidad:</label>
                    <div class="progress progress-sm">
                        @foreach($lotsByQuality as $quality)
                            <div class="progress-bar" 
                                 style="width: {{ $quality['percentage'] }}%; background-color: {{ $quality['color'] }};" 
                                 title="{{ $quality['name'] }}: {{ number_format($quality['weight'], 1) }} kg ({{ number_format($quality['percentage'], 1) }}%)">
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted d-block mt-1">
                        {{ number_format($totalWeight, 1) }} kg acopiados en {{ $totalLots }} lotes
                    </small>
                    
                    <!-- Quality breakdown -->
                    <div class="mt-2">
                        @foreach($lotsByQuality as $quality)
                            <span class="badge mr-1" style="background-color: {{ $quality['color'] }}; color: white;">
                                {{ $quality['name'] }}: {{ number_format($quality['weight'], 0) }} kg 
                                ({{ $quality['lots_count'] }} {{ $quality['lots_count'] == 1 ? 'lote' : 'lotes' }})
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($supplier->notes)
<div class="row mt-3">
    <div class="col-12">
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-sticky-note"></i> Notas Adicionales
                </h3>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $supplier->notes }}</p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Latest Lots -->
@if($supplier->lots->count() > 0)
<div class="row mt-3">
    <div class="col-12">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes"></i> Últimos Lotes Recibidos
                </h3>
                <div class="card-tools">
                    <span class="badge badge-success">{{ $supplier->lots->count() }} lotes totales</span>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Fecha Cosecha</th>
                            <th>Peso (kg)</th>
                            <th>Calidad</th>
                            <th>Precio/kg</th>
                            <th>Total</th>
                            <th>Entrada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supplier->lots()->latest('harvest_date')->take(5)->get() as $lot)
                        <tr>
                            <td><strong>#{{ $lot->lot_code ?? $lot->id }}</strong></td>
                            <td>
                                {{ $lot->harvest_date ? $lot->harvest_date->format('d/m/Y') : 'N/A' }}
                                @if($lot->harvest_date)
                                    <br><small class="text-muted">{{ $lot->harvest_date->diffForHumans() }}</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ number_format($lot->total_weight ?? 0, 1) }} kg</strong>
                            </td>
                            <td>
                                @if($lot->qualityGrade)
                                    <span class="badge" style="background-color: {{ $lot->qualityGrade->color }}; color: white;">
                                        {{ $lot->qualityGrade->name }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-info">${{ number_format($lot->purchase_price_per_kg ?? 0, 2) }}</span>
                            </td>
                            <td>
                                <strong>${{ number_format(($lot->total_weight ?? 0) * ($lot->purchase_price_per_kg ?? 0), 2) }}</strong>
                            </td>
                            <td>
                                @if($lot->entry_date)
                                    <span class="badge badge-success">{{ $lot->entry_date->format('d/m/Y') }}</span>
                                @else
                                    <span class="badge badge-secondary">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                @if($supplier->lots->count() > 5)
                    <div class="card-footer text-center">
                        <small class="text-muted">
                            Mostrando los últimos 5 lotes de {{ $supplier->lots->count() }} totales
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif