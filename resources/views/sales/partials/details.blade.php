<div class="row">
    <!-- Información General -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    Información General
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Código:</strong></td>
                        <td>{{ $sale->sale_code }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cliente:</strong></td>
                        <td>{{ $sale->customer->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tipo:</strong></td>
                        <td>
                            @if($sale->customer->customer_type)
                                <span class="badge badge-{{ $sale->customer->customer_type == 'mayorista' ? 'primary' : 'info' }}">
                                    {{ ucfirst($sale->customer->customer_type) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Fecha Venta:</strong></td>
                        <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                    </tr>
                    @if($sale->delivery_date)
                    <tr>
                        <td><strong>Fecha Entrega:</strong></td>
                        <td>{{ $sale->delivery_date->format('d/m/Y') }}</td>
                    </tr>
                    @endif
                    @if($sale->invoice_number)
                    <tr>
                        <td><strong>Factura:</strong></td>
                        <td>{{ $sale->invoice_number }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            @switch($sale->status)
                                @case('draft')
                                    <span class="badge badge-secondary">Borrador</span>
                                    @break
                                @case('confirmed')
                                    <span class="badge badge-primary">Confirmado</span>
                                    @break
                                @case('delivered')
                                    <span class="badge badge-success">Entregado</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge badge-danger">Cancelado</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ ucfirst($sale->status) }}</span>
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Estado Pago:</strong></td>
                        <td>
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
                                @default
                                    <span class="badge badge-secondary">{{ ucfirst($sale->payment_status) }}</span>
                            @endswitch
                        </td>
                    </tr>
                </table>
                
                @if($sale->notes)
                <div class="mt-3">
                    <strong>Notas:</strong>
                    <p class="text-muted">{{ $sale->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Items de Venta -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart"></i>
                    Items de Venta
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Calidad</th>
                                <th>Peso (kg)</th>
                                <th>Precio/kg</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->saleItems as $item)
                            <tr>
                                <td>
                                    <span class="badge badge-primary">{{ $item->quality_grade }}</span>
                                </td>
                                <td>{{ number_format($item->weight, 2) }} kg</td>
                                <td>${{ number_format($item->price_per_kg, 2) }}</td>
                                <td><strong>${{ number_format($item->subtotal, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <th>TOTALES</th>
                                <th>{{ number_format($sale->total_weight, 2) }} kg</th>
                                <th>${{ number_format($sale->total_amount / $sale->total_weight, 2) }}/kg</th>
                                <th><strong>${{ number_format($sale->total_amount, 2) }}</strong></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Asignaciones de Lotes -->
        @if($sale->saleItems->flatMap->allocations->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes"></i>
                    Lotes Asignados
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Lote</th>
                                <th>Proveedor</th>
                                <th>Calidad</th>
                                <th>Peso Asignado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->saleItems as $item)
                                @foreach($item->allocations as $allocation)
                                <tr>
                                    <td>{{ $allocation->lot->lot_code }}</td>
                                    <td>{{ $allocation->lot->supplier->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-sm badge-secondary">
                                            {{ $allocation->lot->qualityGrade->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($allocation->weight_allocated, 2) }} kg</td>
                                    <td>{{ $allocation->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Historial de Pagos -->
        @if($sale->payments->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-dollar-sign"></i>
                    Historial de Pagos
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($payment->payment_method) }}</span>
                                </td>
                                <td>{{ $payment->reference_number ?? '-' }}</td>
                                <td><strong>${{ number_format($payment->amount, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <th colspan="3">Total Pagado:</th>
                                <th><strong>${{ number_format($sale->payments->sum('amount'), 2) }}</strong></th>
                            </tr>
                            <tr class="bg-warning">
                                <th colspan="3">Saldo Pendiente:</th>
                                <th><strong>${{ number_format($sale->total_amount - $sale->payments->sum('amount'), 2) }}</strong></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>