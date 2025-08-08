<table class="table table-hover text-nowrap">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Contacto</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Ciudad</th>
            <th>Estado</th>
            <th>Saldo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($customers as $customer)
        <tr>
            <td>{{ $customer->id }}</td>
            <td>
                <strong>{{ $customer->name }}</strong>
                @if($customer->notes)
                    <br><small class="text-muted">{{ Str::limit($customer->notes, 50) }}</small>
                @endif
            </td>
            <td>{{ $customer->contact_person ?? '-' }}</td>
            <td>
                @if($customer->email)
                    <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                @else
                    -
                @endif
            </td>
            <td>
                @if($customer->phone)
                    <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                @else
                    -
                @endif
            </td>
            <td>{{ $customer->city ?? '-' }}</td>
            <td>
                @if($customer->status == 'active')
                    <span class="badge badge-success">Activo</span>
                @else
                    <span class="badge badge-danger">Inactivo</span>
                @endif
            </td>
            <td>
                @if($customer->current_balance > 0)
                    <span class="badge badge-warning">${{ number_format($customer->current_balance, 2) }}</span>
                @else
                    <span class="badge badge-success">$0.00</span>
                @endif
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-info" title="Ver" data-id="{{ $customer->id }}">
                        <i class="fas fa-eye"></i>
                    </button>
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-danger" title="Eliminar" data-id="{{ $customer->id }}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center py-4">
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay clientes registrados</h5>
                    <p class="text-muted">Comience agregando su primer cliente</p>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Cliente
                    </a>
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

@if($customers->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Mostrando {{ $customers->firstItem() }} a {{ $customers->lastItem() }} de {{ $customers->total() }} clientes
    </div>
    <div>
        {{ $customers->withQueryString()->onEachSide(2)->links() }}
    </div>
</div>
@endif

<script>
function showCustomerDetails(customerId) {
    $('#quickActionModal').modal('show');
    $('#quickActionTitle').text('Detalles del Cliente');
    $('#quickActionBody').html(`
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando detalles...</p>
        </div>
    `);
    
    fetch(`/customers/${customerId}`, {
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
                Error al cargar los detalles del cliente.
            </div>
        `);
    });
}

function quickDeleteCustomer(customerId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/customers/${customerId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Eliminado', data.message, 'success');
                    loadCustomers();
                } else {
                    Swal.fire('Error', data.message || 'Error al eliminar', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Error de conexión', 'error');
            });
        }
    });
}

function clearCustomerFilters() {
    $('#filterForm')[0].reset();
    loadCustomers();
}
</script>