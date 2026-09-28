@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3>Stock Balance Report</h3>
                <small>{{ \Carbon\Carbon::now()->toFormattedDateString() }}</small>
            </div>

            <div class="card-body">
                <ul class="nav nav-tabs" id="stockTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'overall' ? 'active' : '' }}"
                           href="{{ route('reports.stock-balance.overall') }}">Overall</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'location' ? 'active' : '' }}"
                           href="{{ route('reports.stock-balance.location') }}">By Location</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab == 'batch' ? 'active' : '' }}"
                           href="{{ route('reports.stock-balance.batch') }}">By Batch</a>
                    </li>
                </ul>

                <div class="mt-3">
                    <form method="GET" action="{{ route('reports.stock-balance.' . $activeTab) }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="product_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Products</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" {{ (isset($productId) && $productId == $product->id) ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="location_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Locations</option>
                                    @foreach ($allowedLocations as $location)
                                        <option value="{{ $location->id }}" {{ (isset($locationId) && $locationId == $location->id) ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('reports.stock-balance.' . $activeTab) }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    @if($activeTab == 'overall')
                        <div class="table-responsive">
                            <table id="overall_table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Product</th>
                                        <th>Total Quantity (units)</th>
                                        <th class="pack-col">Total Quantity (pack)</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    @elseif($activeTab == 'location')
                        <div class="table-responsive">
                            <table id="location_table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Product</th>
                                        <th>Location</th>
                                        <th>Quantity (units)</th>
                                        <th class="pack-col">Quantity (pack)</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    @elseif($activeTab == 'batch')
                        <div class="table-responsive">
                            <table id="batch_table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Product</th>
                                        <th>Location</th>
                                        <th>Lot #</th>
                                        <th>Expiry</th>
                                        <th>Quantity (units)</th>
                                        <th class="pack-col">Quantity (pack)</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    @endif
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

    var activeTab = '{{ $activeTab }}';
    var filters = {
        product_id:  '{{ request('product_id') }}',
        location_id: '{{ request('location_id') }}'
    };

    var domLayout = "<'row mb-2'<'col-md-6'B><'col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>";

    function hasPackColumn(api) {
        var any = false;
        api.rows({ search: 'applied' }).every(function () {
            if (this.data().has_pack) { any = true; return false; }
        });
        return any;
    }

    if (activeTab === 'overall') {
        $('#overall_table').DataTable({
            processing: true, serverSide: true,
            dom: domLayout,
            buttons: ["csv", "excel", "pdf", "print"],
            ajax: { url: '{{ route('reports.stock-balance.data') }}', data: $.extend({ tab: 'overall' }, filters) },
            columns: [
                { data: 'no', orderable: false, searchable: false },
                { data: 'product' },
                { data: 'quantity_units' },
                { data: 'quantity_pack' },
                { data: 'last_updated' }
            ],
            order: [[4, 'desc']],
            pageLength: 25,
            drawCallback: function () {
                $('.pack-col').toggle(hasPackColumn(this.api()));
            }
        });
    } else if (activeTab === 'location') {
        $('#location_table').DataTable({
            processing: true, serverSide: true,
            dom: domLayout,
            buttons: ["csv", "excel", "pdf", "print"],
            ajax: { url: '{{ route('reports.stock-balance.data') }}', data: $.extend({ tab: 'location' }, filters) },
            columns: [
                { data: 'no', orderable: false, searchable: false },
                { data: 'product' },
                { data: 'location' },
                { data: 'quantity_units' },
                { data: 'quantity_pack' },
                { data: 'last_updated' }
            ],
            order: [[5, 'desc']],
            pageLength: 25,
            drawCallback: function () {
                $('.pack-col').toggle(hasPackColumn(this.api()));
            }
        });
    } else {
        $('#batch_table').DataTable({
            processing: true, serverSide: true,
            dom: domLayout,
            buttons: ["csv", "excel", "pdf", "print"],
            ajax: { url: '{{ route('reports.stock-balance.data') }}', data: $.extend({ tab: 'batch' }, filters) },
            columns: [
                { data: 'no', orderable: false, searchable: false },
                { data: 'product' },
                { data: 'location' },
                { data: 'lot_number' },
                { data: 'expiry' },
                { data: 'quantity_units' },
                { data: 'quantity_pack' },
                { data: 'last_updated' }
            ],
            order: [[7, 'desc']],
            pageLength: 25,
            drawCallback: function () {
                $('.pack-col').toggle(hasPackColumn(this.api()));
            }
        });
    }
});
</script>
@endpush