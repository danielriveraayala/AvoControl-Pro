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
                                $qualityGrade = \App\Models\QualityGrade::where('name', $lot->quality_grade)->first();
                                $qualityColor = $qualityGrade ? $qualityGrade->color : '#6c757d';
                            @endphp
                            <span class="badge" style="background-color: {{ $qualityColor }}; color: white;">{{ $lot->quality_grade }}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Información de Inventario -->
            <div class="col-md-6">
                <h5 class="text-success"><i class="fas fa-weight"></i> Información de Inventario</h5>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Peso Total:</strong></td>
                        <td>{{ number_format($lot->total_weight, 2) }} kg</td>
                    </tr>
                    <tr>
                        <td><strong>Contribuye al Acopio:</strong></td>
                        <td>
                            @php
                                $qualityGrade = \App\Models\QualityGrade::where('name', $lot->quality_grade)->first();
                                $qualityColor = $qualityGrade ? $qualityGrade->color : '#6c757d';
                            @endphp
                            <span class="badge" style="background-color: {{ $qualityColor }}; color: white;">{{ $lot->quality_grade }}</span>
                            <small class="text-muted d-block">Este lote forma parte del inventario total para ventas por calidad</small>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <!-- Información de Compra -->
            <div class="col-md-6">
                <h5 class="text-warning"><i class="fas fa-dollar-sign"></i> Información de Compra</h5>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Precio Compra/kg:</strong></td>
                        <td>${{ number_format($lot->purchase_price_per_kg, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Costo Total Compra:</strong></td>
                        <td><strong>${{ number_format($lot->total_purchase_cost, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Las ganancias se calculan por acopio total, no por lote individual
                            </small>
                        </td>
                    </tr>
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
                    @if($lot->payments && $lot->payments->count() > 0)
                    <tr>
                        <td colspan="2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                {{ $lot->payments->count() }} pago{{ $lot->payments->count() > 1 ? 's' : '' }} registrado{{ $lot->payments->count() > 1 ? 's' : '' }}
                            </small>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        @if($lot->payments && $lot->payments->count() > 0)
        <div class="row mt-3">
            <div class="col-12">
                <h5 class="text-success"><i class="fas fa-history"></i> Historial de Pagos</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Método de Pago</th>
                                <th>Referencia</th>
                                <th>Registrado por</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lot->payments->sortBy(['payment_date', 'created_at']) as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d/m/Y H:i') }}</td>
                                <td class="text-success"><strong>${{ number_format($payment->amount, 2) }}</strong></td>
                                <td>
                                    @php
                                        $methodLabels = [
                                            'cash' => 'Efectivo',
                                            'transfer' => 'Transferencia',
                                            'check' => 'Cheque',
                                            'card' => 'Tarjeta',
                                            'credit' => 'Crédito'
                                        ];
                                        $methodClasses = [
                                            'cash' => 'success',
                                            'transfer' => 'info',
                                            'check' => 'warning',
                                            'card' => 'primary',
                                            'credit' => 'secondary'
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $methodClasses[$payment->payment_method] ?? 'secondary' }}">
                                        {{ $methodLabels[$payment->payment_method] ?? ucfirst($payment->payment_method) }}
                                    </span>
                                </td>
                                <td>{{ $payment->reference ?? '-' }}</td>
                                <td>{{ $payment->createdBy ? $payment->createdBy->name : 'Sistema' }}</td>
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
                                <td class="text-success"><strong>${{ number_format($lot->payments->sum('amount'), 2) }}</strong></td>
                                <td colspan="4">
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
                @if($lot->notes)
                    <div class="alert alert-light">
                        {{ $lot->notes }}
                    </div>
                @else
                    <p class="text-muted">No hay notas para este lote.</p>
                @endif
            </div>
        </div>

</div>