@extends('layouts.admin')

@section('title', 'Clientes')
@section('page-title', 'Clientes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Clientes</li>
@endsection

@section('content')
    <!-- Estadísticas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="totalCustomers">0</h3>
                    <p>Total Clientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activeCustomers">0</h3>
                    <p>Clientes Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="totalBalance">$0</h3>
                    <p>Cuentas por Cobrar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3 id="mayoristasCount">0</h3>
                    <p>Mayoristas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-store"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Lista de Clientes</h3>
                    <div class="card-tools">
                        <button type="button" id="newCustomerBtn" class="btn btn-success">
                            <i class="fas fa-plus"></i> Nuevo Cliente
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="customersTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Contacto</th>
                                <th>Teléfono</th>
                                <th>Ciudad</th>
                                <th>Estado</th>
                                <th>Saldo</th>
                                <th width="120">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Modal para Crear/Editar Cliente -->
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="customerForm">
                @csrf
                <input type="hidden" id="customerId" name="customer_id">
                <input type="hidden" id="methodField" name="_method" value="POST">

                <div class="modal-header bg-primary">
                    <h4 class="modal-title" id="modalTitle">Nuevo Cliente</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" required>
                                <div class="invalid-feedback" id="name-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Cliente <span class="text-danger">*</span></label>
                                <select name="customer_type" id="customer_type" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <option value="mayorista">Mayorista</option>
                                    <option value="minorista">Minorista</option>
                                    <option value="distribuidor">Distribuidor</option>
                                </select>
                                <div class="invalid-feedback" id="customer_type-error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Persona de Contacto</label>
                                <input type="text" name="contact_person" id="contact_person" class="form-control">
                                <div class="invalid-feedback" id="contact_person-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="phone" id="phone" class="form-control">
                                <div class="invalid-feedback" id="phone-error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="email" class="form-control">
                                <div class="invalid-feedback" id="email-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Límite de Crédito <span class="text-danger">*</span></label>
                                <input type="number" name="credit_limit" id="credit_limit" class="form-control" step="0.01" min="0" required>
                                <div class="invalid-feedback" id="credit_limit-error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Dirección</label>
                                <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                                <div class="invalid-feedback" id="address-error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ciudad</label>
                                <input type="text" name="city" id="city" class="form-control">
                                <div class="invalid-feedback" id="city-error"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Estado</label>
                                <input type="text" name="state" id="state" class="form-control" value="Michoacán">
                                <div class="invalid-feedback" id="state-error"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>País</label>
                                <input type="text" name="country" id="country" class="form-control" value="México">
                                <div class="invalid-feedback" id="country-error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Estado <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="active">Activo</option>
                                    <option value="inactive">Inactivo</option>
                                </select>
                                <div class="invalid-feedback" id="status-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Código Postal</label>
                                <input type="text" name="postal_code" id="postal_code" class="form-control">
                                <div class="invalid-feedback" id="postal_code-error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Notas</label>
                                <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                                <div class="invalid-feedback" id="notes-error"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Ver Detalles -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title">Detalles del Cliente</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let customersTable;

$(document).ready(function() {
    // Initialize DataTable
    customersTable = $('#customersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("customers.index") }}',
            data: function(d) {
                d.ajax = 1;
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'customer_type', name: 'customer_type', orderable: false },
            { data: 'contact_person', name: 'contact_person' },
            { data: 'phone', name: 'phone' },
            { data: 'city', name: 'city' },
            { data: 'status', name: 'status', orderable: false },
            { data: 'current_balance', name: 'current_balance', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']],
        drawCallback: function() {
            loadStatistics();
        }
    });

    // New Customer button
    $('#newCustomerBtn').click(function() {
        resetForm();
        $('#modalTitle').text('Nuevo Cliente');
        $('#methodField').val('POST');
        $('#customerModal').modal('show');
    });

    // Submit form
    $('#customerForm').submit(function(e) {
        e.preventDefault();
        saveCustomer();
    });
});

// Load statistics
function loadStatistics() {
    $.ajax({
        url: '{{ route("customers.index") }}',
        method: 'GET',
        data: { ajax: 1, stats_only: 1 },
        success: function(response) {
            if (response.stats) {
                $('#totalCustomers').text(response.stats.total);
                $('#activeCustomers').text(response.stats.active);
                $('#totalBalance').text('$' + response.stats.balance);
                $('#mayoristasCount').text(response.stats.mayoristas);
            }
        }
    });
}

// View customer details
function viewCustomer(id) {
    $('#viewModalBody').html(`
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando...</p>
        </div>
    `);

    $('#viewModal').modal('show');

    $.ajax({
        url: '{{ route("customers.show", ":id") }}'.replace(':id', id),
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            $('#viewModalBody').html(response);
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            $('#viewModalBody').html(`
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle"></i> Error</h5>
                    Error al cargar los detalles del cliente.
                </div>
            `);
        }
    });
}

// Edit customer
function editCustomer(id) {
    resetForm();

    $.ajax({
        url: '{{ route("customers.edit", ":id") }}'.replace(':id', id),
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(customer) {
            $('#modalTitle').text('Editar Cliente');
            $('#customerId').val(customer.id);
            $('#methodField').val('PUT');

            // Fill form fields
            $('#name').val(customer.name);
            $('#customer_type').val(customer.customer_type);
            $('#contact_person').val(customer.contact_person);
            $('#phone').val(customer.phone);
            $('#email').val(customer.email);
            $('#credit_limit').val(customer.credit_limit);
            $('#address').val(customer.address);
            $('#city').val(customer.city);
            $('#state').val(customer.state);
            $('#country').val(customer.country);
            $('#postal_code').val(customer.postal_code);
            $('#status').val(customer.status);
            $('#notes').val(customer.notes);

            $('#customerModal').modal('show');
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            Swal.fire('Error', 'No se pudo cargar el cliente', 'error');
        }
    });
}

// Save customer (create or update)
function saveCustomer() {
    const formData = new FormData($('#customerForm')[0]);
    const customerId = $('#customerId').val();
    const method = customerId ? 'PUT' : 'POST';
    const url = customerId ? '{{ route("customers.update", ":id") }}'.replace(':id', customerId) : '{{ route("customers.store") }}';

    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    // Show loading
    $('#saveBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    // Add method override for PUT requests
    if (customerId) {
        formData.append('_method', 'PUT');
    }

    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#customerModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                customersTable.ajax.reload();
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                // Validation errors
                const errors = xhr.responseJSON.errors;
                for (let field in errors) {
                    $(`#${field}`).addClass('is-invalid');
                    $(`#${field}-error`).text(errors[field][0]);
                }
            } else {
                Swal.fire('Error', 'Ocurrió un error al guardar el cliente', 'error');
            }
        },
        complete: function() {
            $('#saveBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
        }
    });
}

// Delete customer
function deleteCustomer(id, name) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas eliminar al cliente "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("customers.destroy", ":id") }}'.replace(':id', id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        customersTable.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    Swal.fire('Error', 'No se pudo eliminar el cliente', 'error');
                }
            });
        }
    });
}

// Reset form
function resetForm() {
    $('#customerForm')[0].reset();
    $('#customerId').val('');
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}

// Adjust credit limit function
function adjustCreditLimit(customerId) {
    Swal.fire({
        title: 'Ajustar Límite de Crédito',
        html: `
            <div class="text-left">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Atención:</strong> Este cliente ha excedido su límite de crédito.
                </div>
                <label for="newCreditLimit">Nuevo límite de crédito:</label>
                <input type="number" id="newCreditLimit" class="form-control" step="0.01" min="0"
                       placeholder="Ingrese el nuevo límite">
                <small class="text-muted mt-2 d-block">
                    Establezca un límite que cubra el saldo actual para resolver el exceso de crédito.
                </small>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Actualizar Límite',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            const newLimit = document.getElementById('newCreditLimit').value;
            if (!newLimit || parseFloat(newLimit) < 0) {
                Swal.showValidationMessage('Ingrese un límite válido mayor a 0');
                return false;
            }
            return parseFloat(newLimit);
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("customers.update", ":id") }}'.replace(':id', customerId),
                method: 'POST',
                data: {
                    _method: 'PUT',
                    credit_limit: result.value,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Límite Actualizado!',
                            text: `El nuevo límite de crédito es $${result.value.toFixed(2)}`,
                            timer: 3000,
                            showConfirmButton: false
                        });
                        customersTable.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message || 'Error al actualizar el límite', 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    Swal.fire('Error', 'No se pudo actualizar el límite de crédito', 'error');
                }
            });
        }
    });
}
</script>
@endpush
