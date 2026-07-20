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
                    <li class="nav-item"><a class="nav-link {{ $activeTab == 'overall' ? 'active' : '' }}" data-toggle="tab" href="#overall">Overall</a></li>
                    <li class="nav-item"><a class="nav-link {{ $activeTab == 'location' ? 'active' : '' }}" data-toggle="tab" href="#location">By Location</a></li>
                    <li class="nav-item"><a class="nav-link {{ $activeTab == 'batch' ? 'active' : '' }}" data-toggle="tab" href="#batch">By Batch</a></li>
                </ul>

                <div class="tab-content mt-3">
                    <form method="GET" action="{{ route('reports.stock-balance') }}" class="mb-3">
                        <input type="hidden" name="active_tab" value="{{ $activeTab }}">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="product_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Products</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" {{ $productId == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="location_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">All Locations</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" {{ $locationId == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('reports.stock-balance') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="tab-pane fade {{ $activeTab == 'overall' ? 'show active' : '' }}" id="overall">
                        <table id="overall_table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Total Quantity (units)</th>
                                    @if($overallBalances->contains(fn($item) => $item->packaging_type === 'pack'))
                                    <th>Total Quantity (pack)</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($overallBalances as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                        <td>{{ $item->quantity_units }}</td>
                                        @if($item->packaging_type === 'pack')
                                        <td>{{ $item->quantity_pack_display }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade {{ $activeTab == 'location' ? 'show active' : '' }}" id="location">
                        <table id="location_table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Location</th>
                                    <th>Quantity (units)</th>
                                    @if($locationBalances->contains(fn($item) => $item->packaging_type === 'pack'))
                                    <th>Quantity (pack)</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($locationBalances as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                        <td>{{ $item->location->name ?? 'N/A' }}</td>
                                        <td>{{ $item->quantity_units }}</td>
                                        @if($item->packaging_type === 'pack')
                                        <td>{{ $item->quantity_pack_display }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade {{ $activeTab == 'batch' ? 'show active' : '' }}" id="batch">
                        <table id="batch_table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Location</th>
                                    <th>Lot #</th>
                                    <th>Expiry</th>
                                    <th>Quantity (units)</th>
                                    @if($batchBalances->contains(fn($item) => $item->packaging_type === 'pack'))
                                    <th>Quantity (pack)</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($batchBalances as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                        <td>{{ $item->location->name ?? 'N/A' }}</td>
                                        <td>{{ $item->lot_number ?? 'N/A' }}</td>
                                        <td>{{ $item->expiry_date ?? 'N/A' }}</td>
                                        <td>{{ $item->quantity_units }}</td>
                                        @if($item->packaging_type === 'pack')
                                        <td>{{ $item->quantity_pack_display }}</td>
                                        @endif
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
    ['#overall_table', '#location_table', '#batch_table'].forEach(function(id) {
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