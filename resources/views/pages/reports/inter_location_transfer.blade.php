@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3>Inter-Location Transfer Report</h3>
                <small>From {{ $fromDate }} to {{ $toDate }}</small>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('reports.inter-location-transfer') }}" class="mb-3">
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
                            <select name="from_location_id" class="form-control select2" onchange="this.form.submit()">
                                <option value="">From Any</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" {{ request('from_location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="to_location_id" class="form-control select2" onchange="this.form.submit()">
                                <option value="">To Any</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" {{ request('to_location_id') == $location->id ? 'selected' : '' }}>
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
                            <a href="{{ route('reports.inter-location-transfer') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                <table id="transfer_table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Product</th>
                            <th>Lot #</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Quantity (units)</th>
                            @if($transfers->contains(fn($t) => $t->packaging_type === 'pack'))
                            <th>Quantity (pack)</th>
                            @endif
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transfers as $t)
                            <tr>
                                <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $t->reference ?? 'N/A' }}</td>
                                <td>{{ $t->product->name ?? 'N/A' }}</td>
                                <td>{{ $t->lot_number ?? 'N/A' }}</td>
                                <td>{{ $t->fromLocation->name ?? 'N/A' }}</td>
                                <td>{{ $t->toLocation->name ?? 'N/A' }}</td>
                                <td>{{ $t->quantity_units }}</td>
                                @if($transfers->contains(fn($item) => $item->packaging_type === 'pack'))
                                <td>{{ $t->packaging_type === 'pack' ? $t->quantity_pack_display : '-' }}</td>
                                @endif
                                <td>{{ $t->user->name ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function() {
    $('.select2').select2();
    const dt = $('#transfer_table').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        pageLength: 20,
        buttons: ["csv","excel","pdf","print"]
    });
    dt.buttons().container().appendTo('#transfer_table_wrapper .col-md-6:eq(0)');
});
</script>
@endpush