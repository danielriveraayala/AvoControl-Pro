@php
    // Calculate combined balance from both payment systems
    $polymorphicPayments = $lot->payments->sum('amount');
    $lotPayments = $lot->lotPayments->sum('amount');
    $totalPaid = $polymorphicPayments + $lotPayments;
    $remainingBalance = $lot->total_purchase_cost - $totalPaid;
@endphp

<form id="lotPaymentForm">
    <input type="hidden" name="lot_id" value="{{ $lot->id }}">
    <input type="hidden" name="payment_type" value="supplier">

    <!-- Información del Lote -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Lote:</strong><br>
                            <span class="text-primary">{{ $lot->lot_code }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Proveedor:</strong><br>
                            {{ $lot->supplier->name }}
                        </div>
                        <div class="col-md-3">
                            <strong>Total Compra:</strong><br>
                            <span class="text-success">${{ number_format($lot->total_purchase_cost, 2) }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Saldo Pendiente:</strong><br>
                            <span class="text-danger">${{ number_format($remainingBalance, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Pagos Existentes -->
    @if($lot->payments->count() > 0 || $lot->lotPayments->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history"></i>
                        Pagos Anteriores
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Método</th>
                                    <th>Referencia</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Combine both payment systems
                                    $allPayments = collect();

                                    // Add polymorphic payments
                                    foreach($lot->payments as $payment) {
                                        $allPayments->push([
                                            'payment_date' => $payment->payment_date,
                                            'payment_method' => $payment->payment_method,
                                            'reference' => $payment->reference,
                                            'amount' => $payment->amount,
                                            'type' => 'polymorphic'
                                        ]);
                                    }

                                    // Add legacy payments
                                    foreach($lot->lotPayments as $payment) {
                                        $allPayments->push([
                                            'payment_date' => $payment->payment_date,
                                            'payment_method' => $payment->payment_type,
                                            'reference' => null,
                                            'amount' => $payment->amount,
                                            'type' => 'legacy'
                                        ]);
                                    }

                                    // Sort by payment date
                                    $allPayments = $allPayments->sortByDesc('payment_date');

                                    $methodLabels = [
                                        'cash' => 'Efectivo',
                                        'efectivo' => 'Efectivo',
                                        'transfer' => 'Transferencia',
                                        'transferencia' => 'Transferencia',
                                        'check' => 'Cheque',
                                        'cheque' => 'Cheque',
                                        'card' => 'Tarjeta',
                                        'credit' => 'Crédito',
                                        'deposito' => 'Depósito',
                                        'otro' => 'Otro'
                                    ];
                                @endphp
                                
                                @foreach($allPayments as $payment)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $methodLabels[$payment['payment_method']] ?? ucfirst($payment['payment_method']) }}</span>
                                        @if($payment['type'] == 'legacy')
                                            <small class="badge badge-secondary">LEGACY</small>
                                        @endif
                                    </td>
                                    <td>{{ $payment['reference'] ?? '-' }}</td>
                                    <td><strong>${{ number_format($payment['amount'], 2) }}</strong></td>
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

    <!-- Formulario de Nuevo Pago -->
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="payment_date">Fecha de Pago *</label>
                <input type="date" name="payment_date" id="payment_date" class="form-control"
                       value="{{ date('Y-m-d') }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="payment_method">Método de Pago *</label>
                <select name="payment_method" id="payment_method" class="form-control" required>
                    <option value="">Seleccione método</option>
                    <option value="cash">Efectivo</option>
                    <option value="transfer">Transferencia Bancaria</option>
                    <option value="check">Cheque</option>
                    <option value="card">Tarjeta</option>
                    <option value="credit">Crédito</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="amount">Monto del Pago *</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                    </div>
                    <input type="number" name="amount" id="amount" class="form-control"
                           min="0.01" step="0.01" max="{{ $remainingBalance }}"
                           placeholder="0.00" required>
                </div>
                <small class="form-text text-muted">
                    Máximo: ${{ number_format($remainingBalance, 2) }}
                </small>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="reference_number">Número de Referencia</label>
                <input type="text" name="reference_number" id="reference_number" class="form-control"
                       placeholder="Opcional">
                <small class="form-text text-muted">
                    Ej: número de cheque, referencia de transferencia, etc.
                </small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-group">
                <label for="payment_notes">Notas del Pago</label>
                <textarea name="notes" id="payment_notes" class="form-control" rows="3"
                         placeholder="Notas adicionales sobre el pago"></textarea>
            </div>
        </div>
    </div>

    <!-- Botones de Acceso Rápido -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <label class="form-label">Montos de Acceso Rápido:</label>
                    <div class="btn-group d-block" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="setLotPaymentAmount({{ $remainingBalance * 0.25 }})">
                            25% (${{ number_format($remainingBalance * 0.25, 2) }})
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="setLotPaymentAmount({{ $remainingBalance * 0.50 }})">
                            50% (${{ number_format($remainingBalance * 0.50, 2) }})
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="setLotPaymentAmount({{ $remainingBalance * 0.75 }})">
                            75% (${{ number_format($remainingBalance * 0.75, 2) }})
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm"
                                onclick="setLotPaymentAmount({{ $remainingBalance }})">
                            Total (${{ number_format($remainingBalance, 2) }})
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function setLotPaymentAmount(amount) {
    document.getElementById('amount').value = amount.toFixed(2);
}
</script>
