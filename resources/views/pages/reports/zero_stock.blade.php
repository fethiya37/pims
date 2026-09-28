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
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="zero_stock_table" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Packaging Type</th>
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
    $('#zero_stock_table').DataTable({
        processing: true, serverSide: true,
        dom: "<'row mb-2'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: ["csv", "excel", "pdf", "print"],
        ajax: '{{ route('reports.zero-stock.data') }}',
        columns: [
            { data: 'no', orderable: false, searchable: false },
            { data: 'item_code' },
            { data: 'name' },
            { data: 'category' },
            { data: 'current_stock_units' },
            { data: 'packaging', orderable: false },
            { data: 'status', orderable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 25
    });
});
</script>
@endpush