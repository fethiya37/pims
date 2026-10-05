@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">📋 Expiry Report</h3>
                <small class="text-muted">Products expiring within 90 days</small>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Expired</span>
                                <span class="info-box-number">{{ $summary['expired'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Urgent (≤30 Days)</span>
                                <span class="info-box-number">{{ $summary['urgent'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Soon (≤60 Days)</span>
                                <span class="info-box-number">{{ $summary['soon'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">OK (≤90 Days)</span>
                                <span class="info-box-number">{{ $summary['ok'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('reports.expiry') }}" class="mb-3">
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
                        <div class="col-md-2"><a href="{{ route('reports.expiry') }}" class="btn btn-secondary">Reset</a></div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="expiry_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Product</th>
                                <th>Location</th>
                                <th>Lot #</th>
                                <th>Quantity</th>
                                <th>Expiry Date</th>
                                <th>Days Left</th>
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
    $('#expiry_table').DataTable({
        processing: true, serverSide: true,
        dom: "<'row mb-2'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: ["csv", "excel", "pdf", "print"],
        ajax: {
            url: '{{ route('reports.expiry.data') }}',
            data: {
                product_id: '{{ request('product_id') }}',
                location_id: '{{ $locationId }}'
            }
        },
        columns: [
            { data: 'no', orderable: false, searchable: false },
            { data: 'product' },
            { data: 'location' },
            { data: 'lot_number' },
            { data: 'quantity' },
            { data: 'expiry_date' },
            { data: 'days_remaining' },
            { data: 'status' }
        ],
        order: [[5, 'asc']],
        pageLength: 100
    });
});
</script>
@endpush