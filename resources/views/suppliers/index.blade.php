@extends('layouts.admin')

@section('title', 'Proveedores')
@section('page-title', 'Proveedores')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Proveedores</li>
@endsection

@section('content')
    <!-- Estadísticas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="totalSuppliers">0</h3>
                    <p>Total Proveedores</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activeSuppliers">0</h3>
                    <p>Proveedores Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="totalBalance">$0</h3>
                    <p>Saldo Pendiente</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="inactiveSuppliers">0</h3>
                    <p>Proveedores Inactivos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Proveedores -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Lista de Proveedores</h3>
                    <div class="card-tools">
                        <button type="button" id="newSupplierBtn" class="btn btn-success">
                            <i class="fas fa-plus"></i> Nuevo Proveedor
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="suppliersTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Ciudad</th>
                                <th>Estado</th>
                                <th>Saldo Pendiente</th>
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

<!-- Modal para Crear/Editar Proveedor -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="supplierForm">
                @csrf
                <input type="hidden" id="supplierId" name="supplier_id">
                <input type="hidden" id="methodField" name="_method" value="POST">
                
                <div class="modal-header bg-primary">
                    <h4 class="modal-title" id="modalTitle">Nuevo Proveedor</h4>
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
                                <label>Persona de Contacto</label>
                                <input type="text" name="contact_person" id="contact_person" class="form-control">
                                <div class="invalid-feedback" id="contact_person-error"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="phone" id="phone" class="form-control">
                                <div class="invalid-feedback" id="phone-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" id="email" class="form-control">
                                <div class="invalid-feedback" id="email-error"></div>
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
                <h4 class="modal-title">Detalles del Proveedor</h4>
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
let suppliersTable;

$(document).ready(function() {
    // Initialize DataTable
    suppliersTable = $('#suppliersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("suppliers.index") }}',
            data: function(d) {
                d.ajax = 1;
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'contact_person', name: 'contact_person' },
            { data: 'phone', name: 'phone' },
            { data: 'email', name: 'email' },
            { data: 'city', name: 'city' },
            { data: 'status', name: 'status', orderable: false },
            { data: 'balance_owed', name: 'balance_owed', orderable: false },
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
    
    // New Supplier button
    $('#newSupplierBtn').click(function() {
        resetForm();
        $('#modalTitle').text('Nuevo Proveedor');
        $('#methodField').val('POST');
        $('#supplierModal').modal('show');
    });
    
    // Submit form
    $('#supplierForm').submit(function(e) {
        e.preventDefault();
        saveSupplier();
    });
});

// Load statistics
function loadStatistics() {
    $.ajax({
        url: '{{ route("suppliers.index") }}',
        method: 'GET',
        data: { ajax: 1, stats_only: 1 },
        success: function(response) {
            if (response.stats) {
                $('#totalSuppliers').text(response.stats.total);
                $('#activeSuppliers').text(response.stats.active);
                $('#totalBalance').text('$' + response.stats.balance);
                $('#inactiveSuppliers').text(response.stats.inactive);
            }
        }
    });
}

// View supplier details
function viewSupplier(id) {
    $('#viewModalBody').html(`
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando...</p>
        </div>
    `);
    
    $('#viewModal').modal('show');
    
    $.ajax({
        url: '{{ route("suppliers.show", ":id") }}'.replace(':id', id),
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
                    Error al cargar los detalles del proveedor.
                </div>
            `);
        }
    });
}

// Edit supplier
function editSupplier(id) {
    resetForm();
    
    $.ajax({
        url: '{{ route("suppliers.edit", ":id") }}'.replace(':id', id),
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(supplier) {
            $('#modalTitle').text('Editar Proveedor');
            $('#supplierId').val(supplier.id);
            $('#methodField').val('PUT');
            
            // Fill form fields
            $('#name').val(supplier.name);
            $('#contact_person').val(supplier.contact_person);
            $('#phone').val(supplier.phone);
            $('#email').val(supplier.email);
            $('#address').val(supplier.address);
            $('#city').val(supplier.city);
            $('#state').val(supplier.state);
            $('#country').val(supplier.country);
            $('#postal_code').val(supplier.postal_code);
            $('#status').val(supplier.status);
            $('#notes').val(supplier.notes);
            
            $('#supplierModal').modal('show');
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            Swal.fire('Error', 'No se pudo cargar el proveedor', 'error');
        }
    });
}

// Save supplier (create or update)
function saveSupplier() {
    const formData = new FormData($('#supplierForm')[0]);
    const supplierId = $('#supplierId').val();
    const method = supplierId ? 'PUT' : 'POST';
    const url = supplierId ? '{{ route("suppliers.update", ":id") }}'.replace(':id', supplierId) : '{{ route("suppliers.store") }}';
    
    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    // Show loading
    $('#saveBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    // Add method override for PUT requests
    if (supplierId) {
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
                $('#supplierModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                suppliersTable.ajax.reload();
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
                Swal.fire('Error', 'Ocurrió un error al guardar el proveedor', 'error');
            }
        },
        complete: function() {
            $('#saveBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
        }
    });
}

// Delete supplier
function deleteSupplier(id, name) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas eliminar al proveedor "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("suppliers.destroy", ":id") }}'.replace(':id', id),
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
                        suppliersTable.ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    Swal.fire('Error', 'No se pudo eliminar el proveedor', 'error');
                }
            });
        }
    });
}

// Reset form
function resetForm() {
    $('#supplierForm')[0].reset();
    $('#supplierId').val('');
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}
</script>
@endpush