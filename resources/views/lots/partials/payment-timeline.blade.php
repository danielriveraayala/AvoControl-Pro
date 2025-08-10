@php
    $totalAmount = $lot->total_purchase_cost;
    // Use only polymorphic payment system
    $totalPaid = $lot->payments->sum('amount');
    $remainingBalance = $totalAmount - $totalPaid;
    $paidPercentage = $totalAmount > 0 ? ($totalPaid / $totalAmount) * 100 : 0;
@endphp

<div class="payment-timeline-container">
    <!-- Resumen de Pagos -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    <h5 class="text-muted mb-1">Total Compra</h5>
                    <h3 class="text-primary">${{ number_format($totalAmount, 2) }}</h3>
                </div>
                <div class="col-md-3 text-center">
                    <h5 class="text-muted mb-1">Total Pagado</h5>
                    <h3 class="text-success">${{ number_format($totalPaid, 2) }}</h3>
                </div>
                <div class="col-md-3 text-center">
                    <h5 class="text-muted mb-1">Saldo Pendiente</h5>
                    <h3 class="text-{{ $remainingBalance > 0 ? 'danger' : 'success' }}">${{ number_format($remainingBalance, 2) }}</h3>
                </div>
                <div class="col-md-3 text-center">
                    <h5 class="text-muted mb-1">Estado</h5>
                    @if($lot->payment_status == 'paid')
                        <h3><span class="badge badge-success">PAGADO</span></h3>
                    @elseif($lot->payment_status == 'partial')
                        <h3><span class="badge badge-warning">PARCIAL</span></h3>
                    @else
                        <h3><span class="badge badge-danger">PENDIENTE</span></h3>
                    @endif
                </div>
            </div>

            <!-- Barra de Progreso -->
            <div class="progress mt-3" style="height: 30px;">
                <div class="progress-bar {{ $paidPercentage >= 100 ? 'bg-success' : 'bg-warning' }}"
                     role="progressbar"
                     style="width: {{ min($paidPercentage, 100) }}%"
                     aria-valuenow="{{ $paidPercentage }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                    <strong>{{ number_format($paidPercentage, 1) }}%</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline de Pagos -->
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="card-title">
                <i class="fas fa-history"></i> Timeline de Pagos
            </h3>
            @if($remainingBalance > 0)
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" onclick="registerLotPayment({{ $lot->id }}, true)">
                        <i class="fas fa-plus"></i> Registrar Nuevo Pago
                    </button>
                </div>
            @endif
        </div>
        <div class="card-body">
            @if($lot->payments->count() > 0)
                <div class="timeline">
                    <!-- Timeline Item Start -->
                    <div class="time-label">
                        <span class="bg-primary">Inicio de Compra</span>
                    </div>

                    <div>
                        <i class="fas fa-shopping-cart bg-primary"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="fas fa-clock"></i> {{ $lot->harvest_date->format('d/m/Y H:i') }}
                            </span>
                            <h3 class="timeline-header">
                                Lote Registrado
                            </h3>
                            <div class="timeline-body">
                                <strong>Código:</strong> {{ $lot->lot_code }}<br>
                                <strong>Proveedor:</strong> {{ $lot->supplier->name }}<br>
                                <strong>Monto Total:</strong> ${{ number_format($lot->total_purchase_cost, 2) }}
                            </div>
                        </div>
                    </div>

                    @php
                        $accumulatedAmount = 0;
                        // Use only polymorphic payments, sorted by date
                        $allPayments = $lot->payments->sortBy([
                            ['payment_date', 'asc'],
                            ['created_at', 'asc']
                        ]);
                    @endphp

                    @foreach($allPayments as $payment)
                        @php
                            $accumulatedAmount += $payment->amount;
                            $percentageAtTime = ($accumulatedAmount / $totalAmount) * 100;
                            $isLastPayment = $accumulatedAmount >= $totalAmount;

                            $methodIcons = [
                                'cash' => 'fa-money-bill-wave',
                                'transfer' => 'fa-exchange-alt',
                                'check' => 'fa-money-check',
                                'card' => 'fa-credit-card',
                                'credit' => 'fa-handshake'
                            ];

                            $methodLabels = [
                                'cash' => 'Efectivo',
                                'transfer' => 'Transferencia',
                                'check' => 'Cheque',
                                'card' => 'Tarjeta',
                                'credit' => 'Crédito'
                            ];

                            $icon = $methodIcons[$payment->payment_method] ?? 'fa-dollar-sign';
                            $methodLabel = $methodLabels[$payment->payment_method] ?? ucfirst($payment->payment_method);
                        @endphp

                        <div>
                            <i class="fas {{ $icon }} bg-{{ $isLastPayment ? 'success' : 'warning' }}"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-clock"></i> {{ $payment->payment_date->format('d/m/Y H:i') }}
                                </span>
                                <h3 class="timeline-header">
                                    Pago #{{ $loop->iteration }} - {{ $methodLabel }}
                                    @if($isLastPayment)
                                        <span class="badge badge-success ml-2">COMPLETADO</span>
                                    @else
                                        <span class="badge badge-warning ml-2">PARCIAL</span>
                                    @endif
                                </h3>
                                <div class="timeline-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Código:</strong> {{ $payment->payment_code }}<br>
                                            <strong>Monto:</strong> <span class="text-success font-weight-bold">${{ number_format($payment->amount, 2) }}</span><br>
                                            @if($payment->reference)
                                                <strong>Referencia:</strong> {{ $payment->reference }}<br>
                                            @endif
                                            @if($payment->notes)
                                                <strong>Notas:</strong> {{ $payment->notes }}<br>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Acumulado:</strong> ${{ number_format($accumulatedAmount, 2) }}<br>
                                            <strong>Restante:</strong> ${{ number_format($totalAmount - $accumulatedAmount, 2) }}<br>
                                            <strong>Progreso:</strong> {{ number_format($percentageAtTime, 1) }}%
                                            <div class="progress mt-1" style="height: 15px;">
                                                <div class="progress-bar bg-{{ $percentageAtTime >= 100 ? 'success' : 'info' }}"
                                                     style="width: {{ min($percentageAtTime, 100) }}%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="timeline-footer">
                                    @if($payment->createdBy)
                                        <small class="text-muted">Registrado por: {{ $payment->createdBy->name }}</small>
                                    @endif
                                    @if($payment->created_at->format('Y-m-d') != $payment->payment_date->format('Y-m-d'))
                                        <small class="text-muted ml-2">| Ingresado: {{ $payment->created_at->format('d/m/Y H:i') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Final del Timeline -->
                    @if($lot->payment_status == 'paid')
                        <div class="time-label">
                            <span class="bg-success">
                                <i class="fas fa-check"></i> Lote Pagado Completamente
                            </span>
                        </div>
                    @else
                        <div class="time-label">
                            <span class="bg-warning">
                                <i class="fas fa-clock"></i> Esperando Pagos...
                            </span>
                        </div>
                    @endif

                    <div>
                        <i class="fas fa-flag-checkered bg-gray"></i>
                    </div>
                </div>
            @else
                <!-- No hay pagos -->
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">No hay pagos registrados</h4>
                    <p class="text-muted">Este lote aún no tiene ningún pago registrado al proveedor.</p>
                    <button type="button" class="btn btn-success mt-3" onclick="registerLotPayment({{ $lot->id }}, true)">
                        <i class="fas fa-plus"></i> Registrar Primer Pago
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
/* Timeline Styles */
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 38px;
    height: 100%;
    width: 3px;
    background: #dee2e6;
}

.timeline > div > .timeline-item {
    background: #fff;
    border-radius: 5px;
    margin-top: 0;
    padding: 0;
    position: relative;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    margin-bottom: 20px;
    margin-left: 60px;
}

.timeline > div > .timeline-item > .time {
    color: #999;
    float: right;
    padding: 10px;
    font-size: 12px;
}

.timeline > div > .timeline-item > .timeline-header {
    margin: 0;
    color: #495057;
    border-bottom: 1px solid #f4f4f4;
    padding: 10px;
    font-size: 16px;
    line-height: 1.1;
}

.timeline > div > .timeline-item > .timeline-body {
    padding: 10px;
}

.timeline > div > .timeline-item > .timeline-footer {
    padding: 5px 10px;
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.timeline > div > i {
    position: absolute;
    left: 18px;
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    top: 0;
    border-radius: 50%;
    font-size: 16px;
    background: #fff;
    border: 2px solid #dee2e6;
    color: #6c757d;
}

.timeline > .time-label > span {
    font-weight: 600;
    padding: 5px 10px;
    display: inline-block;
    background-color: #fff;
    border-radius: 4px;
    margin-left: 60px;
    margin-bottom: 15px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    right: 100%;
    top: 15px;
    width: 0;
    height: 0;
    border-top: 10px solid transparent;
    border-right: 10px solid #fff;
    border-bottom: 10px solid transparent;
}
</style>
