<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="theme-color" content="#6777ef" />
    <link rel="apple-touch-icon" href="{{ asset('dist/img/primelogo.jpg') }}">
    <link rel="manifest" href="{{ asset('/manifest.json') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prime Medicare PLC</title>

    <link rel="stylesheet"
        href="{{ asset('plugins/fonts/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback') }}">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1/dist/jquery.min.js"></script>
    <link rel="stylesheet" href="{{ asset('plugins/datatables/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/css/sweetalert2.min.css') }}">
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <link href="{{ asset('plugins/sweetalert2/css/sweetalert2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap/css/bootstrap.min.css') }}">
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <link href="{{ asset('plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('plugins/popper/popper.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .custom-badge {
            text-align: center;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 0.5rem;
        }

        .main-sidebar .nav-sidebar .nav-item .nav-link i.nav-icon {
            color: #007bff !important;
            opacity: 0.85;
            transition: all 0.2s ease-in-out;
        }

        .main-sidebar .nav-sidebar .nav-item .nav-link:hover i.nav-icon {
            color: #0056b3 !important;
            transform: scale(1.1);
            opacity: 1;
        }

        .main-sidebar .nav-sidebar .nav-item .nav-link.active i.nav-icon {
            color: #ffffff !important;
            background: #007bff;
            border-radius: 6px;
            padding: 6px;
        }

        .main-sidebar .nav-sidebar .nav-item .nav-link.active {
            background-color: #007bff !important;
            color: #fff !important;
            font-weight: 600;
            border-radius: 8px;
        }

        .main-sidebar .nav-sidebar .nav-item .nav-link:hover {
            background-color: rgba(0, 123, 255, 0.15) !important;
            border-radius: 8px;
            color: #0056b3 !important;
        }

        .main-sidebar .nav-header {
            color: #0d6efd;
            font-weight: bold;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }

        .main-sidebar .nav-sidebar .nav-item>.nav-link .right {
            color: #007bff;
            opacity: 0.7;
        }

        .main-sidebar .nav-sidebar .nav-item .nav-link {
            transition: all 0.2s ease-in-out;
        }

        .navbar-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 9px;
            padding: 2px 5px;
            border-radius: 50%;
            background-color: #dc3545;
            color: #fff;
            min-width: 18px;
            text-align: center;
        }

        #notificationDropdown {
            width: 380px;
            max-height: 420px;
            overflow-y: auto;
        }

        #notificationDropdown .dropdown-item {
            white-space: normal;
            word-wrap: break-word;
            padding: 10px 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        #notificationDropdown .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        #notificationDropdown .dropdown-divider {
            margin: 0;
        }

        .notification-badge {
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 12px;
        }

        .mark-read-btn {
            border: none;
            background: none;
            cursor: pointer;
            color: #28a745;
            font-size: 14px;
            padding: 0 5px;
        }

        .mark-read-btn:hover {
            color: #1e7e34;
            transform: scale(1.1);
        }
    </style>
    @livewireStyles
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed accent-info">
    <div class="wrapper">

        <nav class="main-header navbar navbar-expand navbar-light shadow-sm">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas text-primary fa-bars"></i>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">

                <li class="nav-item dropdown">
                    <a class="nav-link text-primary" data-toggle="dropdown" href="#">
                        <i class="fas text-primary fa-globe"></i> {{ strtoupper(app()->getLocale()) }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="{{ url('lang/en') }}" class="dropdown-item">🇺🇸 English</a>
                        <a href="{{ url('lang/am') }}" class="dropdown-item">🇪🇹 አማርኛ</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" id="notificationBell" title="Notifications">
                        <i class="fas fa-bell text-primary"></i>
                        <span class="navbar-badge" id="notificationCount" style="display:none;">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" id="notificationDropdown">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-bell mr-1"></i> Notifications</span>
                            <button class="btn btn-sm btn-outline-secondary" id="markAllReadBtn">Mark all read</button>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div id="notificationList">
                            <div class="dropdown-item text-center text-muted">Loading...</div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer text-center">View all notifications</a>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas text-primary fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                        <i class="fas text-primary fa-th-large"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <aside class="main-sidebar sidebar-light-primary elevation-4">
            <a href="/" class="brand-link text-center text-primary">
                <img src="{{ asset('dist/img/primelogo.jpg') }}" alt="AdminLTE Logo" class="brand-image"
                    style="width: 100px">
            </a>
            <div class="sidebar">
                <hr>
                @php
                    $permission = App\Models\Role::where('id', Auth::user()->role_id)->first();
                @endphp
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">

                        <li class="nav-item">
                            <a href="/" class="nav-link active">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p class="text-sm">{{ __('messages.dashboard') }}</p>
                            </a>
                        </li>

                        @if ($permission != null)
                            <!-- Management Menu -->
                            @if (
                                $permission->manage_user == 'on' ||
                                $permission->manage_locations == 'on' ||
                                $permission->manage_products == 'on' ||
                                $permission->manage_supplier == 'on' ||
                                $permission->manage_categories == 'on')
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="nav-icon fas fa-cog"></i>
                                        <p class="text-sm">
                                            {{ __('messages.management') }}
                                            <i class="fas fa-angle-left right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @if ($permission->manage_user == 'on')
                                            <li class="nav-item">
                                                <a href="{{ route('roles.index') }}" class="nav-link">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p class="text-sm">Roles</p>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="users" class="nav-link">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p class="text-sm">{{ __('messages.users') }}</p>
                                                </a>
                                            </li>
                                        @endif
                                        @if ($permission->manage_locations == 'on')
                                            <li class="nav-item">
                                                <a href="location" class="nav-link">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p class="text-sm">{{ __('messages.locations') }}</p>
                                                </a>
                                            </li>
                                        @endif
                                        @if ($permission->manage_products == 'on')
                                            <li class="nav-item">
                                                <a href="products" class="nav-link">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p class="text-sm">{{ __('messages.products') }}</p>
                                                </a>
                                            </li>
                                        @endif
                                        @if ($permission->manage_supplier == 'on')
                                            <li class="nav-item">
                                                <a href="suppliers" class="nav-link">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p class="text-sm">{{ __('messages.suppliers') }}</p>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                            <!-- Inventory Menu -->
                            @if (
                                $permission->manage_goods_receipt == 'on' ||
                                $permission->manage_inventory_transfer == 'on' ||
                                $permission->manage_inventory_adjustment == 'on')
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="nav-icon fas fa-boxes"></i>
                                        <p class="text-sm">
                                            Inventory
                                            <i class="fas fa-angle-left right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @if ($permission->manage_goods_receipt == 'on')
                                            <li class="nav-item">
                                                <a href="goods-receipts" class="nav-link">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p class="text-sm">Goods Receipt</p>
                                                </a>
                                            </li>
                                        @endif
                                        @if ($permission->manage_inventory_transfer == 'on')
                                            <li class="nav-item">
                                                <a href="inventory-transfers" class="nav-link">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p class="text-sm">Inventory Transfers</p>
                                                </a>
                                            </li>
                                        @endif
                                        @if ($permission->manage_inventory_adjustment == 'on')
                                            <li class="nav-item">
                                                <a href="{{ route('inventory-adjustments.index') }}" class="nav-link">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p class="text-sm">Inventory Adjustments</p>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                            <!-- Patients & Treatments Menu -->
                            @if (
                                $permission->manage_patients == 'on' ||
                                $permission->manage_treatment_consumption == 'on')
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="nav-icon fas fa-user-md"></i>
                                        <p class="text-sm">
                                            Patients & Treatments
                                            <i class="fas fa-angle-left right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @if ($permission->manage_patients == 'on')
                                            <li class="nav-item">
                                                <a href="patients" class="nav-link">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p class="text-sm">Patients</p>
                                                </a>
                                            </li>
                                        @endif
                                        @if ($permission->manage_treatment_consumption == 'on')
                                            <li class="nav-item">
                                                <a href="treatments" class="nav-link">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p class="text-sm">Treatment Consumption</p>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                            <!-- Sales Menu -->
                            @if ($permission->manage_product_sales == 'on')
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="nav-icon fas fa-cash-register"></i>
                                        <p class="text-sm">
                                            Sales
                                            <i class="fas fa-angle-left right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @if ($permission->manage_product_sales == 'on')
                                            <li class="nav-item">
                                                <a href="sales" class="nav-link">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p class="text-sm">Product Sales</p>
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            @endif

                            <!-- Reports Menu -->
                            @if ($permission->view_reports == 'on')
                                <li class="nav-header">{{ __('messages.reports') }}</li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="nav-icon fas fa-chart-bar"></i>
                                        <p class="text-sm">
                                            Reports
                                            <i class="fas fa-angle-left right"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ route('reports.stock-balance') }}" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-sm">Stock Balance</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.low-stock') }}" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-sm">Low Stock Report</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.expiry') }}" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-sm">Expiry Report</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.inter-location-transfer') }}" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-sm">Inter-Location Transfer</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.treatment-consumption') }}" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-sm">Treatment Consumption</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.sales-report') }}" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-sm">Sales Report</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('reports.transaction') }}" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p class="text-sm">Transaction Report</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif
                        @endif

                        <li class="nav-header"></li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon far fa-user"></i>
                                <p class="text-sm">
                                    {{ __('messages.my_account') }}
                                    <i class="fas fa-angle-left right"></i>
                                    <span class="badge badge-success text-white right">Auth</span>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                                    <div class="image">
                                        <img src="dist/img/avatar5.png" class="img-circle elevation-2"
                                            alt="User Image">
                                    </div>
                                    <div class="info">
                                        <a href="#" class="d-block">{{ Auth::user()->name }}</a>
                                    </div>
                                </div>
                                <li class="nav-item">
                                    <a class="nav-link" href="/profile">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ __('messages.edit_account') }}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <a class="nav-link" href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>{{ __('messages.logout') }}</p>
                                        </a>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            @include('inc.message')
            @yield('content')
        </div>

        <aside class="control-sidebar control-sidebar-dark"></aside>

        <footer class="main-footer">
            <strong>&copy; 2022-{{ \Carbon\Carbon::now()->format('Y') }} <a href="https://skylinkict.com">Prime Medicare PLC
                    </a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 2.0
            </div>
        </footer>
    </div>

    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.js') }}"></script>

    <script src="{{ asset('plugins/jquery-mapael/jquery.mapael.min.js') }}"></script>
    <script src="{{ asset('plugins/raphael/raphael.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery-mapael/jquery.mapael.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery-mapael/maps/usa_states.min.js') }}"></script>
    <script src="{{ asset('plugins/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/sweetalert2/js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('plugins/sweetalert2/js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>

    <script src="dist/js/demo.js"></script>
    <script src="dist/js/validation.js"></script>
    <script src="dist/js/alert.js"></script>
    <script src="dist/js/pages/dashboard2.js"></script>

    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    @livewireScripts
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')

    <script>
        $(function() {
            $('.select2').select2();
            $('.select2bs4').select2({ theme: 'bootstrap4' });

            $("#example1").DataTable({
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                pageLength: 50,
                buttons: ["csv", "excel", "pdf", "print"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

            $("#example3").DataTable({
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                pageLength: 50,
                buttons: ["csv", "excel", "pdf", "print"]
            }).buttons().container().appendTo('#example3_wrapper .col-md-6:eq(0)');

            $('#example2').DataTable({
                paging: true,
                lengthChange: false,
                searching: false,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true,
            });

            $("#warehouse_stock").DataTable({
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                pageLength: 20,
                buttons: ["csv", "excel", "pdf", "print"]
            }).buttons().container().appendTo('#warehouse_stock_wrapper .col-md-6:eq(0)');

            $("#w_stock_by_location").DataTable({
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                pageLength: 20,
                buttons: ["csv", "excel", "pdf", "print"]
            }).buttons().container().appendTo('#w_stock_by_location_wrapper .col-md-6:eq(0)');

            $("#w_stock_by_batch").DataTable({
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                pageLength: 20,
                buttons: ["csv", "excel", "pdf", "print"]
            }).buttons().container().appendTo('#w_stock_by_batch_wrapper .col-md-6:eq(0)');
        });
    </script>

    <script src="{{ asset('/sw.js') }}"></script>
    <script>
        if (!navigator.serviceWorker.controller) {
            navigator.serviceWorker.register("/sw.js").then(function(reg) {
                console.log("Service worker registered for scope: " + reg.scope);
            });
        }

        function fetchNotifications() {
            $.ajax({
                url: "{{ route('notifications.fetch') }}",
                method: "GET",
                dataType: "json",
                success: function(data) {
                    var count = data.count || 0;
                    if (count > 0) {
                        $('#notificationCount').text(count).show();
                    } else {
                        $('#notificationCount').hide();
                    }

                    var html = '';
                    if (data.notifications.length === 0) {
                        html = '<div class="dropdown-item text-center text-muted">No unread notifications</div>';
                    } else {
                        $.each(data.notifications, function(index, n) {
                            var badgeClass = n.type === 'expired' ? 'danger' :
                                (n.type === 'urgent' ? 'warning' :
                                (n.type === 'soon' ? 'info' :
                                (n.type === 'low_stock' ? 'primary' : 'secondary')));
                            var icon = n.type === 'expired' ? '❌' :
                                (n.type === 'urgent' ? '🔴' :
                                (n.type === 'soon' ? '🟠' :
                                (n.type === 'low_stock' ? '📦' : '🔵')));
                            var displayType = n.type === 'low_stock' ? 'LOW STOCK' : n.type.toUpperCase();

                            html += `
                                <a href="${n.link}" class="dropdown-item d-flex justify-content-between align-items-start" style="text-decoration:none; color:inherit;">
                                    <div>
                                        <span class="badge badge-${badgeClass} notification-badge">${displayType}</span>
                                        <span>${icon} ${n.message}</span>
                                        <br><small class="text-muted">${n.created_at}</small>
                                    </div>
                                    <button class="mark-read-btn" data-id="${n.id}" title="Mark as read" onclick="event.stopPropagation();">✔</button>
                                </a>
                                <div class="dropdown-divider"></div>
                            `;
                        });
                    }
                    $('#notificationList').html(html);
                },
                error: function(xhr) {
                    console.error('Failed to fetch notifications:', xhr.status);
                    $('#notificationList').html(
                        '<div class="dropdown-item text-center text-danger">Failed to load</div>');
                }
            });
        }

        $(document).on('click', '.mark-read-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(this).data('id');
            $.ajax({
                url: "/notifications/" + id + "/mark-read",
                method: "POST",
                data: { _token: "{{ csrf_token() }}" },
                success: function() {
                    fetchNotifications();
                },
                error: function(xhr) {
                    alert('Error marking notification as read.');
                }
            });
        });

        $('#markAllReadBtn').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('notifications.mark-all-read') }}",
                method: "POST",
                data: { _token: "{{ csrf_token() }}" },
                success: function() {
                    fetchNotifications();
                },
                error: function(xhr) {
                    alert('Error marking all as read.');
                }
            });
        });

        $(document).ready(function() {
            fetchNotifications();
            setInterval(fetchNotifications, 30000);
        });
    </script>

</body>
</html>