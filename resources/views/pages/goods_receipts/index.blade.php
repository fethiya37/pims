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
                                <b>Goods Receipts: <span id="totalReceipts">—</span></b>
                            </div>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-primary btn-sm float-right" data-toggle="modal"
                                data-target="#modal-xl">
                                New Goods Receipt
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
                        <table id="receiptsTable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Receipt #</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Supplier</th>
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

        {{-- New Goods Receipt modal --}}
        <div class="modal fade" id="modal-xl">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">New Goods Receipt</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('goods-receipts.store') }}">
                            @csrf
                            <div class="invoice p-3 mb-3">
                                <div class="row">
                                    <div class="col-12">
                                        <h4>
                                            <i class="fas fa-globe"></i> Prime Medicare PLC
                                            <small class="float-right">Date:
                                                {{ \Carbon\Carbon::now()->toFormattedDateString() }}</small>
                                        </h4>
                                    </div>
                                </div>

                                <div class="row invoice-info mb-4">
                                    <div class="col-sm-4">
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
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Supplier</label>
                                            <select name="supplier_id" class="form-control">
                                                <option value="">Select Supplier</option>
                                                @foreach ($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Receipt Date</label>
                                            <input type="date" name="receipt_date" class="form-control"
                                                value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row invoice-info mb-4">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Reference Number</label>
                                            <input type="text" name="reference_number" class="form-control"
                                                placeholder="Supplier Invoice #">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Delivered By</label>
                                            <input type="text" name="delivered_by" class="form-control"
                                                placeholder="Who delivered the goods?">
                                        </div>
                                    </div>
                                </div>

                                <div class="row invoice-info mb-4">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <input type="text" name="notes" class="form-control" placeholder="Notes">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Lot #</th>
                                                    <th>Full Packages</th>
                                                    <th>Units</th>
                                                    <th>Pack Size</th>
                                                    <th>Total Qty</th>
                                                    <th>Expiry</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="add_items">
                                                <tr>
                                                    <td style="width: 180px;">
                                                        <select name="items[0][product_id]"
                                                            class="form-control product-select" required>
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
                                                        <input type="text" name="items[0][lot_number]"
                                                            class="form-control" placeholder="Lot #">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[0][full_packages]"
                                                            class="form-control packages-input" value="0" min="0" step="1" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="items[0][extra_units]"
                                                            class="form-control extra-input" value="0" min="0" step="1">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control pack-size-display" value="0" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="items[0][quantity]"
                                                            class="form-control quantity-display" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="date" name="items[0][expiry_date]"
                                                            class="form-control" required>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex">
                                                            <button type="button"
                                                                class="remove-tr btn btn-danger btn-sm mr-1"><b>X</b></button>
                                                            <button type="button"
                                                                class="btn btn-success btn-sm add-row"><i
                                                                    class="fa fa-plus-circle"></i></button>
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

        {{-- Shared View modal --}}
        <div class="modal fade" id="viewReceiptModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">View Goods Receipt</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="viewReceiptBody"></div>
                </div>
            </div>
        </div>

        {{-- Shared Edit modal --}}
        <div class="modal fade" id="editReceiptModal">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Goods Receipt</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="editReceiptBody"></div>
                </div>
            </div>
        </div>

        <script id="goods-receipt-data" type="application/json">
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
    var products = JSON.parse(document.getElementById('goods-receipt-data').textContent || '[]');

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
            '<td><input type="text" name="items[' + idx + '][lot_number]" class="form-control" placeholder="Lot #"></td>' +
            '<td><input type="number" name="items[' + idx + '][full_packages]" class="form-control packages-input" value="0" min="0" step="1" required></td>' +
            '<td><input type="number" name="items[' + idx + '][extra_units]" class="form-control extra-input" value="0" min="0" step="1"></td>' +
            '<td><input type="text" class="form-control pack-size-display" value="0" readonly></td>' +
            '<td><input type="text" name="items[' + idx + '][quantity]" class="form-control quantity-display" readonly></td>' +
            '<td><input type="date" name="items[' + idx + '][expiry_date]" class="form-control" required></td>' +
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

    $('#receiptsTable').DataTable({
        processing: true,
        serverSide: true,
        dom: "<'row mb-2'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: ["csv", "excel", "pdf", "print"],
        ajax: {
            url: '{{ route('goods-receipts.data') }}',
            type: 'GET'
        },
        columns: [
            { data: 'no',         orderable: false, searchable: false },
            { data: 'reference',  name: 'reference_number' },
            { data: 'date',       name: 'receipt_date' },
            { data: 'location',   orderable: false, searchable: false },
            { data: 'supplier',   orderable: false, searchable: false },
            { data: 'items',      orderable: false, searchable: false },
            { data: 'status',     name: 'status' },
            { data: 'actions',    orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 100,
        drawCallback: function () {
            $('#totalReceipts').text(this.api().page.info().recordsTotal);
        }
    });

    $(document).on('click', '.view-receipt-btn', function () {
        var id = $(this).data('id');
        $.get('/goods-receipts/' + id + '/view', function (html) {
            $('#viewReceiptBody').html(html);
            $('#viewReceiptModal').modal('show');
        });
    });

    $(document).on('click', '.edit-receipt-btn', function () {
        var id = $(this).data('id');
        $.get('/goods-receipts/' + id + '/edit-form', function (html) {
            $('#editReceiptBody').html(html);
            $('#editReceiptModal').modal('show');
            $('#editReceiptBody').find('tbody[id^="edit_items_"] tr').each(function () {
                initRow(this);
            });
        });
    });
})();
</script>
@endpush