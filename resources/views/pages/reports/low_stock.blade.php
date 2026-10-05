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
                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="location_id" class="form-control select2" onchange="this.form.submit()">
                                <option value="">All Locations</option>
                                @foreach ($allowedLocations as $location)
                                    <option value="{{ $location->id }}" {{ $locationId == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2"><a href="{{ route('reports.low-stock') }}" class="btn btn-secondary">Reset</a></div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="low_stock_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Product</th>
                                <th>Location</th>
                                <th>Current Stock (units)</th>
                                <th class="pack-col">Current Stock (pack)</th>
                                <th>Reorder Level</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function () {
    $('.select2').select2();
    $('#low_stock_table').DataTable({
        processing: true, serverSide: true,
        dom: "<'row mb-2'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: ["csv", "excel", "pdf", "print"],
        ajax: {
            url: '{{ route('reports.low-stock.data') }}',
            data: {
                product_id: '{{ request('product_id') }}',
                location_id: '{{ $locationId }}'
            }
        },
        columns: [
            { data: 'no', orderable: false, searchable: false },
            { data: 'product' },
            { data: 'location' },
            { data: 'current_stock_units' },
            { data: 'current_stock_pack' },
            { data: 'reorder_display' },
            { data: 'status' }
        ],
        order: [[0, 'asc']],
        pageLength: 100,
        drawCallback: function () {
            var any = false;
            this.api().rows({ search: 'applied' }).every(function () {
                if (this.data().has_pack) { any = true; return false; }
            });
            $('.pack-col').toggle(any);
        }
    });
});
</script>
@endpush