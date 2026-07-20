@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3>Product Sales Report</h3>
                <small>From {{ $fromDate }} to {{ $toDate }}</small>
            </div>

            <div class="card-body">
                <ul class="nav nav-tabs" id="salesTab" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#details">Details</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#summary">Summary</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#top">Top Products</a></li>
                </ul>

                <div class="tab-content mt-3">
                    <form method="GET" action="{{ route('reports.sales-report') }}" class="mb-3">
                        <input type="hidden" name="active_tab" value="{{ $activeTab ?? 'details' }}">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="product_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Products</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="location_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Locations</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="to_date" value="{{ $toDate }}" class="form-control" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('reports.sales-report') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="tab-pane fade show active" id="details">
                        <table id="detail_table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Invoice</th>
                                    <th>Location</th>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Line Total</th>
                                    <th>VAT</th>
                                    <th>Total</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sales as $sale)
                                    @foreach ($sale->items as $item)
                                        <tr>
                                            <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                                            <td>SALE-#{{ $sale->id }}</td>
                                            <td>{{ $sale->invoice_no ?? 'N/A' }}</td>
                                            <td>{{ $sale->location->name ?? 'N/A' }}</td>
                                            <td>{{ $item->product->name ?? 'N/A' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ number_format($item->unit_price, 2) }}</td>
                                            <td>{{ number_format($item->line_total, 2) }}</td>
                                            <td>{{ number_format($item->total_tax, 2) }}</td>
                                            <td>{{ number_format($item->line_total + $item->total_tax, 2) }}</td>
                                            <td>{{ $sale->user->name ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="summary">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-shopping-cart"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Sales</span>
                                        <span class="info-box-number">{{ $summary->total_sales ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-money-bill"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Revenue</span>
                                        <span class="info-box-number">{{ number_format($summary->total_revenue ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-receipt"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total VAT</span>
                                        <span class="info-box-number">{{ number_format($summary->total_tax ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-chart-line"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Avg Sale Value</span>
                                        <span class="info-box-number">{{ number_format($summary->average_sale_value ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="top">
                        <table id="top_table" class="table table-bordered table-striped">
                            <thead>
                                <tr><th>#</th><th>Product</th><th>Total Quantity</th><th>Total Revenue</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($topProducts as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                        <td>{{ number_format($item->total_quantity ?? 0) }}</td>
                                        <td>{{ number_format($item->total_revenue ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function() {
    $('.select2').select2();
    ['#detail_table', '#top_table'].forEach(function(id) {
        const dt = $(id).DataTable({
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            pageLength: 20,
            buttons: ["csv","excel","pdf","print"]
        });
        dt.buttons().container().appendTo(`${id}_wrapper .col-md-6:eq(0)`);
    });
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        $('input[name="active_tab"]').val($(e.target).attr('href').replace('#', ''));
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });
});
</script>
@endpush