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

{{-- CREATE MODAL --}}
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

                    <div id="add_items">
                        <div class="card card-outline card-secondary adjustment-item mb-2" data-index="0">
                            <div class="card-body py-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="small mb-1">Product</label>
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
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-2">
                                            <label class="small mb-1">Type</label>
                                            <select name="items[0][adjustment_type]" class="form-control">
                                                <option value="IN">Stock In</option>
                                                <option value="OUT">Stock Out</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small mb-1">Lot / Batch No</label>
                                            <input type="text" name="items[0][lot_number]" class="form-control" placeholder="Optional">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-2">
                                            <label class="small mb-1">Expiry Date</label>
                                            <input type="date" name="items[0][expiry_date]" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row align-items-end">
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label class="small mb-1">Full Packages</label>
                                            <input type="number" name="items[0][full_packages]" class="form-control packages-input" value="0" min="0" step="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label class="small mb-1">Units</label>
                                            <input type="number" name="items[0][extra_units]" class="form-control extra-input" value="0" min="0" step="1">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            <label class="small mb-1">Pack Size</label>
                                            <input type="text" class="form-control pack-size-display" value="0" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            <label class="small mb-1">Total Quantity</label>
                                            <input type="text" name="items[0][quantity]" class="form-control quantity-display" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-right">
                                        <button type="button" class="btn btn-danger btn-sm remove-tr">
                                            <i class="fa fa-times"></i> Remove
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm add-row">
                                            <i class="fa fa-plus-circle"></i> Add Another
                                        </button>
                                    </div>
                                </div>
                            </div>
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

    function updatePackSize(card) {
        var select = card.querySelector('.product-select');
        var display = card.querySelector('.pack-size-display');
        if (!select || !display) return;
        display.value = getProductPackSize(select.value);
    }

    function updatePackagesDisabled(card) {
        var select = card.querySelector('.product-select');
        var packagesInput = card.querySelector('.packages-input');
        if (!select || !packagesInput) return;
        if (getProductPackagingType(select.value) === 'unit') {
            packagesInput.readOnly = true;
            packagesInput.value = 0;
        } else {
            packagesInput.readOnly = false;
        }
    }

    function updateQuantity(card) {
        var packagesInput = card.querySelector('.packages-input');
        var extraInput = card.querySelector('.extra-input');
        var quantityDisplay = card.querySelector('.quantity-display');
        var packSizeDisplay = card.querySelector('.pack-size-display');
        if (!packagesInput || !extraInput || !quantityDisplay || !packSizeDisplay) return;

        var packSize = parseInt(packSizeDisplay.value) || 0;
        var packages = parseInt(packagesInput.value) || 0;
        var extra = parseFloat(extraInput.value) || 0;
        quantityDisplay.value = ((packages * packSize) + extra).toFixed(0);
    }

    function initCard(card) {
        updatePackSize(card);
        updatePackagesDisabled(card);
        updateQuantity(card);

        var packagesInput = card.querySelector('.packages-input');
        var extraInput = card.querySelector('.extra-input');
        var productSelect = card.querySelector('.product-select');

        if (packagesInput) packagesInput.addEventListener('input', function () { updateQuantity(card); });
        if (extraInput) extraInput.addEventListener('input', function () { updateQuantity(card); });
        if (productSelect) productSelect.addEventListener('change', function () {
            updatePackSize(card);
            updatePackagesDisabled(card);
            updateQuantity(card);
        });
    }

    function buildOptions() {
        var opts = '<option value="">Select</option>';
        products.forEach(function (p) {
            opts += '<option value="' + p.id + '" data-pack-size="' + p.pack_size + '" data-packaging-type="' + p.packaging_type + '">' + p.name + '</option>';
        });
        return opts;
    }

    function buildCard(idx, mode) {
        var buttons = mode === 'edit'
            ? '<button type="button" class="btn btn-danger btn-sm remove-edit-card"><i class="fa fa-times"></i> Remove</button>'
            : '<button type="button" class="btn btn-danger btn-sm remove-tr"><i class="fa fa-times"></i> Remove</button>' +
              '<button type="button" class="btn btn-success btn-sm add-row ml-1"><i class="fa fa-plus-circle"></i> Add Another</button>';

        return '' +
            '<div class="card card-outline card-secondary adjustment-item mb-2" data-index="' + idx + '">' +
                '<div class="card-body py-3">' +
                    '<div class="row">' +
                        '<div class="col-md-4">' +
                            '<div class="form-group mb-2">' +
                                '<label class="small mb-1">Product</label>' +
                                '<select name="items[' + idx + '][product_id]" class="form-control product-select" required>' + buildOptions() + '</select>' +
                            '</div>' +
                        '</div>' +
                        '<div class="col-md-2">' +
                            '<div class="form-group mb-2">' +
                                '<label class="small mb-1">Type</label>' +
                                '<select name="items[' + idx + '][adjustment_type]" class="form-control">' +
                                    '<option value="IN">Stock In</option>' +
                                    '<option value="OUT">Stock Out</option>' +
                                '</select>' +
                            '</div>' +
                        '</div>' +
                        '<div class="col-md-3">' +
                            '<div class="form-group mb-2">' +
                                '<label class="small mb-1">Lot / Batch No</label>' +
                                '<input type="text" name="items[' + idx + '][lot_number]" class="form-control" placeholder="Optional">' +
                            '</div>' +
                        '</div>' +
                        '<div class="col-md-3">' +
                            '<div class="form-group mb-2">' +
                                '<label class="small mb-1">Expiry Date</label>' +
                                '<input type="date" name="items[' + idx + '][expiry_date]" class="form-control" required>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="row align-items-end">' +
                        '<div class="col-md-2">' +
                            '<div class="form-group mb-0">' +
                                '<label class="small mb-1">Full Packages</label>' +
                                '<input type="number" name="items[' + idx + '][full_packages]" class="form-control packages-input" value="0" min="0" step="1" required>' +
                            '</div>' +
                        '</div>' +
                        '<div class="col-md-2">' +
                            '<div class="form-group mb-0">' +
                                '<label class="small mb-1">Units</label>' +
                                '<input type="number" name="items[' + idx + '][extra_units]" class="form-control extra-input" value="0" min="0" step="1">' +
                            '</div>' +
                        '</div>' +
                        '<div class="col-md-2">' +
                            '<div class="form-group mb-0">' +
                                '<label class="small mb-1">Pack Size</label>' +
                                '<input type="text" class="form-control pack-size-display" value="0" readonly>' +
                            '</div>' +
                        '</div>' +
                        '<div class="col-md-3">' +
                            '<div class="form-group mb-0">' +
                                '<label class="small mb-1">Total Quantity</label>' +
                                '<input type="text" name="items[' + idx + '][quantity]" class="form-control quantity-display" readonly>' +
                            '</div>' +
                        '</div>' +
                        '<div class="col-md-3 text-right">' + buttons + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
    }

    document.addEventListener('click', function (e) {
        var addBtn = e.target.closest('.add-row');
        if (addBtn) {
            e.preventDefault();
            var container = document.getElementById('add_items');
            var count = container.querySelectorAll('.adjustment-item').length;
            var wrapper = document.createElement('div');
            wrapper.innerHTML = buildCard(count, 'create');
            var card = wrapper.firstElementChild;
            container.appendChild(card);
            initCard(card);
        }

        var removeBtn = e.target.closest('.remove-tr');
        if (removeBtn) {
            e.preventDefault();
            var container = document.getElementById('add_items');
            var cards = container.querySelectorAll('.adjustment-item');
            if (cards.length > 1) {
                removeBtn.closest('.adjustment-item').remove();
            } else {
                alert('At least one product is required.');
            }
        }

        var addEditBtn = e.target.closest('.add-edit-card');
        if (addEditBtn) {
            e.preventDefault();
            var targetId = addEditBtn.dataset.target;
            var container = document.getElementById(targetId);
            if (!container) return;
            var count = container.querySelectorAll('.adjustment-item').length;
            var wrapper = document.createElement('div');
            wrapper.innerHTML = buildCard(count, 'edit');
            var card = wrapper.firstElementChild;
            container.appendChild(card);
            initCard(card);
        }

        var removeEditBtn = e.target.closest('.remove-edit-card');
        if (removeEditBtn) {
            e.preventDefault();
            var container = removeEditBtn.closest('[id^="edit_items_"]');
            var cards = container.querySelectorAll('.adjustment-item');
            if (cards.length > 1) {
                removeEditBtn.closest('.adjustment-item').remove();
            } else {
                alert('At least one product is required.');
            }
        }
    });

    document.querySelectorAll('#add_items .adjustment-item').forEach(initCard);

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
            $('#editAdjustmentBody').find('[id^="edit_items_"] .adjustment-item').each(function () {
                initCard(this);
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