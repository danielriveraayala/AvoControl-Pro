@php
    $paymentSummary = $customer->getPaymentSummaryAttribute();
    $creditStatus = $customer->getCreditStatusAttribute();
    $creditUtilization = $customer->getCreditUtilizationAttribute();
    $availableCredit = $customer->getAvailableCreditAttribute();
@endphp

<!-- Cliente Header -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-user-circle text-primary"></i> 
                    {{ $customer->name }}
                    @if($customer->status === 'active')
                        <span class="badge badge-success ml-2">Activo</span>
                    @else
                        <span class="badge badge-danger ml-2">Inactivo</span>
                    @endif
                </h4>
                <p class="text-muted mb-0">
                    @switch($customer->customer_type)
                        @case('mayorista')
                            <i class="fas fa-store text-primary"></i> Cliente Mayorista
                            @break
                        @case('minorista')
                            <i class="fas fa-shopping-basket text-info"></i> Cliente Minorista
                            @break
                        @case('distribuidor')
                            <i class="fas fa-truck text-warning"></i> Distribuidor
                            @break
                        @default
                            <i class="fas fa-user"></i> {{ ucfirst($customer->customer_type) }}
                    @endswitch
                </p>
            </div>
            <div class="text-right">
                <small class="text-muted">Cliente desde</small>
                <div class="font-weight-bold">{{ $customer->created_at->format('d/m/Y') }}</div>
                <small class="text-muted">({{ $customer->created_at->diffForHumans() }})</small>
            </div>
        </div>
    </div>
</div>

<!-- Estado de Crédito Alertas -->
@if($creditStatus === 'exceeded')
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>¡Límite de Crédito Excedido!</strong>
        Este cliente ha superado su límite de crédito en ${{ number_format(abs($availableCredit), 2) }}.
        Se requiere acción inmediata.
    </div>
@elseif($creditStatus === 'critical')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Límite de Crédito Crítico</strong>
        Este cliente está usando {{ number_format($creditUtilization, 1) }}% de su crédito disponible.
    </div>
@elseif($creditStatus === 'warning')
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Advertencia de Crédito</strong>
        Este cliente está usando {{ number_format($creditUtilization, 1) }}% de su crédito disponible.
    </div>
@endif

<!-- Métricas Principales -->
<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon 
                @if($creditStatus === 'exceeded') bg-danger 
                @elseif($creditStatus === 'critical') bg-warning 
                @elseif($creditStatus === 'warning') bg-info 
                @else bg-success @endif">
                <i class="fas fa-credit-card"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Crédito Disponible</span>
                <span class="info-box-number">
                    @if($availableCredit >= 0)
                        <span class="text-success">${{ number_format($availableCredit, 2) }}</span>
                    @else
                        <span class="text-danger">-${{ number_format(abs($availableCredit), 2) }}</span>
                    @endif
                </span>
                <div class="progress">
                    <div class="progress-bar 
                        @if($creditStatus === 'exceeded') bg-danger 
                        @elseif($creditStatus === 'critical') bg-warning 
                        @elseif($creditStatus === 'warning') bg-info 
                        @else bg-success @endif" 
                        style="width: {{ min($creditUtilization, 100) }}%"></div>
                </div>
                <span class="progress-description">
                    {{ number_format($creditUtilization, 1) }}% utilizado
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info">
                <i class="fas fa-dollar-sign"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Saldo Pendiente</span>
                <span class="info-box-number">
                    @if($paymentSummary['balance_due'] > 0)
                        <span class="text-warning">${{ number_format($paymentSummary['balance_due'], 2) }}</span>
                    @else
                        <span class="text-success">$0.00</span>
                    @endif
                </span>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: {{ $paymentSummary['payment_percentage'] }}%"></div>
                </div>
                <span class="progress-description">
                    {{ number_format($paymentSummary['payment_percentage'], 1) }}% pagado
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success">
                <i class="fas fa-chart-line"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Ventas</span>
                <span class="info-box-number">${{ number_format($paymentSummary['total_sales'], 2) }}</span>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: 85%"></div>
                </div>
                <span class="progress-description">{{ $customer->sales->count() }} transacciones</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-primary">
                <i class="fas fa-handshake"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Pagado</span>
                <span class="info-box-number">${{ number_format($paymentSummary['total_paid'], 2) }}</span>
                <div class="progress">
                    <div class="progress-bar bg-primary" style="width: {{ $paymentSummary['payment_percentage'] }}%"></div>
                </div>
                <span class="progress-description">Recibido</span>
            </div>
        </div>
    </div>
</div>

<!-- Detalles de Contacto y Información -->
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
                    <dd class="col-sm-7">{{ $customer->contact_person ?: 'No especificado' }}</dd>
                    
                    <dt class="col-sm-5">Teléfono:</dt>
                    <dd class="col-sm-7">
                        @if($customer->phone)
                            <a href="tel:{{ $customer->phone }}" class="text-primary">
                                <i class="fas fa-phone"></i> {{ $customer->phone }}
                            </a>
                        @else
                            <span class="text-muted">No especificado</span>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-5">Email:</dt>
                    <dd class="col-sm-7">
                        @if($customer->email)
                            <a href="mailto:{{ $customer->email }}" class="text-primary">
                                <i class="fas fa-envelope"></i> {{ $customer->email }}
                            </a>
                        @else
                            <span class="text-muted">No especificado</span>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-5">Dirección:</dt>
                    <dd class="col-sm-7">{{ $customer->address ?: 'No especificada' }}</dd>
                    
                    <dt class="col-sm-5">Ciudad:</dt>
                    <dd class="col-sm-7">{{ $customer->city ?: 'No especificada' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i> Análisis Financiero
                </h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-6">Límite de Crédito:</dt>
                    <dd class="col-sm-6">
                        <strong class="text-primary">${{ number_format($customer->credit_limit, 2) }}</strong>
                    </dd>
                    
                    <dt class="col-sm-6">Utilización de Crédito:</dt>
                    <dd class="col-sm-6">
                        <strong class="text-{{ $creditStatus === 'exceeded' ? 'danger' : ($creditStatus === 'critical' ? 'warning' : 'success') }}">
                            {{ number_format($creditUtilization, 1) }}%
                        </strong>
                    </dd>
                    
                    <dt class="col-sm-6">Promedio por Venta:</dt>
                    <dd class="col-sm-6">
                        @php
                            $avgSale = $customer->sales->count() > 0 ? $paymentSummary['total_sales'] / $customer->sales->count() : 0;
                        @endphp
                        <strong>${{ number_format($avgSale, 2) }}</strong>
                    </dd>
                    
                    <dt class="col-sm-6">Estado de Pagos:</dt>
                    <dd class="col-sm-6">
                        @if($paymentSummary['balance_due'] <= 0)
                            <span class="badge badge-success">Al Corriente</span>
                        @elseif($paymentSummary['payment_percentage'] >= 50)
                            <span class="badge badge-warning">Parcialmente Pagado</span>
                        @else
                            <span class="badge badge-danger">Pendiente</span>
                        @endif
                    </dd>
                </dl>
                
                <!-- Barra de progreso de crédito -->
                <div class="mt-3">
                    <label class="text-sm">Uso de Crédito:</label>
                    <div class="progress progress-sm">
                        <div class="progress-bar 
                            @if($creditStatus === 'exceeded') bg-danger 
                            @elseif($creditStatus === 'critical') bg-warning 
                            @elseif($creditStatus === 'warning') bg-info 
                            @else bg-success @endif" 
                            style="width: {{ min($creditUtilization, 100) }}%"></div>
                    </div>
                    <small class="text-muted">
                        ${{ number_format($customer->current_balance, 2) }} de ${{ number_format($customer->credit_limit, 2) }} usado
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@if($customer->notes)
<div class="row mt-3">
    <div class="col-12">
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-sticky-note"></i> Notas Adicionales
                </h3>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $customer->notes }}</p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Últimas Ventas -->
@if($customer->sales->count() > 0)
<div class="row mt-3">
    <div class="col-12">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart"></i> Últimas Ventas
                </h3>
                <div class="card-tools">
                    <span class="badge badge-success">{{ $customer->sales->count() }} ventas totales</span>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th>Estado Venta</th>
                            <th>Estado Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->salesWithPayments()->take(5) as $sale)
                        <tr>
                            <td><strong>#{{ $sale->sale_code ?? $sale->id }}</strong></td>
                            <td>
                                {{ $sale->created_at->format('d/m/Y') }}
                                <br><small class="text-muted">{{ $sale->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <strong>${{ number_format($sale->total_amount ?? 0, 2) }}</strong>
                            </td>
                            <td>
                                <span class="text-success">${{ number_format($sale->total_paid ?? 0, 2) }}</span>
                            </td>
                            <td>
                                @if($sale->balance_due > 0)
                                    <span class="text-warning font-weight-bold">${{ number_format($sale->balance_due, 2) }}</span>
                                @else
                                    <span class="text-success">$0.00</span>
                                @endif
                            </td>
                            <td>
                                @switch($sale->status ?? 'draft')
                                    @case('delivered')
                                        <span class="badge badge-success">Entregada</span>
                                        @break
                                    @case('shipped')
                                        <span class="badge badge-warning">Enviada</span>
                                        @break
                                    @case('confirmed')
                                        <span class="badge badge-info">Confirmada</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge badge-danger">Cancelada</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">Borrador</span>
                                @endswitch
                            </td>
                            <td>
                                @switch($sale->payment_status)
                                    @case('paid')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Pagado
                                        </span>
                                        @break
                                    @case('partial')
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Parcial
                                        </span>
                                        @break
                                    @default
                                        <span class="badge badge-danger">
                                            <i class="fas fa-exclamation"></i> Pendiente
                                        </span>
                                @endswitch
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                @if($customer->sales->count() > 5)
                    <div class="card-footer text-center">
                        <small class="text-muted">
                            Mostrando las últimas 5 ventas de {{ $customer->sales->count() }} totales
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

