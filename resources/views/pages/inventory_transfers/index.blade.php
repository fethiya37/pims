@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <div class="pl-3">
                                <b>Inventory Transfers: <span id="totalTransfers">—</span></b>
                            </div>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-primary btn-sm float-right" data-toggle="modal"
                                data-target="#createTransferModal">
                                New Transfer
                            </button>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li style="color: red">{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="transfersTable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Transfer #</th>
                                    <th>Requested Date</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th style="width: 240px;">Items</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- CREATE MODAL --}}
        <div class="modal fade" id="createTransferModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">New Inventory Transfer</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('inventory-transfers.store') }}" method="POST">
                            @csrf
                            <div class="invoice p-3 mb-3">
                                <div class="row">
                                    <div class="col-12">
                                        <h4>
                                            <i class="fas fa-exchange-alt"></i> Transfer Request
                                            <small class="float-right">Date: {{ \Carbon\Carbon::now()->toFormattedDateString() }}</small>
                                        </h4>
                                    </div>
                                </div>

                                <div class="row invoice-info mb-4">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>From (Store)</label>
                                            <select name="from_location_id" class="form-control" required>
                                                <option value="">Select Store</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>To (Sale / Point of Use)</label>
                                            <select name="to_location_id" class="form-control" required
                                                {{ !$isSuperAdmin ? 'disabled' : '' }}>
                                                <option value="">Select Destination</option>
                                                @foreach ($saleStores as $saleStore)
                                                    <option value="{{ $saleStore->id }}"
                                                        {{ Auth::user()->location_id == $saleStore->id ? 'selected' : '' }}>
                                                        {{ $saleStore->name }} (Sale)
                                                    </option>
                                                @endforeach
                                                @foreach ($pointOfUseStores as $pointOfUse)
                                                    <option value="{{ $pointOfUse->id }}"
                                                        {{ Auth::user()->location_id == $pointOfUse->id ? 'selected' : '' }}>
                                                        {{ $pointOfUse->name }} (Point of Use)
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if (!$isSuperAdmin)
                                                <input type="hidden" name="to_location_id" value="{{ Auth::user()->location_id }}">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Collected By</label>
                                            <input type="text" name="collected_by" class="form-control" placeholder="Who collected the items?">
                                        </div>
                                    </div>
                                </div>

                                <div class="row invoice-info mb-4">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Remarks</label>
                                            <input type="text" name="remarks" class="form-control" placeholder="Optional remarks">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Full Packages</th>
                                                    <th>Units</th>
                                                    <th>Pack Size</th>
                                                    <th>Total Qty</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="add_items">
                                                <tr>
                                                    <td style="width: 180px;">
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
                                                    <td>
                                                        <input type="number" name="items[0][full_packages]" class="form-control packages-input" value="0" min="0" step="1" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[0][extra_units]" class="form-control extra-input" value="0" min="0" step="1">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control pack-size-display" value="0" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="items[0][quantity]" class="form-control quantity-display" readonly>
                                                    </td>
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

                                <div class="row no-print">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success float-right">
                                            {{ __('messages.submit') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- SHARED EDIT MODAL --}}
        <div class="modal fade" id="editTransferModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Transfer</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="editTransferBody"></div>
                </div>
            </div>
        </div>

        {{-- SHARED REJECT MODAL --}}
        <div class="modal fade" id="rejectTransferModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Reject Transfer</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="rejectTransferForm" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Remarks</label>
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

        {{-- SHARED VIEW MODAL --}}
        <div class="modal fade" id="viewTransferModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Transfer Details</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="viewTransferBody"></div>
                </div>
            </div>
        </div>

        <script id="transfer-data" type="application/json">
            {!! json_encode($products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'pack_size' => $p->default_pack_size,
                'packaging_type' => $p->packaging_type ?? 'pack',
            ])->toArray()) !!}
        </script>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    var products = JSON.parse(document.getElementById('transfer-data').textContent || '[]');

    function getProductPackSize(productId) {
        var found = products.find(function (p) { return p.id == productId; });
        return found ? found.pack_size : 0;
    }

    function getProductPackagingType(productId) {
        var found = products.find(function (p) { return p.id == productId; });
        return found ? found.packaging_type : 'pack';
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

    function buildProductOptions(selectedId) {
        var opts = '<option value="">Select</option>';
        products.forEach(function (p) {
            var selected = (selectedId && selectedId == p.id) ? ' selected' : '';
            opts += '<option value="' + p.id + '" data-pack-size="' + p.pack_size + '" data-packaging-type="' + p.packaging_type + '"' + selected + '>' + p.name + '</option>';
        });
        return opts;
    }

    function buildNewRowHtml(idx, selectedId, mode) {
        var rowMode = mode || 'create';
        var actionsHtml = rowMode === 'create'
            ? '<td><div class="d-flex"><button type="button" class="remove-tr btn btn-danger btn-sm mr-1"><b>X</b></button><button type="button" class="btn btn-success btn-sm add-row"><i class="fa fa-plus-circle"></i></button></div></td>'
            : '<td><button type="button" class="remove-edit-tr btn btn-danger btn-sm"><b>X</b></button></td>';

        return '' +
            '<td style="width: 180px;">' +
                '<select name="items[' + idx + '][product_id]" class="form-control product-select" required>' +
                    buildProductOptions(selectedId) +
                '</select>' +
            '</td>' +
            '<td><input type="number" name="items[' + idx + '][full_packages]" class="form-control packages-input" value="0" min="0" step="1" required></td>' +
            '<td><input type="number" name="items[' + idx + '][extra_units]" class="form-control extra-input" value="0" min="0" step="1"></td>' +
            '<td><input type="text" class="form-control pack-size-display" value="0" readonly></td>' +
            '<td><input type="text" name="items[' + idx + '][quantity]" class="form-control quantity-display" readonly></td>' +
            actionsHtml;
    }

    document.addEventListener('click', function (e) {
        var addBtn = e.target.closest('.add-row');
        if (addBtn) {
            e.preventDefault();
            var tbody = addBtn.closest('tbody');
            var rowCount = tbody.querySelectorAll('tr').length;
            var newRow = document.createElement('tr');
            newRow.innerHTML = buildNewRowHtml(rowCount, null, 'create');
            tbody.appendChild(newRow);
            initRow(newRow);
        }

        var removeBtn = e.target.closest('.remove-tr');
        if (removeBtn) {
            e.preventDefault();
            var row = removeBtn.closest('tr');
            var tbody = row.closest('tbody');
            if (tbody.querySelectorAll('tr').length > 1) row.remove();
            else alert('You must have at least one product.');
        }

        var addEditBtn = e.target.closest('.add-edit-row');
        if (addEditBtn) {
            e.preventDefault();
            var targetId = addEditBtn.dataset.target;
            var container = document.getElementById(targetId);
            if (!container) return;
            var idx = parseInt(addEditBtn.dataset.index) || container.querySelectorAll('tr').length;
            var newRow = document.createElement('tr');
            newRow.innerHTML = buildNewRowHtml(idx, null, 'edit');
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
            else alert('You must have at least one product.');
        }
    });

    document.querySelectorAll('#add_items tr').forEach(initRow);

    $('#transfersTable').DataTable({
        processing: true, serverSide: true,
        dom: "<'row mb-2'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: ["csv", "excel", "pdf", "print"],
        ajax: { url: '{{ route('inventory-transfers.data') }}', type: 'GET' },
        columns: [
            { data: 'no',         orderable: false, searchable: false },
            { data: 'reference',  orderable: false },
            { data: 'date',       name: 'requested_date' },
            { data: 'from',       orderable: false },
            { data: 'to',         orderable: false },
            { data: 'items',      orderable: false, searchable: false },
            { data: 'status',     name: 'status' },
            { data: 'actions',    orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 100,
        drawCallback: function () {
            $('#totalTransfers').text(this.api().page.info().recordsTotal);
        }
    });

    $(document).on('click', '.view-transfer-btn', function () {
        var id = $(this).data('id');
        $.get('/inventory-transfers/' + id + '/view', function (html) {
            $('#viewTransferBody').html(html);
            $('#viewTransferModal').modal('show');
        });
    });

    $(document).on('click', '.edit-transfer-btn', function () {
        var id = $(this).data('id');
        $.get('/inventory-transfers/' + id + '/edit-form', function (html) {
            $('#editTransferBody').html(html);
            $('#editTransferModal').modal('show');
            $('#editTransferBody').find('tbody[id^="edit_items_"] tr').each(function () {
                initRow(this);
            });
        });
    });

    $(document).on('click', '.reject-transfer-btn', function () {
        var id = $(this).data('id');
        $('#rejectTransferForm').attr('action', '/inventory-transfers/' + id + '/reject');
        $('#rejectTransferModal').modal('show');
    });
})();
</script>
@endpush