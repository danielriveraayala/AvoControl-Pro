<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

    <!-- AdminLTE 3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Additional UX Libraries CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    
    <!-- Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

    <!-- Custom CSS for improved UX -->
    <style>
        /* Loading state */
        body.loading {
            cursor: wait;
        }

        body.loading * {
            pointer-events: none;
        }

        /* Smooth transitions */
        .card, .info-box, .small-box {
            transition: all 0.3s ease;
        }

        .card:hover, .info-box:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* Loading spinner overlay */
        .ajax-loading {
            position: relative;
        }

        .ajax-loading::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 1000;
        }

        .ajax-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 32px;
            height: 32px;
            margin: -16px 0 0 -16px;
            border: 3px solid #007bff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
            z-index: 1001;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Improved table styles */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.075);
        }

        /* Status badges */
        .badge {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }

        /* Quick action buttons */
        .quick-actions {
            white-space: nowrap;
        }

        .quick-actions .btn {
            margin: 0 2px;
            padding: 0.25rem 0.5rem;
        }

        /* Select2 integration with AdminLTE */
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px);
            border-color: #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(2.25rem + 2px);
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        /* Modal improvements */
        .modal-xl {
            max-width: 90%;
        }

        /* Progress bars */
        .progress-sm {
            height: 0.625rem;
        }

        /* Custom scrollbar */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
            }

            .btn-group-sm .btn {
                padding: 0.125rem 0.25rem;
                font-size: 0.75rem;
            }

            .card-tools .btn-group {
                flex-direction: column;
            }

            .small-box .inner h3 {
                font-size: 1.5rem;
            }

            .info-box-number {
                font-size: 1.2rem;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .select2-container--default .select2-selection--single {
                background-color: #343a40;
                border-color: #6c757d;
                color: #fff;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center" style="background-color: rgba(3,8,3,0.55)">
        <img class="animation__shake" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🥑%3C/text%3E%3C/svg%3E" alt="AvoControl" height="60" width="60">
    </div>

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- User Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i>
                    <span class="badge badge-warning navbar-badge"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">{{ Auth::user()->name }}</span>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('profile.index') }}" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Perfil
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}" class="dropdown-item">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 text-dark" style="text-decoration: none;">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light">🥑 <strong>AvoControl Pro</strong></span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <!-- Gestión de Lotes -->
                    <li class="nav-item">
                        <a href="{{ route('lots.index') }}" class="nav-link {{ request()->routeIs('lots.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>Gestión de Lotes</p>
                        </a>
                    </li>

                    <!-- Acopio -->
                    <li class="nav-item {{ request()->routeIs('acopio.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('acopio.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-layer-group"></i>
                            <p>
                                Acopio
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('acopio.index') }}" class="nav-link {{ request()->routeIs('acopio.index') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Inventario por Calidad</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('acopio.reporte') }}" class="nav-link {{ request()->routeIs('acopio.reporte') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Reportes y Análisis de Acopio</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Ventas -->
                    <li class="nav-item">
                        <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Ventas</p>
                        </a>
                    </li>

                    <!-- Clientes -->
                    <li class="nav-item">
                        <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Clientes</p>
                        </a>
                    </li>

                    <!-- Proveedores -->
                    <li class="nav-item">
                        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-truck"></i>
                            <p>Proveedores</p>
                        </a>
                    </li>

                    <!-- Reportes -->
                    <li class="nav-item {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>
                                Reportes
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('reports.profitability') }}" class="nav-link {{ request()->routeIs('reports.profitability') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-success"></i>
                                    <p>Rentabilidad</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reports.customer-analysis') }}" class="nav-link {{ request()->routeIs('reports.customer-analysis') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-info"></i>
                                    <p>Análisis de Clientes</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('reports.supplier-analysis') }}" class="nav-link {{ request()->routeIs('reports.supplier-analysis') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon text-warning"></i>
                                    <p>Análisis de Proveedores</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Configuración -->
                    <li class="nav-item">
                        <a href="{{ route('configuration.index') }}" class="nav-link {{ request()->routeIs('configuration.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>Configuración</p>
                        </a>
                    </li>

                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; {{ date('Y') }} <a href="#">AvoControl Pro</a>.</strong>
        Todos los derechos reservados.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
    </aside>

</div>

<!-- AdminLTE 3 Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- Additional Libraries for UX -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

<!-- DataTables JavaScript -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>

<!-- Date Range Picker -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<!-- Push Notifications -->
<script src="{{ asset('js/push-notifications.js') }}"></script>

<!-- Global AJAX Setup -->
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Global loading state
$(document).ajaxStart(function() {
    $('body').addClass('loading');
}).ajaxStop(function() {
    $('body').removeClass('loading');
});

// Global AJAX error handler
$(document).ajaxError(function(event, xhr, settings) {
    if (xhr.status === 419) {
        Swal.fire({
            title: 'Sesión Expirada',
            text: 'Su sesión ha expirado. La página se recargará.',
            icon: 'warning',
            confirmButtonText: 'Recargar'
        }).then(() => {
            window.location.reload();
        });
    } else if (xhr.status === 500) {
        toastr.error('Error interno del servidor. Contacte al administrador.');
    }
});

// Configure toastr
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};
</script>

@stack('scripts')
</body>
</html>
