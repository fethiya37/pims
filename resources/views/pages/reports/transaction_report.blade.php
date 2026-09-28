@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3>Inventory Transaction Report</h3>
                <small>From {{ $fromDate }} to {{ $toDate }}</small>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.transaction') }}" class="mb-3">
                    <div class="row">
                        <div class="col-md-2">
                            <select name="product_id" class="form-control select2" onchange="this.form.submit()">
                                <option value="">All Products</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="from_location_id" class="form-control select2" onchange="this.form.submit()">
                                <option value="">From Any</option>
                                @foreach ($allowedLocations as $location)
                                    <option value="{{ $location->id }}" {{ $fromLocationId == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="to_location_id" class="form-control select2" onchange="this.form.submit()">
                                <option value="">To Any</option>
                                @foreach ($allowedLocations as $location)
                                    <option value="{{ $location->id }}" {{ $toLocationId == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="transaction_type" class="form-control select2" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                @foreach (['opening','receiving','transfer','consumption','sale','adjustment'] as $t)
                                    <option value="{{ $t }}" {{ request('transaction_type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1"><input type="date" name="from_date" value="{{ $fromDate }}" class="form-control" onchange="this.form.submit()"></div>
                        <div class="col-md-1"><input type="date" name="to_date" value="{{ $toDate }}" class="form-control" onchange="this.form.submit()"></div>
                        <div class="col-md-2"><a href="{{ route('reports.transaction') }}" class="btn btn-secondary">Reset</a></div>
                    </div>
                </form>
                <table id="transaction_table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th><th>Date</th><th>Reference</th><th>Type</th>
                            <th>Product</th><th>Lot #</th><th>Expiry</th>
                            <th>From</th><th>To</th><th>Qty (units)</th>
                            <th class="pack-col">Qty (pack)</th><th>User</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function () {
    $('.select2').select2();

    $('#transaction_table').DataTable({
        processing: true,
        serverSide: true,
        dom: "<'row mb-2'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: ["csv", "excel", "pdf", "print"],
        ajax: {
            url: '{{ route('reports.transaction.data') }}',
            data: {
                product_id: '{{ request('product_id') }}',
                from_location_id: '{{ $fromLocationId }}',
                to_location_id: '{{ $toLocationId }}',
                transaction_type: '{{ $transactionType }}',
                from_date: '{{ $fromDate }}',
                to_date: '{{ $toDate }}'
            }
        },
        columns: [
            { data: 'no', orderable: false, searchable: false },
            { data: 'date' },
            { data: 'reference' },
            { data: 'type' },
            { data: 'product' },
            { data: 'lot_number' },
            { data: 'expiry' },
            { data: 'from', orderable: false },
            { data: 'to', orderable: false },
            { data: 'quantity_units' },
            { data: 'quantity_pack' },
            { data: 'user', orderable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
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