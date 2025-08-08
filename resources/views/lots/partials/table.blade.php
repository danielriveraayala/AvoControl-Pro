<table class="table table-hover text-nowrap">
    <thead>
        <tr>
            <th>Código</th>
            <th>Proveedor</th>
            <th>Fecha Cosecha</th>
            <th>Peso Total</th>
            <th>Disponible</th>
            <th>Calidad</th>
            <th>Estado</th>
            <th>Precio/kg</th>
            <th>Valor Total</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($lots as $lot)
        <tr>
            <td>
                <strong class="text-primary">{{ $lot->lot_code }}</strong>
                <br><small class="text-muted">{{ $lot->created_at->format('d/m/Y') }}</small>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div>
                        <strong>{{ $lot->supplier->name }}</strong>
                        @if($lot->supplier->city)
                            <br><small class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> {{ $lot->supplier->city }}
                            </small>
                        @endif
                    </div>
                </div>
            </td>
            <td>
                <span class="badge badge-info">{{ $lot->harvest_date->format('d/m/Y') }}</span>
                <br><small class="text-muted">{{ $lot->harvest_date->diffForHumans() }}</small>
            </td>
            <td>
                <strong>{{ number_format($lot->total_weight, 2) }} kg</strong>
                @if($lot->weight_sold > 0)
                    <div class="progress progress-sm mt-1">
                        <div class="progress-bar bg-success" 
                             style="width: {{ ($lot->weight_sold / $lot->total_weight) * 100 }}%"
                             title="Vendido: {{ number_format($lot->weight_sold, 2) }} kg">
                        </div>
                    </div>
                @endif
            </td>
            <td>
                <span class="badge {{ $lot->weight_available > 0 ? 'badge-success' : 'badge-secondary' }}">
                    {{ number_format($lot->weight_available, 2) }} kg
                </span>
            </td>
            <td>
                @switch($lot->quality_grade)
                    @case('Primera')
                        <span class="badge badge-success badge-quality">
                            <i class="fas fa-star"></i> Primera
                        </span>
                        @break
                    @case('Segunda')
                        <span class="badge badge-warning badge-quality">
                            <i class="fas fa-star-half-alt"></i> Segunda
                        </span>
                        @break
                    @case('Tercera')
                        <span class="badge badge-danger badge-quality">
                            <i class="far fa-star"></i> Tercera
                        </span>
                        @break
                    @default
                        <span class="badge badge-secondary">{{ $lot->quality_grade }}</span>
                @endswitch
            </td>
            <td>
                @switch($lot->status)
                    @case('active')
                        <span class="badge badge-primary">
                            <i class="fas fa-check-circle"></i> Activo
                        </span>
                        @break
                    @case('partial')
                        <span class="badge badge-warning">
                            <i class="fas fa-clock"></i> Parcial
                        </span>
                        @break
                    @case('sold')
                        <span class="badge badge-success">
                            <i class="fas fa-handshake"></i> Vendido
                        </span>
                        @break
                    @default
                        <span class="badge badge-secondary">{{ ucfirst($lot->status) }}</span>
                @endswitch
            </td>
            <td>
                <strong class="text-success">
                    ${{ number_format($lot->purchase_price_per_kg, 2) }}
                </strong>
            </td>
            <td>
                <strong class="text-primary">
                    ${{ number_format($lot->total_purchase_cost, 2) }}
                </strong>
            </td>
            <td>
                <div class="btn-group btn-group-sm quick-actions">
                    <a href="{{ route('lots.show', $lot) }}" 
                       class="btn btn-info" 
                       data-toggle="tooltip" 
                       title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('lots.edit', $lot) }}" 
                       class="btn btn-primary btn-ajax" 
                       data-toggle="tooltip" 
                       title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    @if($lot->status !== 'sold')
                        <button type="button" 
                                class="btn btn-success" 
                                onclick="createSale({{ $lot->id }})"
                                data-toggle="tooltip" 
                                title="Crear venta">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                    @endif
                    <div class="btn-group btn-group-sm">
                        <button type="button" 
                                class="btn btn-secondary dropdown-toggle dropdown-icon" 
                                data-toggle="dropdown">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('lots.show', $lot) }}">
                                <i class="fas fa-eye"></i> Ver Detalles
                            </a>
                            <a class="dropdown-item btn-ajax" href="{{ route('lots.edit', $lot) }}">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            @if($lot->saleItems->count() > 0)
                                <a class="dropdown-item" href="#" onclick="showSalesHistory({{ $lot->id }})">
                                    <i class="fas fa-history"></i> Ver Ventas
                                </a>
                            @endif
                            <div class="dropdown-divider"></div>
                            <button type="button" 
                                    class="dropdown-item text-danger" 
                                    data-id="{{ $lot->id }}">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="10" class="text-center py-5">
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-boxes fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted mb-2">No hay lotes registrados</h4>
                    <p class="text-muted mb-3">
                        @if(request()->hasAny(['status', 'quality', 'supplier_id', 'date_from', 'date_to']))
                            No se encontraron lotes con los filtros aplicados.
                            <button class="btn btn-sm btn-outline-primary ml-2" onclick="clearFilters()">
                                <i class="fas fa-times"></i> Limpiar Filtros
                            </button>
                        @else
                            Comience registrando su primer lote de aguacates
                        @endif
                    </p>
                    @if(!request()->hasAny(['status', 'quality', 'supplier_id', 'date_from', 'date_to']))
                        <a href="{{ route('lots.create') }}" class="btn btn-success btn-lg btn-ajax">
                            <i class="fas fa-plus"></i> Registrar Primer Lote
                        </a>
                    @endif
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($lots->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Mostrando {{ $lots->firstItem() }} a {{ $lots->lastItem() }} de {{ $lots->total() }} lotes
    </div>
    <div>
        {{ $lots->withQueryString()->onEachSide(2)->links() }}
    </div>
</div>
@endif

<script>
function createSale(lotId) {
    window.location.href = `{{ route('sales.create') }}?lot_id=${lotId}`;
}

function showSalesHistory(lotId) {
    // Load sales history for this lot
    $('#quickActionModal').modal('show');
    $('#quickActionTitle').text('Historial de Ventas');
    $('#quickActionBody').html(`
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando historial...</p>
        </div>
    `);
    
    fetch(`/lots/${lotId}/sales-history`, {
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
                Error al cargar el historial de ventas.
            </div>
        `);
    });
}

function clearFilters() {
    $('#filterForm')[0].reset();
    loadLots();
}
</script>