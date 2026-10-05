@extends('inc.frame')

@section('content')
<div class="container-fluid px-4">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Inventory Adjustments</h3>
            <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#adjustmentModal">
                <i class="fas fa-plus"></i> New Adjustment Request
            </button>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="adjustmentsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Reference</th>
                            <th>Location</th>
                            <th style="width: 240px;">Items</th>
                            <th>Status</th>
                            <th>Requested By</th>
                            <th>Approved By</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">New Inventory Adjustment Request</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('inventory-adjustments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Location</label>
                                <select name="location_id" class="form-control" required
                                    {{ !$isSuperAdmin ? 'disabled' : '' }}>
                                    <option value="">Select Location</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}"
                                            {{ Auth::user()->location_id == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (!$isSuperAdmin)
                                    <input type="hidden" name="location_id" value="{{ Auth::user()->location_id }}">
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Reason</label>
                                <input type="text" name="reason" class="form-control" placeholder="Why is this adjustment needed?">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Lot/Batch</th>
                                        <th>Expiry</th>
                                        <th>Full Packages</th>
                                        <th>Units</th>
                                        <th>Pack Size</th>
                                        <th>Total Qty</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="add_items">
                                    <tr>
                                        <td style="width: 200px;">
                                            <select name="items[0][product_id]" class="form-control product-select" required>
                                                <option value="">Select</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        data-pack-size="{{ $product->default_pack_size }}"
                                                        data-packaging-type="{{ $product->packaging_type ?? 'pack' }}">
                                                        {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td style="width: 100px;">
                                            <select name="items[0][adjustment_type]" class="form-control">
                                                <option value="IN">Stock In</option>
                                                <option value="OUT">Stock Out</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="items[0][lot_number]" class="form-control" placeholder="Lot #"></td>
                                        <td><input type="date" name="items[0][expiry_date]" class="form-control" required></td>
                                        <td><input type="number" name="items[0][full_packages]" class="form-control packages-input" value="0" min="0" step="1" required></td>
                                        <td><input type="number" name="items[0][extra_units]" class="form-control extra-input" value="0" min="0" step="1"></td>
                                        <td><input type="text" class="form-control pack-size-display" value="0" readonly></td>
                                        <td><input type="text" name="items[0][quantity]" class="form-control quantity-display" readonly></td>
                                        <td>
                                            <div class="d-flex">
                                                <button type="button" class="remove-tr btn btn-danger btn-sm mr-1"><b>X</b></button>
                                                <button type="button" class="btn btn-success btn-sm add-row"><i class="fa fa-plus-circle"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.close') }}</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editAdjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Edit Adjustment Request</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="editAdjustmentBody"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewAdjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Adjustment Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewAdjustmentBody"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectAdjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Adjustment</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectAdjustmentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason for rejection</label>
                        <textarea name="remarks" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script id="adjustment-data" type="application/json">
    {!! json_encode($products->map(fn($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'pack_size' => $p->default_pack_size,
        'packaging_type' => $p->packaging_type ?? 'pack',
    ])->toArray()) !!}
</script>
@endsection

@push('scripts')
<script>
(function () {
    var products = JSON.parse(document.getElementById('adjustment-data').textContent || '[]');

    function getProductPackSize(id) {
        var p = products.find(function (x) { return x.id == id; });
        return p ? p.pack_size : 0;
    }

    function getProductPackagingType(id) {
        var p = products.find(function (x) { return x.id == id; });
        return p ? p.packaging_type : 'pack';
    }

    function updatePackSize(row) {
        var select = row.querySelector('.product-select');
        var display = row.querySelector('.pack-size-display');
        if (!select || !display) return;
        display.value = getProductPackSize(select.value);
    }

    function updatePackagesDisabled(row) {
        var select = row.querySelector('.product-select');
        var packagesInput = row.querySelector('.packages-input');
        if (!select || !packagesInput) return;
        if (getProductPackagingType(select.value) === 'unit') {
            packagesInput.readOnly = true;
            packagesInput.value = 0;
        } else {
            packagesInput.readOnly = false;
        }
    }

    function updateQuantity(row) {
        var packagesInput = row.querySelector('.packages-input');
        var extraInput = row.querySelector('.extra-input');
        var quantityDisplay = row.querySelector('.quantity-display');
        var packSizeDisplay = row.querySelector('.pack-size-display');
        if (!packagesInput || !extraInput || !quantityDisplay || !packSizeDisplay) return;

        var packSize = parseInt(packSizeDisplay.value) || 0;
        var packages = parseInt(packagesInput.value) || 0;
        var extra = parseFloat(extraInput.value) || 0;
        quantityDisplay.value = ((packages * packSize) + extra).toFixed(0);
    }

    function initRow(row) {
        updatePackSize(row);
        updatePackagesDisabled(row);
        updateQuantity(row);

        var packagesInput = row.querySelector('.packages-input');
        var extraInput = row.querySelector('.extra-input');
        var productSelect = row.querySelector('.product-select');

        if (packagesInput) packagesInput.addEventListener('input', function () { updateQuantity(row); });
        if (extraInput) extraInput.addEventListener('input', function () { updateQuantity(row); });
        if (productSelect) productSelect.addEventListener('change', function () {
            updatePackSize(row);
            updatePackagesDisabled(row);
            updateQuantity(row);
        });
    }

    function buildOptions(selectedId) {
        var opts = '<option value="">Select</option>';
        products.forEach(function (p) {
            var sel = (selectedId && selectedId == p.id) ? ' selected' : '';
            opts += '<option value="' + p.id + '" data-pack-size="' + p.pack_size + '" data-packaging-type="' + p.packaging_type + '"' + sel + '>' + p.name + '</option>';
        });
        return opts;
    }

    function buildRow(idx, mode) {
        var actions = mode === 'edit'
            ? '<td><button type="button" class="remove-edit-tr btn btn-danger btn-sm"><b>X</b></button></td>'
            : '<td><div class="d-flex"><button type="button" class="remove-tr btn btn-danger btn-sm mr-1"><b>X</b></button><button type="button" class="btn btn-success btn-sm add-row"><i class="fa fa-plus-circle"></i></button></div></td>';

        return '' +
            '<td style="width: 200px;">' +
                '<select name="items[' + idx + '][product_id]" class="form-control product-select" required>' +
                    buildOptions(null) +
                '</select>' +
            '</td>' +
            '<td style="width: 100px;">' +
                '<select name="items[' + idx + '][adjustment_type]" class="form-control">' +
                    '<option value="IN">Stock In</option>' +
                    '<option value="OUT">Stock Out</option>' +
                '</select>' +
            '</td>' +
            '<td><input type="text" name="items[' + idx + '][lot_number]" class="form-control" placeholder="Lot #"></td>' +
            '<td><input type="date" name="items[' + idx + '][expiry_date]" class="form-control" required></td>' +
            '<td><input type="number" name="items[' + idx + '][full_packages]" class="form-control packages-input" value="0" min="0" step="1" required></td>' +
            '<td><input type="number" name="items[' + idx + '][extra_units]" class="form-control extra-input" value="0" min="0" step="1"></td>' +
            '<td><input type="text" class="form-control pack-size-display" value="0" readonly></td>' +
            '<td><input type="text" name="items[' + idx + '][quantity]" class="form-control quantity-display" readonly></td>' +
            actions;
    }

    document.addEventListener('click', function (e) {
        var addBtn = e.target.closest('.add-row');
        if (addBtn) {
            e.preventDefault();
            var tbody = addBtn.closest('tbody');
            var rowCount = tbody.querySelectorAll('tr').length;
            var newRow = document.createElement('tr');
            newRow.innerHTML = buildRow(rowCount, 'create');
            tbody.appendChild(newRow);
            initRow(newRow);
        }

        var removeBtn = e.target.closest('.remove-tr');
        if (removeBtn) {
            e.preventDefault();
            var row = removeBtn.closest('tr');
            var tbody = row.closest('tbody');
            if (tbody.querySelectorAll('tr').length > 1) row.remove();
            else alert('At least one product is required.');
        }

        var addEditBtn = e.target.closest('.add-edit-row');
        if (addEditBtn) {
            e.preventDefault();
            var targetId = addEditBtn.dataset.target;
            var container = document.getElementById(targetId);
            if (!container) return;
            var idx = parseInt(addEditBtn.dataset.index) || container.querySelectorAll('tr').length;
            var newRow = document.createElement('tr');
            newRow.innerHTML = buildRow(idx, 'edit');
            container.appendChild(newRow);
            initRow(newRow);
            addEditBtn.dataset.index = idx + 1;
        }

        var removeEditBtn = e.target.closest('.remove-edit-tr');
        if (removeEditBtn) {
            e.preventDefault();
            var row = removeEditBtn.closest('tr');
            var tbody = row.closest('tbody');
            if (tbody.querySelectorAll('tr').length > 1) row.remove();
            else alert('At least one product is required.');
        }
    });

    document.querySelectorAll('#add_items tr').forEach(initRow);

    $('#adjustmentsTable').DataTable({
        processing: true,
        serverSide: true,
        dom: "<'row mb-2'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: ["csv", "excel", "pdf", "print"],
        ajax: { url: '{{ route('inventory-adjustments.data') }}', type: 'GET' },
        columns: [
            { data: 'no',        orderable: false, searchable: false },
            { data: 'reference', orderable: false },
            { data: 'location',  orderable: false },
            { data: 'items',     orderable: false, searchable: false },
            { data: 'status',    name: 'status' },
            { data: 'requested', orderable: false },
            { data: 'approved',  orderable: false },
            { data: 'date',      name: 'created_at' },
            { data: 'actions',   orderable: false, searchable: false }
        ],
        order: [[7, 'desc']],
        pageLength: 25
    });

    $(document).on('click', '.view-adjustment-btn', function () {
        var id = $(this).data('id');
        $.get('/inventory-adjustments/' + id + '/view', function (html) {
            $('#viewAdjustmentBody').html(html);
            $('#viewAdjustmentModal').modal('show');
        });
    });

    $(document).on('click', '.edit-adjustment-btn', function () {
        var id = $(this).data('id');
        $.get('/inventory-adjustments/' + id + '/edit-form', function (html) {
            $('#editAdjustmentBody').html(html);
            $('#editAdjustmentModal').modal('show');
            $('#editAdjustmentBody').find('tbody[id^="edit_items_"] tr').each(function () {
                initRow(this);
            });
        });
    });

    $(document).on('click', '.reject-adjustment-btn', function () {
        var id = $(this).data('id');
        $('#rejectAdjustmentForm').attr('action', '/inventory-adjustments/' + id + '/reject');
        $('#rejectAdjustmentModal').modal('show');
    });
})();
</script>
@endpush