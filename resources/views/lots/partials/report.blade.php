<script>
$('#reportModalTitle').text('Reporte del Lote {{ $lot->lot_code }}');
</script>

<div class="row">
            <!-- Información General -->
            <div class="col-md-6">
                <h5 class="text-primary"><i class="fas fa-info-circle"></i> Información General</h5>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Código:</strong></td>
                        <td>{{ $lot->lot_code }}</td>
                    </tr>
                    <tr>
                        <td><strong>Proveedor:</strong></td>
                        <td>
                            @if($lot->supplier)
                                {{ $lot->supplier->name }}
                                @if($lot->supplier->city)
                                    <br><small class="text-muted">{{ $lot->supplier->city }}</small>
                                @endif
                            @else
                                <span class="text-muted">🕶️ Anónimo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Fecha de Compra:</strong></td>
                        <td>{{ $lot->harvest_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Calidad:</strong></td>
                        <td>
                            @php
                                $badgeClass = match($lot->quality_grade) {
                                    'Primera' => 'success',
                                    'Segunda' => 'warning',
                                    'Tercera' => 'info',
                                    'Industrial' => 'secondary',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge badge-{{ $badgeClass }}">{{ $lot->quality_grade }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            @php
                                $statusClass = match($lot->status) {
                                    'active' => 'primary',
                                    'partial' => 'warning',
                                    'sold' => 'success',
                                    'disponible' => 'primary',
                                    'vendido_parcial' => 'warning',
                                    'vendido' => 'success',
                                    'cancelado' => 'danger',
                                    default => 'secondary'
                                };
                                $statusText = match($lot->status) {
                                    'active' => 'Activo',
                                    'partial' => 'Parcial',
                                    'sold' => 'Vendido',
                                    'disponible' => 'Disponible',
                                    'vendido_parcial' => 'Vendido Parcial',
                                    'vendido' => 'Vendido',
                                    'cancelado' => 'Cancelado',
                                    default => ucfirst($lot->status)
                                };
                            @endphp
                            <span class="badge badge-{{ $statusClass }}">{{ $statusText }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Métricas de Peso -->
            <div class="col-md-6">
                <h5 class="text-success"><i class="fas fa-weight"></i> Métricas de Peso</h5>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Peso Total:</strong></td>
                        <td>{{ number_format($metrics['weight_metrics']['total'], 2) }} kg</td>
                    </tr>
                    <tr>
                        <td><strong>Peso Vendido:</strong></td>
                        <td>{{ number_format($metrics['weight_metrics']['sold'], 2) }} kg</td>
                    </tr>
                    <tr>
                        <td><strong>Peso Disponible:</strong></td>
                        <td>{{ number_format($metrics['weight_metrics']['available'], 2) }} kg</td>
                    </tr>
                    <tr>
                        <td><strong>% Vendido:</strong></td>
                        <td>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success" style="width: {{ $metrics['weight_metrics']['sold_percentage'] }}%"></div>
                            </div>
                            {{ number_format($metrics['weight_metrics']['sold_percentage'], 1) }}%
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <!-- Métricas Financieras -->
            <div class="col-md-6">
                <h5 class="text-warning"><i class="fas fa-dollar-sign"></i> Métricas Financieras</h5>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Precio Compra/kg:</strong></td>
                        <td>${{ number_format($lot->purchase_price_per_kg, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Costo Total Compra:</strong></td>
                        <td>${{ number_format($metrics['financial_metrics']['purchase_cost'], 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Ingresos Ventas:</strong></td>
                        <td>${{ number_format($metrics['financial_metrics']['revenue'], 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Ganancia Actual:</strong></td>
                        <td class="{{ $metrics['financial_metrics']['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            <strong>${{ number_format($metrics['financial_metrics']['profit'], 2) }}</strong>
                        </td>
                    </tr>
                    @if($metrics['financial_metrics']['suggested_price_per_kg'])
                    <tr>
                        <td><strong>Precio Venta Sugerido:</strong></td>
                        <td>${{ number_format($metrics['financial_metrics']['suggested_price_per_kg'], 2) }}/kg</td>
                    </tr>
                    <tr class="table-info">
                        <td><strong>Ingresos Potenciales:</strong></td>
                        <td><strong>${{ number_format($metrics['financial_metrics']['potential_revenue'], 2) }}</strong></td>
                    </tr>
                    <tr class="table-info">
                        <td><strong>Ganancia Potencial:</strong></td>
                        <td class="{{ $metrics['financial_metrics']['potential_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            <strong>${{ number_format($metrics['financial_metrics']['potential_profit'], 2) }}</strong>
                            @if($metrics['financial_metrics']['potential_margin'])
                                <br><small>({{ number_format($metrics['financial_metrics']['potential_margin'], 1) }}% margen)</small>
                            @endif
                        </td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Estado de Pagos -->
            <div class="col-md-6">
                <h5 class="text-success"><i class="fas fa-money-bill-wave"></i> Estado de Pagos</h5>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Total a Pagar:</strong></td>
                        <td>${{ number_format($lot->total_purchase_cost, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Monto Pagado:</strong></td>
                        <td class="text-success"><strong>${{ number_format($lot->amount_paid ?? 0, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Saldo Pendiente:</strong></td>
                        <td class="{{ ($lot->amount_owed ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                            <strong>${{ number_format($lot->amount_owed ?? 0, 2) }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Estado del Pago:</strong></td>
                        <td>
                            @php
                                $paymentStatusClass = match($lot->payment_status ?? 'pending') {
                                    'paid' => 'success',
                                    'partial' => 'warning',
                                    'pending' => 'danger',
                                    default => 'secondary'
                                };
                                $paymentStatusText = match($lot->payment_status ?? 'pending') {
                                    'paid' => 'Pagado Completo',
                                    'partial' => 'Pago Parcial',
                                    'pending' => 'Pendiente',
                                    default => 'Desconocido'
                                };
                            @endphp
                            <span class="badge badge-{{ $paymentStatusClass }}">{{ $paymentStatusText }}</span>
                        </td>
                    </tr>
                    @if($lot->lotPayments && $lot->lotPayments->count() > 0)
                    <tr>
                        <td colspan="2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                {{ $lot->lotPayments->count() }} pago{{ $lot->lotPayments->count() > 1 ? 's' : '' }} registrado{{ $lot->lotPayments->count() > 1 ? 's' : '' }}
                            </small>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        @if($lot->lotPayments && $lot->lotPayments->count() > 0)
        <div class="row mt-3">
            <div class="col-12">
                <h5 class="text-success"><i class="fas fa-history"></i> Historial de Pagos</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Tipo de Pago</th>
                                <th>Registrado por</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lot->lotPayments->sortBy('payment_date') as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                <td class="text-success"><strong>${{ number_format($payment->amount, 2) }}</strong></td>
                                <td>
                                    @php
                                        $paymentTypeClass = match($payment->payment_type) {
                                            'efectivo' => 'success',
                                            'transferencia' => 'info',
                                            'cheque' => 'warning',
                                            'deposito' => 'primary',
                                            default => 'secondary'
                                        };
                                        $paymentTypeText = match($payment->payment_type) {
                                            'efectivo' => 'Efectivo',
                                            'transferencia' => 'Transferencia',
                                            'cheque' => 'Cheque',
                                            'deposito' => 'Depósito',
                                            default => ucfirst($payment->payment_type)
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $paymentTypeClass }}">{{ $paymentTypeText }}</span>
                                </td>
                                <td>{{ $payment->paidByUser ? $payment->paidByUser->name : 'Sistema' }}</td>
                                <td>
                                    @if($payment->notes)
                                        <small>{{ $payment->notes }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold bg-light">
                                <td><strong>Total Pagado:</strong></td>
                                <td class="text-success"><strong>${{ number_format($lot->lotPayments->sum('amount'), 2) }}</strong></td>
                                <td colspan="3">
                                    <strong>Saldo Restante: 
                                        <span class="{{ ($lot->amount_owed ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                            ${{ number_format($lot->amount_owed ?? 0, 2) }}
                                        </span>
                                    </strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <div class="row mt-3">
            <!-- Notas -->
            <div class="col-md-12">
                <h5 class="text-info"><i class="fas fa-sticky-note"></i> Notas</h5>
                @php
                    $metadata = is_array($lot->metadata) ? $lot->metadata : json_decode($lot->metadata ?? '{}', true);
                    $notes = $metadata['notas'] ?? null;
                @endphp
                @if($notes)
                    <div class="alert alert-light">
                        {{ $notes }}
                    </div>
                @else
                    <p class="text-muted">No hay notas para este lote.</p>
                @endif
            </div>
        </div>

        @if($lot->saleItems->count() > 0)
        <div class="row mt-3">
            <div class="col-12">
                <h5 class="text-primary"><i class="fas fa-shopping-cart"></i> Historial de Ventas</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Peso Vendido</th>
                                <th>Precio/kg</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lot->saleItems as $item)
                            <tr>
                                <td>{{ $item->sale->sale_date->format('d/m/Y') }}</td>
                                <td>{{ $item->sale->customer->name }}</td>
                                <td>{{ number_format($item->weight, 2) }} kg</td>
                                <td>${{ number_format($item->price_per_kg, 2) }}</td>
                                <td>${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="2"><strong>Totales:</strong></td>
                                <td><strong>{{ number_format($lot->saleItems->sum('weight'), 2) }} kg</strong></td>
                                <td>-</td>
                                <td><strong>${{ number_format($lot->saleItems->sum('subtotal'), 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif
</div>