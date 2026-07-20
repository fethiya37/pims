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
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Expired</span>
                                <span class="info-box-number">{{ $summary['expired'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Urgent (≤30 Days)</span>
                                <span class="info-box-number">{{ $summary['urgent'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Soon (≤60 Days)</span>
                                <span class="info-box-number">{{ $summary['soon'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
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
                            <a href="{{ route('reports.expiry') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="expiry_table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Location</th>
                                <th>Lot #</th>
                                <th>Quantity</th>
                                <th>Expiry Date</th>
                                <th>Days Left</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expiryItems as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->location->name }}</td>
                                    <td>{{ $item->lot_number ?? 'N/A' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->expiry_date->format('Y-m-d') }}</td>
                                    <td>{{ $item->days_remaining }}</td>
                                    <td>
                                        <span class="badge badge-{{ $item->badge_class }}">
                                            {{ $item->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($expiryItems->isEmpty())
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> No products expiring within 90 days.
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
    $('#expiry_table').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        pageLength: 20,
        order: [[4, 'asc']],
        buttons: ["csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#expiry_table_wrapper .col-md-6:eq(0)');
});
</script>
@endpush