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
                                    @foreach ($allowedLocations as $location)
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
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overallBalances as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                        <td>{{ $item->quantity_units }}</td>
                                        @if($item->packaging_type === 'pack')
                                        <td>{{ $item->quantity_pack_display }}</td>
                                        @endif
                                        <td>{{ isset($item->last_updated) ? \Carbon\Carbon::parse($item->last_updated)->format('Y-m-d H:i') : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $overallBalances->contains(fn($item) => $item->packaging_type === 'pack') ? 4 : 3 }}" class="text-center">No records found</td>
                                    </tr>
                                @endforelse
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
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($locationBalances as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                        <td>{{ $item->location->name ?? 'N/A' }}</td>
                                        <td>{{ $item->quantity_units }}</td>
                                        @if($item->packaging_type === 'pack')
                                        <td>{{ $item->quantity_pack_display }}</td>
                                        @endif
                                        <td>{{ isset($item->last_updated) ? \Carbon\Carbon::parse($item->last_updated)->format('Y-m-d H:i') : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $locationBalances->contains(fn($item) => $item->packaging_type === 'pack') ? 5 : 4 }}" class="text-center">No records found</td>
                                    </tr>
                                @endforelse
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
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($batchBalances as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                        <td>{{ $item->location->name ?? 'N/A' }}</td>
                                        <td>{{ $item->lot_number ?? 'N/A' }}</td>
                                        <td>{{ $item->expiry_date ?? 'N/A' }}</td>
                                        <td>{{ $item->quantity_units }}</td>
                                        @if($item->packaging_type === 'pack')
                                        <td>{{ $item->quantity_pack_display }}</td>
                                        @endif
                                        <td>{{ isset($item->updated_at) ? \Carbon\Carbon::parse($item->updated_at)->format('Y-m-d H:i') : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $batchBalances->contains(fn($item) => $item->packaging_type === 'pack') ? 7 : 6 }}" class="text-center">No records found</td>
                                    </tr>
                                @endforelse
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

    function initDataTable(tableId) {
        if ($.fn.dataTable.isDataTable(tableId)) {
            $(tableId).DataTable().destroy();
            $(tableId).empty();
        }
        
        return $(tableId).DataTable({
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            pageLength: 20,
            buttons: ["csv", "excel", "pdf", "print"]
        });
    }

    var overallTable = initDataTable('#overall_table');
    var locationTable = initDataTable('#location_table');
    var batchTable = initDataTable('#batch_table');

    // Append buttons to wrapper
    if (overallTable) {
        overallTable.buttons().container().appendTo('#overall_table_wrapper .col-md-6:eq(0)');
    }
    if (locationTable) {
        locationTable.buttons().container().appendTo('#location_table_wrapper .col-md-6:eq(0)');
    }
    if (batchTable) {
        batchTable.buttons().container().appendTo('#batch_table_wrapper .col-md-6:eq(0)');
    }

    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        $('input[name="active_tab"]').val($(e.target).attr('href').replace('#', ''));
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    // Handle tab switching properly
    $('a[data-toggle="tab"]').on('hide.bs.tab', function(e) {
        var target = $(e.target).attr('href');
        if (target === '#overall' && overallTable) {
            overallTable.destroy();
            overallTable = null;
        } else if (target === '#location' && locationTable) {
            locationTable.destroy();
            locationTable = null;
        } else if (target === '#batch' && batchTable) {
            batchTable.destroy();
            batchTable = null;
        }
    });

    $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
        var target = $(e.target).attr('href');
        setTimeout(function() {
            if (target === '#overall') {
                overallTable = initDataTable('#overall_table');
                if (overallTable) {
                    overallTable.buttons().container().appendTo('#overall_table_wrapper .col-md-6:eq(0)');
                }
            } else if (target === '#location') {
                locationTable = initDataTable('#location_table');
                if (locationTable) {
                    locationTable.buttons().container().appendTo('#location_table_wrapper .col-md-6:eq(0)');
                }
            } else if (target === '#batch') {
                batchTable = initDataTable('#batch_table');
                if (batchTable) {
                    batchTable.buttons().container().appendTo('#batch_table_wrapper .col-md-6:eq(0)');
                }
            }
        }, 100);
    });
});
</script>
@endpush