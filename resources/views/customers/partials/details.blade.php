<div class="row">
    <div class="col-md-8">
        <h5><i class="fas fa-user"></i> {{ $customer->name }}</h5>
        <div class="row">
            <div class="col-md-6">
                <dl class="row">
                    <dt class="col-sm-5">Tipo:</dt>
                    <dd class="col-sm-7">
                        <span class="badge 
                            @switch($customer->customer_type)
                                @case('mayorista') badge-primary @break
                                @case('distribuidor') badge-info @break
                                @default badge-secondary
                            @endswitch">
                            {{ ucfirst($customer->customer_type) }}
                        </span>
                    </dd>

                    <dt class="col-sm-5">Estado:</dt>
                    <dd class="col-sm-7">
                        <span class="badge {{ $customer->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                            {{ $customer->status === 'active' ? 'Activo' : 'Inactivo' }}
                        </span>
                    </dd>

                    <dt class="col-sm-5">Contacto:</dt>
                    <dd class="col-sm-7">{{ $customer->contact_person ?? 'No especificado' }}</dd>

                    <dt class="col-sm-5">Email:</dt>
                    <dd class="col-sm-7">
                        @if($customer->email)
                            <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                        @else
                            No especificado
                        @endif
                    </dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row">
                    <dt class="col-sm-5">Teléfono:</dt>
                    <dd class="col-sm-7">
                        @if($customer->phone)
                            <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                        @else
                            No especificado
                        @endif
                    </dd>

                    <dt class="col-sm-5">Ciudad:</dt>
                    <dd class="col-sm-7">{{ $customer->city ?? 'No especificado' }}</dd>

                    <dt class="col-sm-5">País:</dt>
                    <dd class="col-sm-7">{{ $customer->country ?? 'No especificado' }}</dd>

                    <dt class="col-sm-5">Crédito:</dt>
                    <dd class="col-sm-7">
                        <strong class="text-info">${{ number_format($customer->credit_limit, 2) }}</strong>
                    </dd>
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

    <div class="col-md-4">
        <!-- Estadísticas rápidas -->
        <div class="card">
            <div class="card-body">
                <div class="text-center">
                    <div class="row">
                        <div class="col-6">
                            <div class="description-block border-right">
                                <h5 class="description-header text-{{ $customer->current_balance > 0 ? 'warning' : 'success' }}">
                                    ${{ number_format($customer->current_balance, 2) }}
                                </h5>
                                <span class="description-text">SALDO ACTUAL</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <h5 class="description-header text-primary">{{ $customer->sales->count() }}</h5>
                                <span class="description-text">VENTAS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="d-grid gap-2">
            <a href="{{ route('sales.create') }}?customer_id={{ $customer->id }}" 
               class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nueva Venta
            </a>
            <a href="{{ route('customers.edit', $customer) }}" 
               class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>
</div>

@if($customer->sales->count() > 0)
<hr>
<h6><i class="fas fa-shopping-cart"></i> Últimas Ventas</h6>
<div class="table-responsive">
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>Código</th>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customer->sales->take(5) as $sale)
            <tr>
                <td>{{ $sale->sale_code ?? $sale->invoice_number }}</td>
                <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                <td>${{ number_format($sale->total_amount, 2) }}</td>
                <td>
                    <span class="badge badge-sm 
                        @switch($sale->status)
                            @case('delivered') badge-success @break
                            @case('confirmed') badge-primary @break
                            @default badge-secondary
                        @endswitch">
                        {{ ucfirst($sale->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif