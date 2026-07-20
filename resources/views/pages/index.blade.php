@extends('inc.frame')
@section('content')
<section class="content">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Dashboard</h2>
            <a href="{{ route('dashboard.report') }}" class="btn btn-primary">
                <i class="fas fa-download mr-1"></i> Generate Report
            </a>
        </div>

        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold">Products</div>
                            <div class="h3 mb-0 font-weight-bold" id="kpi-products">0</div>
                        </div>
                        <div class="rounded-circle bg-primary-soft p-3">
                            <i class="fas fa-boxes text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold">Categories</div>
                            <div class="h3 mb-0 font-weight-bold" id="kpi-categories">0</div>
                        </div>
                        <div class="rounded-circle bg-success-soft p-3">
                            <i class="fas fa-tags text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold">Locations</div>
                            <div class="h3 mb-0 font-weight-bold" id="kpi-locations">0</div>
                        </div>
                        <div class="rounded-circle bg-info-soft p-3">
                            <i class="fas fa-map-marker-alt text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold">Users</div>
                            <div class="h3 mb-0 font-weight-bold" id="kpi-users">0</div>
                        </div>
                        <div class="rounded-circle bg-warning-soft p-3">
                            <i class="fas fa-users text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold">Patients</div>
                            <div class="h3 mb-0 font-weight-bold" id="kpi-patients">0</div>
                        </div>
                        <div class="rounded-circle bg-danger-soft p-3">
                            <i class="fas fa-user-injured text-danger fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold">Treatments</div>
                            <div class="h3 mb-0 font-weight-bold" id="kpi-treatments">0</div>
                        </div>
                        <div class="rounded-circle bg-purple-soft p-3">
                            <i class="fas fa-syringe text-purple fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold">Sales</div>
                            <div class="h3 mb-0 font-weight-bold" id="kpi-sales">0</div>
                        </div>
                        <div class="rounded-circle bg-teal-soft p-3">
                            <i class="fas fa-shopping-cart text-teal fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold">Low Stock</div>
                            <div class="h3 mb-0 font-weight-bold" id="kpi-low-stock">0</div>
                        </div>
                        <div class="rounded-circle bg-orange-soft p-3">
                            <i class="fas fa-exclamation-triangle text-orange fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold">Expired Stock</div>
                            <div class="h3 mb-0 font-weight-bold" id="kpi-expired">0</div>
                        </div>
                        <div class="rounded-circle bg-red-soft p-3">
                            <i class="fas fa-times-circle text-red fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold">Expiring Soon</div>
                            <div class="h3 mb-0 font-weight-bold" id="kpi-expiring">0</div>
                        </div>
                        <div class="rounded-circle bg-yellow-soft p-3">
                            <i class="fas fa-clock text-yellow fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold">Total Batches</div>
                            <div class="h3 mb-0 font-weight-bold" id="kpi-batches">0</div>
                        </div>
                        <div class="rounded-circle bg-blue-soft p-3">
                            <i class="fas fa-layer-group text-blue fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold">Active Products</div>
                            <div class="h3 mb-0 font-weight-bold" id="kpi-active-products">0</div>
                        </div>
                        <div class="rounded-circle bg-green-soft p-3">
                            <i class="fas fa-check-circle text-green fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
    .dashboard-card {
        border: none;
        border-radius: 0.75rem;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        background: #ffffff;
    }

    .dashboard-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
    }

    .dashboard-card .card-body {
        padding: 1.25rem 1.5rem;
    }

    .dashboard-card .text-muted {
        font-size: 0.7rem;
        letter-spacing: 0.5px;
    }

    .dashboard-card .h3 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a1a2e;
    }

    .rounded-circle {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .rounded-circle i {
        font-size: 1.5rem;
    }

    .bg-primary-soft { background: #e8f0fe; }
    .bg-success-soft { background: #e6f9ed; }
    .bg-info-soft    { background: #e3f6fc; }
    .bg-warning-soft { background: #fef3e2; }
    .bg-danger-soft  { background: #fde8e8; }
    .bg-purple-soft  { background: #f0ebff; }
    .bg-teal-soft    { background: #e0f7f1; }
    .bg-orange-soft  { background: #ffeed9; }
    .bg-red-soft     { background: #ffe5e5; }
    .bg-yellow-soft  { background: #fff8e1; }
    .bg-blue-soft    { background: #e3ecfa; }
    .bg-green-soft   { background: #e6f7e6; }

    .text-primary { color: #4e73df !important; }
    .text-success { color: #1cc88a !important; }
    .text-info    { color: #36b9cc !important; }
    .text-warning { color: #f6c23e !important; }
    .text-danger  { color: #e74a3b !important; }
    .text-purple  { color: #6f42c1 !important; }
    .text-teal    { color: #20c997 !important; }
    .text-orange  { color: #fd7e14 !important; }
    .text-red     { color: #dc3545 !important; }
    .text-yellow  { color: #ffc107 !important; }
    .text-blue    { color: #007bff !important; }
    .text-green   { color: #28a745 !important; }

    .fa-2x { font-size: 1.75rem; }
</style>

<script>
    const intFmt = new Intl.NumberFormat('en-ET', { maximumFractionDigits: 0 });

    $(function () {
        $.ajax({
            url: "{{ route('dashboard.data') }}",
            method: "GET",
            dataType: "json",
        })
        .done(function (res) {
            if (res.ok === false) {
                console.error('Dashboard error:', res.error);
                return;
            }

            const counts = res.counts || {};
            $('#kpi-products').text(intFmt.format(counts.products ?? 0));
            $('#kpi-categories').text(intFmt.format(counts.categories ?? 0));
            $('#kpi-locations').text(intFmt.format(counts.locations ?? 0));
            $('#kpi-users').text(intFmt.format(counts.users ?? 0));
            $('#kpi-patients').text(intFmt.format(counts.patients ?? 0));
            $('#kpi-treatments').text(intFmt.format(counts.treatments ?? 0));
            $('#kpi-sales').text(intFmt.format(counts.sales ?? 0));
            $('#kpi-low-stock').text(intFmt.format(counts.low_stock ?? 0));
            $('#kpi-expired').text(intFmt.format(counts.expired ?? 0));
            $('#kpi-expiring').text(intFmt.format(counts.expiring_soon ?? 0));
            $('#kpi-batches').text(intFmt.format(counts.batches ?? 0));
            $('#kpi-active-products').text(intFmt.format(counts.active_products ?? 0));
        })
        .fail(function (xhr) {
            console.error('Failed to load dashboard data:', xhr.status);
        });
    });
</script>
@endsection