@extends('layouts.admin')

@section('title', 'Proveedores')
@section('page-title', 'Proveedores')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Proveedores</li>
@endsection

@section('content')
    <!-- Filtros y Búsqueda -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-search"></i> Filtros</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('suppliers.index') }}" class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Buscar</label>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nombre, contacto o email...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="status" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Ciudad</label>
                                <input type="text" name="city" value="{{ request('city') }}" class="form-control" placeholder="Ciudad...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-block">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-block">
                                    <a href="{{ route('suppliers.create') }}" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Nuevo Proveedor
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $suppliers->total() }}</h3>
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
                    <h3>{{ $suppliers->where('status', 'active')->count() }}</h3>
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
                    <h3>${{ number_format($totalBalance ?? 0, 2) }}</h3>
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
                    <h3>{{ $suppliers->where('status', 'inactive')->count() }}</h3>
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
                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
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
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td>{{ $supplier->id }}</td>
                                <td>
                                    <strong>{{ $supplier->name }}</strong>
                                    @if($supplier->notes)
                                        <br><small class="text-muted">{{ Str::limit($supplier->notes, 50) }}</small>
                                    @endif
                                </td>
                                <td>{{ $supplier->contact_person ?? '-' }}</td>
                                <td>
                                    @if($supplier->email)
                                        <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->phone)
                                        <a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $supplier->city ?? '-' }}</td>
                                <td>
                                    @if($supplier->status == 'active')
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->balance_owed > 0)
                                        <span class="badge badge-warning">${{ number_format($supplier->balance_owed, 2) }}</span>
                                    @else
                                        <span class="badge badge-success">$0.00</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" title="Eliminar" data-id="{{ $supplier->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No hay proveedores registrados</h5>
                                        <p class="text-muted">Comience agregando su primer proveedor</p>
                                        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Nuevo Proveedor
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($suppliers->hasPages())
                    <div class="card-footer clearfix">
                        <div class="float-right">
                            {{ $suppliers->withQueryString()->links() }}
                        </div>
                        <div class="float-left">
                            Mostrando {{ $suppliers->firstItem() }} a {{ $suppliers->lastItem() }} de {{ $suppliers->total() }} resultados
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Auto-collapse filter card after filter
            @if(request()->hasAny(['search', 'status', 'city']))
            $('.card[data-card-widget="collapse"]').CardWidget('collapse');
            @endif
        });
    </script>
@endpush
