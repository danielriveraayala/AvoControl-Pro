@extends('layouts.admin')

@section('title', 'Registrar Pago')
@section('page-title', 'Registrar Nuevo Pago')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Pagos</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-money-bill-wave"></i>
                    Registrar Pago General
                </h3>
            </div>
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Tipo de Pago *</label>
                                <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                    <option value="">Seleccione tipo</option>
                                    <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>
                                        <i class="fas fa-arrow-up"></i> Ingreso
                                    </option>
                                    <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>
                                        <i class="fas fa-arrow-down"></i> Gasto
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="concept">Concepto *</label>
                                <select name="concept" id="concept" class="form-control @error('concept') is-invalid @enderror" required>
                                    <option value="">Seleccione concepto</option>
                                    <option value="general" {{ old('concept') == 'general' ? 'selected' : '' }}>General</option>
                                    <option value="operational" {{ old('concept') == 'operational' ? 'selected' : '' }}>Operacional</option>
                                    <option value="maintenance" {{ old('concept') == 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
                                    <option value="transport" {{ old('concept') == 'transport' ? 'selected' : '' }}>Transporte</option>
                                    <option value="services" {{ old('concept') == 'services' ? 'selected' : '' }}>Servicios</option>
                                    <option value="other" {{ old('concept') == 'other' ? 'selected' : '' }}>Otro</option>
                                </select>
                                @error('concept')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Monto *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="amount" id="amount" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           value="{{ old('amount') }}" 
                                           min="0.01" step="0.01" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_date">Fecha de Pago *</label>
                                <input type="date" name="payment_date" id="payment_date" 
                                       class="form-control @error('payment_date') is-invalid @enderror" 
                                       value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_method">Método de Pago *</label>
                                <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                    <option value="">Seleccione método</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>
                                        <i class="fas fa-money-bill"></i> Efectivo
                                    </option>
                                    <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>
                                        <i class="fas fa-university"></i> Transferencia
                                    </option>
                                    <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>
                                        <i class="fas fa-money-check"></i> Cheque
                                    </option>
                                    <option value="credit" {{ old('payment_method') == 'credit' ? 'selected' : '' }}>
                                        <i class="fas fa-credit-card"></i> Crédito
                                    </option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reference">Referencia</label>
                                <input type="text" name="reference" id="reference" 
                                       class="form-control @error('reference') is-invalid @enderror" 
                                       value="{{ old('reference') }}" 
                                       placeholder="Número de cheque, referencia bancaria, etc.">
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Descripción *</label>
                        <input type="text" name="description" id="description" 
                               class="form-control @error('description') is-invalid @enderror" 
                               value="{{ old('description') }}" 
                               placeholder="Breve descripción del pago" required>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="notes">Notas Adicionales</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" 
                                  rows="3" placeholder="Notas adicionales (opcional)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="row">
                        <div class="col-6">
                            <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                        <div class="col-6 text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Registrar Pago
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update UI based on payment type
    $('#type').change(function() {
        const type = $(this).val();
        const card = $('.card');
        
        if (type === 'income') {
            card.removeClass('card-outline-danger').addClass('card-outline-success');
            $('.card-title').html('<i class="fas fa-arrow-up text-success"></i> Registrar Ingreso');
        } else if (type === 'expense') {
            card.removeClass('card-outline-success').addClass('card-outline-danger');
            $('.card-title').html('<i class="fas fa-arrow-down text-danger"></i> Registrar Gasto');
        } else {
            card.removeClass('card-outline-success card-outline-danger');
            $('.card-title').html('<i class="fas fa-money-bill-wave"></i> Registrar Pago General');
        }
    });
});
</script>
@endpush