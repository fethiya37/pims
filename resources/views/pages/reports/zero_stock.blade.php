@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i> Zero Stock Report
                    <small class="text-muted d-block">Products with no stock records</small>
                </h3>
                <div class="card-tools">
                    <span class="badge badge-danger">{{ $totalZeroStock }}</span>
                    <span class="text-muted">no stock records</span>
                </div>
            </div>

            <div class="card-body">
                @if ($zeroStockItems->isEmpty())
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> All products have stock records.
                    </div>
                @else
                    <div class="table-responsive">
                        <table id="zero_stock_table" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Code</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
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
                                            <span class="badge badge-info">{{ $item->product->item_code ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $item->product->name }}</strong>
                                        </td>
                                        <td>{{ optional($item->product->category)->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-danger badge-lg">{{ $item->current_stock_units }}</span>
                                        </td>
                                        <td>
                                            @if ($item->packaging_type === 'pack')
                                                <span class="badge badge-warning">Pack</span>
                                                <small class="d-block">Size: {{ $item->pack_size }} {{ $item->unit }}</small>
                                            @else
                                                <span class="badge badge-secondary">Unit</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times-circle"></i> No Stock Record
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <p class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Showing {{ $totalZeroStock }} out of {{ $totalProducts }} total product(s) with no stock records
                        </p>
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