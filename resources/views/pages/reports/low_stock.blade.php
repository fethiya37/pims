@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Low Stock Report</h3>
                <small class="text-muted">Products where current stock ≤ reorder level</small>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('reports.low-stock') }}" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <select name="product_id" class="form-control select2" onchange="this.form.submit()">
                                <option value="">All Products</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
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
                            <a href="{{ route('reports.low-stock') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                @if ($lowStockItems->isEmpty())
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> All products are adequately stocked.
                    </div>
                @else
                    <div class="table-responsive">
                        <table id="low_stock_table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Location</th>
                                    <th>Current Stock (units)</th>
                                    @if($lowStockItems->contains(fn($item) => $item->packaging_type === 'pack'))
                                    <th>Current Stock (pack)</th>
                                    @endif
                                    <th>Reorder Level</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lowStockItems as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->location->name }}</td>
                                        <td>{{ $item->current_stock_units }}</td>
                                        @if($lowStockItems->contains(fn($i) => $i->packaging_type === 'pack'))
                                        <td>{{ $item->packaging_type === 'pack' ? $item->current_stock_pack_display : '-' }}</td>
                                        @endif
                                        <td>{{ $item->reorder_display }}</td>
                                        <td>
                                            <span class="badge badge-{{ $item->status == 'Out of Stock' ? 'danger' : 'warning' }}">
                                                {{ $item->status }}
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
    $('#low_stock_table').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        pageLength: 20,
        buttons: ["csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#low_stock_table_wrapper .col-md-6:eq(0)');
});
</script>
@endpush