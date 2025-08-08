<table class="table table-hover text-nowrap">
    <thead>
        <tr>
            <th>Código</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Peso</th>
            <th>Monto</th>
            <th>Estado Venta</th>
            <th>Estado Pago</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sales as $sale)
        <tr>
            <td>
                <strong class="text-primary">{{ $sale->sale_code ?? $sale->invoice_number }}</strong>
                <br><small class="text-muted">
                    <i class="fas fa-receipt"></i> {{ $sale->invoice_number }}
                </small>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div>
                        <strong>{{ $sale->customer->name }}</strong>
                        @if($sale->customer->customer_type)
                            <br><span class="badge badge-sm 
                                @switch($sale->customer->customer_type)
                                    @case('mayorista') badge-primary @break
                                    @case('distribuidor') badge-info @break
                                    @default badge-secondary
                                @endswitch">
                                {{ ucfirst($sale->customer->customer_type) }}
                            </span>
                        @endif
                    </div>
                </div>
            </td>
            <td>
                <span class="badge badge-info">{{ $sale->sale_date->format('d/m/Y') }}</span>
                <br><small class="text-muted">{{ $sale->sale_date->diffForHumans() }}</small>
                @if($sale->delivery_date)
                    <br><small class="text-success">
                        <i class="fas fa-truck"></i> {{ $sale->delivery_date->format('d/m/Y') }}
                    </small>
                @endif
            </td>
            <td>
                <strong>{{ number_format($sale->total_weight ?? 0, 2) }} kg</strong>
                @if($sale->saleItems->count() > 0)
                    <br><small class="text-muted">
                        {{ $sale->saleItems->count() }} 
                        {{ $sale->saleItems->count() == 1 ? 'item' : 'items' }}
                    </small>
                @endif
            </td>
            <td>
                <strong class="text-success">${{ number_format($sale->total_amount, 2) }}</strong>
                @if($sale->saleItems->count() > 0 && $sale->total_weight > 0)
                    <br><small class="text-muted">
                        ${{ number_format($sale->total_amount / $sale->total_weight, 2) }}/kg
                    </small>
                @endif
            </td>
            <td>
                @switch($sale->status)
                    @case('draft')
                        <span class="badge badge-secondary">
                            <i class="fas fa-edit"></i> Borrador
                        </span>
                        @break
                    @case('confirmed')
                        <span class="badge badge-primary">
                            <i class="fas fa-check"></i> Confirmado
                        </span>
                        @break
                    @case('shipped')
                        <span class="badge badge-info">
                            <i class="fas fa-shipping-fast"></i> Enviado
                        </span>
                        @break
                    @case('delivered')
                        <span class="badge badge-success">
                            <i class="fas fa-handshake"></i> Entregado
                        </span>
                        @break
                    @case('cancelled')
                        <span class="badge badge-danger">
                            <i class="fas fa-times"></i> Cancelado
                        </span>
                        @break
                    @default
                        <span class="badge badge-secondary">{{ ucfirst($sale->status) }}</span>
                @endswitch
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <span class="payment-status-indicator 
                        @switch($sale->payment_status)
                            @case('paid') payment-paid @break
                            @case('partial') payment-partial @break  
                            @case('overdue') payment-overdue @break
                            @default payment-pending
                        @endswitch"></span>
                    @switch($sale->payment_status)
                        @case('pending')
                            <span class="badge badge-warning">Pendiente</span>
                            @break
                        @case('partial')
                            <span class="badge badge-info">Parcial</span>
                            @break
                        @case('paid')
                            <span class="badge badge-success">Pagado</span>
                            @break
                        @case('overdue')
                            <span class="badge badge-danger">Vencido</span>
                            @break
                        @default
                            <span class="badge badge-secondary">{{ ucfirst($sale->payment_status) }}</span>
                    @endswitch
                </div>
            </td>
            <td>
                <div class="btn-group btn-group-sm quick-actions">
                    <button type="button" 
                            class="btn btn-info" 
                            onclick="showSaleDetails({{ $sale->id }})"
                            data-toggle="tooltip" 
                            title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                    <a href="{{ route('sales.edit', $sale) }}" 
                       class="btn btn-primary btn-ajax" 
                       data-toggle="tooltip" 
                       title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    @if($sale->status !== 'cancelled')
                        <div class="btn-group btn-group-sm">
                            <button type="button" 
                                    class="btn btn-secondary dropdown-toggle dropdown-icon" 
                                    data-toggle="dropdown">
                            </button>
                            <div class="dropdown-menu">
                                @if($sale->status === 'draft')
                                    <a class="dropdown-item" href="#" onclick="changeStatus({{ $sale->id }}, 'confirmed')">
                                        <i class="fas fa-check text-success"></i> Confirmar
                                    </a>
                                @endif
                                @if($sale->status === 'confirmed')
                                    <a class="dropdown-item" href="#" onclick="changeStatus({{ $sale->id }}, 'delivered')">
                                        <i class="fas fa-truck text-info"></i> Marcar Entregado
                                    </a>
                                @endif
                                @if($sale->payment_status !== 'paid')
                                    <a class="dropdown-item" href="#" onclick="registerPayment({{ $sale->id }})">
                                        <i class="fas fa-dollar-sign text-success"></i> Registrar Pago
                                    </a>
                                @endif
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" onclick="printInvoice({{ $sale->id }})">
                                    <i class="fas fa-print text-primary"></i> Imprimir Factura
                                </a>
                                @if(in_array($sale->status, ['draft', 'confirmed']))
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#" onclick="changeStatus({{ $sale->id }}, 'cancelled')">
                                        <i class="fas fa-ban"></i> Cancelar Venta
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center py-5">
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted mb-2">No hay ventas registradas</h4>
                    <p class="text-muted mb-3">
                        @if(request()->hasAny(['status', 'payment_status', 'customer_id', 'date_from', 'date_to']))
                            No se encontraron ventas con los filtros aplicados.
                            <button class="btn btn-sm btn-outline-primary ml-2" onclick="clearFilters()">
                                <i class="fas fa-times"></i> Limpiar Filtros
                            </button>
                        @else
                            Comience registrando su primera venta
                        @endif
                    </p>
                    @if(!request()->hasAny(['status', 'payment_status', 'customer_id', 'date_from', 'date_to']))
                        <a href="{{ route('sales.create') }}" class="btn btn-success btn-lg btn-ajax">
                            <i class="fas fa-plus"></i> Registrar Primera Venta
                        </a>
                    @endif
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($sales->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Mostrando {{ $sales->firstItem() }} a {{ $sales->lastItem() }} de {{ $sales->total() }} ventas
    </div>
    <div>
        {{ $sales->withQueryString()->onEachSide(2)->links() }}
    </div>
</div>
@endif

<script>
function registerPayment(saleId) {
    window.location.href = `{{ route('payments.create') }}?sale_id=${saleId}`;
}

function printInvoice(saleId) {
    window.open(`/sales/${saleId}/invoice`, '_blank');
}

function clearFilters() {
    $('#filterForm')[0].reset();
    loadSales();
}
</script>