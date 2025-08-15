<form id="editSaleForm">
    <input type="hidden" name="sale_id" value="{{ $sale->id }}">
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="edit_customer_id">Cliente *</label>
                <select name="customer_id" id="edit_customer_id" class="form-control" required>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} - {{ $customer->contact_person }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="edit_sale_date">Fecha de Venta *</label>
                <input type="date" name="sale_date" id="edit_sale_date" class="form-control" 
                       value="{{ $sale->sale_date->format('Y-m-d') }}" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="edit_delivery_date">Fecha de Entrega</label>
                <input type="date" name="delivery_date" id="edit_delivery_date" class="form-control" 
                       value="{{ $sale->delivery_date ? $sale->delivery_date->format('Y-m-d') : '' }}">
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="edit_invoice_number">Número de Factura</label>
                <input type="text" name="invoice_number" id="edit_invoice_number" class="form-control" 
                       value="{{ $sale->invoice_number }}" placeholder="Opcional">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-group">
                <label for="edit_notes">Notas</label>
                <textarea name="notes" id="edit_notes" class="form-control" rows="3" 
                         placeholder="Notas adicionales">{{ $sale->notes }}</textarea>
            </div>
        </div>
    </div>

    <!-- Información de Items (Solo lectura) -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        Información de Items (Solo lectura)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Nota:</strong> Los items y cantidades no se pueden modificar una vez creada la venta.
                        Para cambios en productos, cancele esta venta y cree una nueva.
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm">
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
                                        @php
                                            $qualityGrade = \App\Models\QualityGrade::where('name', $item->quality_grade)->first();
                                            $qualityColor = $qualityGrade ? $qualityGrade->color : '#6c757d';
                                        @endphp
                                        <span class="badge" style="background-color: {{ $qualityColor }}; color: white;">{{ $item->quality_grade }}</span>
                                    </td>
                                    <td>{{ number_format($item->weight, 2) }} kg</td>
                                    <td>${{ number_format($item->price_per_kg, 2) }}</td>
                                    <td><strong>${{ number_format($item->subtotal, 2) }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-primary">
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
        </div>
    </div>
</form>