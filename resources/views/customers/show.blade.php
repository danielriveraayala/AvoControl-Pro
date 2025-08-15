@extends('layouts.admin')

@section('title', 'Detalle Cliente')
@section('page-title', 'Detalle del Cliente')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Clientes</a></li>
    <li class="breadcrumb-item active">{{ $customer->name }}</li>
@endsection

@section('content')
    <!-- Información del Cliente -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i> Información del Cliente
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary btn-sm btn-ajax">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Nombre:</dt>
                                <dd class="col-sm-8">{{ $customer->name }}</dd>

                                <dt class="col-sm-4">Tipo:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge 
                                        @switch($customer->customer_type)
                                            @case('mayorista') badge-primary @break
                                            @case('distribuidor') badge-info @break
                                            @default badge-secondary
                                        @endswitch">
                                        {{ ucfirst($customer->customer_type) }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Estado:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge {{ $customer->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                        {{ $customer->status === 'active' ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Contacto:</dt>
                                <dd class="col-sm-8">{{ $customer->contact_person ?? 'No especificado' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Email:</dt>
                                <dd class="col-sm-8">
                                    @if($customer->email)
                                        <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                                    @else
                                        No especificado
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Teléfono:</dt>
                                <dd class="col-sm-8">
                                    @if($customer->phone)
                                        <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                                    @else
                                        No especificado
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Ciudad:</dt>
                                <dd class="col-sm-8">{{ $customer->city ?? 'No especificado' }}</dd>

                                <dt class="col-sm-4">País:</dt>
                                <dd class="col-sm-8">{{ $customer->country ?? 'No especificado' }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($customer->address)
                    <div class="row">
                        <div class="col-12">
                            <dl class="row">
                                <dt class="col-sm-2">Dirección:</dt>
                                <dd class="col-sm-10">{{ $customer->address }}</dd>
                            </dl>
                        </div>
                    </div>
                    @endif

                    @if($customer->notes)
                    <div class="row">
                        <div class="col-12">
                            <dl class="row">
                                <dt class="col-sm-2">Notas:</dt>
                                <dd class="col-sm-10">{{ $customer->notes }}</dd>
                            </dl>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Estadísticas del Cliente -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-info"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Límite de Crédito</span>
                            <span class="info-box-number">${{ number_format($customer->credit_limit, 2) }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-{{ $customer->current_balance > 0 ? 'warning' : 'success' }}">
                            <i class="fas fa-balance-scale"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Saldo Actual</span>
                            <span class="info-box-number">${{ number_format($customer->current_balance, 2) }}</span>
                        </div>
                    </div>

                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-primary"><i class="fas fa-shopping-cart"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Ventas</span>
                            <span class="info-box-number">{{ $customer->sales->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('sales.create') }}?customer_id={{ $customer->id }}" 
                           class="btn btn-success btn-ajax">
                            <i class="fas fa-plus"></i> Nueva Venta
                        </a>
                        <a href="{{ route('payments.create') }}?customer_id={{ $customer->id }}" 
                           class="btn btn-primary btn-ajax">
                            <i class="fas fa-money-bill-wave"></i> Registrar Pago
                        </a>
                        <a href="{{ route('customers.edit', $customer) }}" 
                           class="btn btn-warning btn-ajax">
                            <i class="fas fa-edit"></i> Editar Cliente
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ventas Recientes -->
    @if($customer->sales->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i> Ventas Recientes
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('sales.index') }}?customer_id={{ $customer->id }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> Ver Todas
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th>Peso</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->sales as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.index') }}?customer_id={{ $sale->customer_id }}" class="text-primary">
                                        {{ $sale->sale_code ?? $sale->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                                <td>{{ number_format($sale->total_weight ?? 0, 2) }} kg</td>
                                <td>${{ number_format($sale->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge 
                                        @switch($sale->status)
                                            @case('delivered') badge-success @break
                                            @case('confirmed') badge-primary @break
                                            @case('cancelled') badge-danger @break
                                            @default badge-secondary
                                        @endswitch">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        @switch($sale->payment_status)
                                            @case('paid') badge-success @break
                                            @case('partial') badge-warning @break
                                            @default badge-danger
                                        @endswitch">
                                        {{ ucfirst($sale->payment_status) }}
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
    @endif

    <!-- Modal para Acciones Rápidas -->
    <div class="modal fade" id="quickActionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="quickActionTitle">Acción Rápida</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="quickActionBody">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Handle AJAX links
$(document).on('click', '.btn-ajax', function(e) {
    e.preventDefault();
    const url = $(this).attr('href');
    const title = $(this).text().trim();
    
    $('#quickActionTitle').text(title);
    $('#quickActionBody').html(`
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando...</p>
        </div>
    `);
    
    $('#quickActionModal').modal('show');
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        $('#quickActionBody').html(html);
    })
    .catch(error => {
        $('#quickActionBody').html(`
            <div class="alert alert-danger">
                Error al cargar el contenido. 
                <a href="${url}" target="_blank" class="btn btn-sm btn-outline-primary">
                    Abrir en nueva pestaña
                </a>
            </div>
        `);
    });
});
</script>
@endpush