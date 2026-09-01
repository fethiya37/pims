@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="card-title">Zero Stock Report</h3>
                        <small class="text-muted d-block">Products with no available stock</small>
                    </div>
                    <div class="col-md-6">
                        <span class="float-right">
                            <span class="badge badge-danger badge-lg">{{ $totalZeroStock }}</span>
                            <span class="text-muted">products out of stock</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('reports.zero-stock') }}" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Location</label>
                                <select name="location_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Locations</option>
                                    @foreach ($allowedLocations as $location)
                                        <option value="{{ $location->id }}" {{ $locationId == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <label>&nbsp;</label>
                                <a href="{{ route('reports.zero-stock') }}" class="btn btn-secondary btn-block">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $totalZeroStock }}</h3>
                                <p>Products Out of Stock</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $totalProducts }}</h3>
                                <p>Total Products</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-cubes"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $totalLocations }}</h3>
                                <p>Locations Checked</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($zeroStockItems->isEmpty())
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> All products have stock available at all locations.
                    </div>
                @else
                    <div class="table-responsive">
                        <table id="zero_stock_table" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Location</th>
                                    <th>Current Stock</th>
                                    <th>Packaging Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($zeroStockItems as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->product->name }}</strong>
                                            @if ($item->product->item_code)
                                                <br>
                                                <small class="text-muted">Code: {{ $item->product->item_code }}</small>
                                            @endif
                                        </td>
                                        <td>{{ optional($item->product->category)->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-secondary">{{ optional($item->location)->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-danger badge-lg">{{ $item->current_stock_units }}</span>
                                        </td>
                                        <td>
                                            @if ($item->packaging_type === 'pack')
                                                <span class="badge badge-warning">Pack</span>
                                                <small class="d-block">Pack Size: {{ $item->product->default_pack_size ?? 1 }}</small>
                                            @else
                                                <span class="badge badge-secondary">Unit</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times-circle"></i> Out of Stock
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function() {
    $('.select2').select2();
    $('#zero_stock_table').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        pageLength: 20,
        buttons: ["csv", "excel", "pdf", "print"],
        order: [[0, 'asc']]
    }).buttons().container().appendTo('#zero_stock_table_wrapper .col-md-6:eq(0)');
});
</script>
@endpush