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
                                <b>Inventory Transfers: {{ $transfers->count() }}</b>
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
                        <table id="example1" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Transfer #</th>
                                    <th>Requested Date</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transfers as $index => $transfer)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>#{{ $transfer->id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($transfer->requested_date)->toFormattedDateString() }}</td>
                                        <td>{{ optional($transfer->fromLocation)->name ?? 'N/A' }}</td>
                                        <td>{{ optional($transfer->toLocation)->name ?? 'N/A' }}</td>
                                        <td>
                                            @if ($transfer->items->count() > 0)
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Product</th>
                                                            <th>Qty</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($transfer->items as $item)
                                                            <tr>
                                                                <td>{{ $item->product->name }}</td>
                                                                <td>{{ $item->quantity }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <span class="text-muted">No items</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'approved' => 'info',
                                                    'issued' => 'primary',
                                                    'received' => 'success',
                                                    'rejected' => 'danger',
                                                ][$transfer->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-{{ $statusClass }}">{{ ucfirst($transfer->status) }}</span>
                                        </td>
                                        <td>
                                            @if ($transfer->status == 'pending')
                                                {{-- Edit and Delete are always shown if user can see the transfer --}}
                                                <a type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                                    data-target="#editTransferModal-{{ $transfer->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('inventory-transfers.destroy', $transfer->id) }}"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Delete this transfer?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>

                                                {{-- Approve & Reject: only if user is from the source location (from_location) --}}
                                                @if ($isSuperAdmin || Auth::user()->location_id == $transfer->from_location_id)
                                                    <a href="{{ route('inventory-transfers.approve', $transfer->id) }}"
                                                        class="btn btn-success btn-sm"
                                                        onclick="return confirm('Approve this transfer?');">
                                                        <i class="fas fa-check"></i> Approve
                                                    </a>
                                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                                        data-target="#rejectModal-{{ $transfer->id }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            @endif

                                            @if ($transfer->status == 'approved')
                                                {{-- Issue: only if user is from the source location --}}
                                                @if ($isSuperAdmin || Auth::user()->location_id == $transfer->from_location_id)
                                                    <a href="{{ route('inventory-transfers.issue', $transfer->id) }}"
                                                        class="btn btn-primary btn-sm"
                                                        onclick="return confirm('Issue this transfer?');">
                                                        <i class="fas fa-clipboard-check"></i> Issue
                                                    </a>
                                                @endif
                                            @endif

                                            @if ($transfer->status == 'issued')
                                                {{-- Receive: only if user is from the destination location --}}
                                                @if ($isSuperAdmin || Auth::user()->location_id == $transfer->to_location_id)
                                                    <a href="{{ route('inventory-transfers.receive', $transfer->id) }}"
                                                        class="btn btn-success btn-sm"
                                                        onclick="return confirm('Receive this transfer?');">
                                                        <i class="fas fa-box"></i> Receive
                                                    </a>
                                                @endif
                                            @endif

                                            {{-- View button always visible --}}
                                            <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal"
                                                data-target="#viewTransferModal-{{ $transfer->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center">No transfers found</td></tr>
                                @endforelse
                            </tbody>
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
                                            <label>To (Point of Use)</label>
                                            <select name="to_location_id" class="form-control" required
                                                {{ !$isSuperAdmin ? 'disabled' : '' }}>
                                                <option value="">Select Point of Use</option>
                                                @foreach ($pointOfUseStores as $pointOfUse)
                                                    <option value="{{ $pointOfUse->id }}"
                                                        {{ Auth::user()->location_id == $pointOfUse->id ? 'selected' : '' }}>
                                                        {{ $pointOfUse->name }}
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

        {{-- EDIT MODALS --}}
        @foreach ($transfers as $transfer)
            @if ($transfer->status == 'pending')
                <div class="modal fade" id="editTransferModal-{{ $transfer->id }}">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Transfer - #{{ $transfer->id }}</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('inventory-transfers.update', $transfer->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="invoice p-3 mb-3">
                                        <div class="row">
                                            <div class="col-12">
                                                <h4>
                                                    <i class="fas fa-exchange-alt"></i> Edit Transfer
                                                    <small class="float-right">Requested: {{ \Carbon\Carbon::parse($transfer->requested_date)->toFormattedDateString() }}</small>
                                                </h4>
                                            </div>
                                        </div>

                                        <div class="row invoice-info mb-4">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>From (Store)</label>
                                                    <select name="from_location_id" class="form-control" required>
                                                        @foreach ($stores as $store)
                                                            <option value="{{ $store->id }}"
                                                                {{ $store->id == $transfer->from_location_id ? 'selected' : '' }}>
                                                                {{ $store->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>To (Point of Use)</label>
                                                    <select name="to_location_id" class="form-control" required
                                                        {{ !$isSuperAdmin ? 'disabled' : '' }}>
                                                        @foreach ($pointOfUseStores as $pointOfUse)
                                                            <option value="{{ $pointOfUse->id }}"
                                                                {{ $pointOfUse->id == $transfer->to_location_id ? 'selected' : '' }}>
                                                                {{ $pointOfUse->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @if (!$isSuperAdmin)
                                                        <input type="hidden" name="to_location_id" value="{{ $transfer->to_location_id }}">
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Collected By</label>
                                                    <input type="text" name="collected_by" class="form-control"
                                                        value="{{ $transfer->collected_by }}" placeholder="Who collected the items?">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row invoice-info mb-4">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label>Remarks</label>
                                                    <input type="text" name="remarks" class="form-control"
                                                        value="{{ $transfer->remarks }}" placeholder="Optional remarks">
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
                                                    <tbody id="edit_items_{{ $transfer->id }}">
                                                        @foreach ($transfer->items as $index => $item)
                                                            @php
                                                                $product = $item->product;
                                                                $packSize = $product->default_pack_size;
                                                                $fullPkgs = floor($item->quantity / $packSize);
                                                                $extra = $item->quantity - ($fullPkgs * $packSize);
                                                            @endphp
                                                            <tr>
                                                                <td style="width: 180px;">
                                                                    <select name="items[{{ $index }}][product_id]" class="form-control product-select" required>
                                                                        <option value="{{ $item->product_id }}"
                                                                            data-pack-size="{{ $product->default_pack_size }}"
                                                                            data-packaging-type="{{ $product->packaging_type ?? 'pack' }}">
                                                                            {{ optional($item->product)->name ?? 'N/A' }}
                                                                        </option>
                                                                        @foreach ($products as $product)
                                                                            <option value="{{ $product->id }}"
                                                                                data-pack-size="{{ $product->default_pack_size }}"
                                                                                data-packaging-type="{{ $product->packaging_type ?? 'pack' }}"
                                                                                {{ $product->id == $item->product_id ? 'selected' : '' }}>
                                                                                {{ $product->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="number" name="items[{{ $index }}][full_packages]"
                                                                        class="form-control packages-input"
                                                                        value="{{ $fullPkgs }}" min="0" step="1" required>
                                                                </td>
                                                                <td>
                                                                    <input type="number" name="items[{{ $index }}][extra_units]"
                                                                        class="form-control extra-input"
                                                                        value="{{ $extra }}" min="0" step="1">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control pack-size-display"
                                                                        value="{{ $packSize }}" readonly>
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="items[{{ $index }}][quantity]"
                                                                        class="form-control quantity-display"
                                                                        value="{{ $item->quantity }}" readonly>
                                                                </td>
                                                                <td>
                                                                    <button type="button" class="remove-edit-tr btn btn-danger btn-sm"><b>X</b></button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <button type="button" class="btn btn-success btn-sm add-edit-row"
                                                    data-target="edit_items_{{ $transfer->id }}"
                                                    data-index="{{ count($transfer->items) }}">
                                                    <i class="fa fa-plus-circle"></i> Add Product
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row no-print">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success float-right">
                                                    Update Transfer
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- REJECT MODALS --}}
        @foreach ($transfers as $transfer)
            @if ($transfer->status == 'pending')
                <div class="modal fade" id="rejectModal-{{ $transfer->id }}">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Reject Transfer</h5>
                                <button type="button" class="close text-white" data-dismiss="modal">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('inventory-transfers.reject', $transfer->id) }}" method="POST">
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
            @endif
        @endforeach

        {{-- VIEW MODALS --}}
        @foreach ($transfers as $transfer)
            <div class="modal fade" id="viewTransferModal-{{ $transfer->id }}">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">Transfer Details - #{{ $transfer->id }}</h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="invoice p-3 mb-3">
                                <div class="row">
                                    <div class="col-12">
                                        <h4>
                                            <i class="fas fa-exchange-alt"></i> Transfer Request
                                            <small class="float-right">Requested: {{ \Carbon\Carbon::parse($transfer->requested_date)->toFormattedDateString() }}</small>
                                        </h4>
                                    </div>
                                </div>

                                <div class="row invoice-info mb-3">
                                    <div class="col-sm-4 invoice-col">
                                        <strong>From</strong>
                                        <address class="mb-0">{{ optional($transfer->fromLocation)->name ?? 'N/A' }}</address>
                                    </div>
                                    <div class="col-sm-4 invoice-col">
                                        <strong>To</strong>
                                        <address class="mb-0">{{ optional($transfer->toLocation)->name ?? 'N/A' }}</address>
                                    </div>
                                    <div class="col-sm-4 invoice-col">
                                        <strong>Status</strong>
                                        <address class="mb-0"><span class="badge badge-{{ $statusClass }}">{{ ucfirst($transfer->status) }}</span></address>
                                    </div>
                                </div>

                                <div class="row invoice-info mb-3">
                                    <div class="col-sm-4 invoice-col">
                                        <strong>Requested By</strong>
                                        <address class="mb-0">{{ optional($transfer->requestedBy)->name ?? 'N/A' }}</address>
                                    </div>
                                    <div class="col-sm-4 invoice-col">
                                        <strong>Approved By</strong>
                                        <address class="mb-0">{{ optional($transfer->approvedBy)->name ?? 'N/A' }}</address>
                                    </div>
                                    <div class="col-sm-4 invoice-col">
                                        <strong>Collected By</strong>
                                        <address class="mb-0">{{ $transfer->collected_by ?? 'N/A' }}</address>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 table-responsive">
                                        <table class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Product</th>
                                                    <th>Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($transfer->items as $i => $item)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ optional($item->product)->name ?? 'N/A' }}</td>
                                                        <td>{{ $item->quantity }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="text-center text-muted">No items</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                @if ($transfer->remarks)
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="lead">Remarks</p>
                                            <p class="text-muted">{{ $transfer->remarks }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div id="transfer-data"
             data-products='{{ json_encode($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'pack_size' => $p->default_pack_size, 'packaging_type' => $p->packaging_type ?? 'pack'])->toArray()) }}'>
        </div>

        <script type="text/javascript">
            (function() {
                var dataEl = document.getElementById('transfer-data');
                var products = JSON.parse(dataEl.dataset.products || '[]');

                function getProductPackSize(productId) {
                    var found = products.find(function(p) { return p.id == productId; });
                    return found ? found.pack_size : 0;
                }

                function getProductPackagingType(productId) {
                    var found = products.find(function(p) { return p.id == productId; });
                    return found ? found.packaging_type : 'pack';
                }

                function updatePackSize(row) {
                    var select = row.querySelector('.product-select');
                    var display = row.querySelector('.pack-size-display');
                    if (!select || !display) return;
                    var productId = select.value;
                    display.value = getProductPackSize(productId);
                }

                function updatePackagesDisabled(row) {
                    var select = row.querySelector('.product-select');
                    var packagesInput = row.querySelector('.packages-input');
                    if (!select || !packagesInput) return;
                    var productId = select.value;
                    var pType = getProductPackagingType(productId);
                    if (pType === 'unit') {
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
                    var total = (packages * packSize) + extra;
                    quantityDisplay.value = total.toFixed(0);
                }

                function initRow(row) {
                    updatePackSize(row);
                    updatePackagesDisabled(row);
                    updateQuantity(row);

                    var packagesInput = row.querySelector('.packages-input');
                    var extraInput = row.querySelector('.extra-input');
                    var productSelect = row.querySelector('.product-select');

                    if (packagesInput) packagesInput.addEventListener('input', function() { updateQuantity(row); });
                    if (extraInput) extraInput.addEventListener('input', function() { updateQuantity(row); });
                    if (productSelect) productSelect.addEventListener('change', function() {
                        updatePackSize(row);
                        updatePackagesDisabled(row);
                        updateQuantity(row);
                    });
                }

                var createRows = document.querySelectorAll('#add_items tr');
                createRows.forEach(function(row) { initRow(row); });

                document.querySelector('.add-row')?.addEventListener('click', function(e) {
                    var btn = e.currentTarget;
                    var rowCount = document.querySelectorAll('#add_items tr').length;

                    document.querySelectorAll('#add_items .add-row').forEach(function(el) { el.remove(); });

                    var newRow = document.createElement('tr');
                    var opts = '<option value="">Select</option>';
                    products.forEach(function(p) {
                        opts += '<option value="' + p.id + '" data-pack-size="' + p.pack_size + '" data-packaging-type="' + p.packaging_type + '">' + p.name + '</option>';
                    });

                    newRow.innerHTML = `
                        <td style="width: 180px;">
                            <select name="items[${rowCount}][product_id]" class="form-control product-select" required>
                                ${opts}
                            </select>
                        </td>
                        <td>
                            <input type="number" name="items[${rowCount}][full_packages]" class="form-control packages-input" value="0" min="0" step="1" required>
                        </td>
                        <td>
                            <input type="number" name="items[${rowCount}][extra_units]" class="form-control extra-input" value="0" min="0" step="1">
                        </td>
                        <td>
                            <input type="text" class="form-control pack-size-display" value="0" readonly>
                        </td>
                        <td>
                            <input type="text" name="items[${rowCount}][quantity]" class="form-control quantity-display" readonly>
                        </td>
                        <td>
                            <div class="d-flex">
                                <button type="button" class="remove-tr btn btn-danger btn-sm mr-1"><b>X</b></button>
                                <button type="button" class="btn btn-success btn-sm add-row"><i class="fa fa-plus-circle"></i></button>
                            </div>
                        </td>
                    `;
                    document.getElementById('add_items').appendChild(newRow);
                    initRow(newRow);
                });

                document.addEventListener('click', function(e) {
                    var target = e.target.closest('.remove-tr');
                    if (!target) return;
                    var rows = document.querySelectorAll('#add_items tr');
                    if (rows.length > 1) {
                        target.closest('tr').remove();
                        var lastRow = document.querySelector('#add_items tr:last');
                        if (lastRow && !lastRow.querySelector('.add-row')) {
                            var td = lastRow.querySelector('td:last-child');
                            if (td) {
                                td.innerHTML = `
                                    <div class="d-flex">
                                        <button type="button" class="remove-tr btn btn-danger btn-sm mr-1"><b>X</b></button>
                                        <button type="button" class="btn btn-success btn-sm add-row"><i class="fa fa-plus-circle"></i></button>
                                    </div>
                                `;
                            }
                        }
                    } else {
                        alert('You must have at least one product.');
                    }
                });

                function setupEditRows(containerId) {
                    var container = document.getElementById(containerId);
                    if (!container) return;
                    var rows = container.querySelectorAll('tr');
                    rows.forEach(function(row) {
                        var packagesInput = row.querySelector('.packages-input');
                        var extraInput = row.querySelector('.extra-input');
                        var productSelect = row.querySelector('.product-select');

                        if (packagesInput) packagesInput.addEventListener('input', function() { updateQuantity(row); });
                        if (extraInput) extraInput.addEventListener('input', function() { updateQuantity(row); });
                        if (productSelect) productSelect.addEventListener('change', function() {
                            updatePackSize(row);
                            updatePackagesDisabled(row);
                            updateQuantity(row);
                        });
                    });
                }

                document.addEventListener('shown.bs.modal', function(e) {
                    var modal = e.target;
                    if (modal.id && modal.id.startsWith('editTransferModal-')) {
                        var receiptId = modal.id.replace('editTransferModal-', '');
                        var containerId = 'edit_items_' + receiptId;
                        setupEditRows(containerId);
                    }
                });

                document.addEventListener('click', function(e) {
                    var target = e.target.closest('.add-edit-row');
                    if (!target) return;
                    var targetId = target.dataset.target;
                    var container = document.getElementById(targetId);
                    if (!container) return;
                    var rowCount = container.querySelectorAll('tr').length;
                    var idx = parseInt(target.dataset.index) || rowCount;

                    var opts = '<option value="">Select</option>';
                    products.forEach(function(p) {
                        opts += '<option value="' + p.id + '" data-pack-size="' + p.pack_size + '" data-packaging-type="' + p.packaging_type + '">' + p.name + '</option>';
                    });

                    var newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <td style="width: 180px;">
                            <select name="items[${idx}][product_id]" class="form-control product-select" required>
                                ${opts}
                            </select>
                        </td>
                        <td>
                            <input type="number" name="items[${idx}][full_packages]" class="form-control packages-input" value="0" min="0" step="1" required>
                        </td>
                        <td>
                            <input type="number" name="items[${idx}][extra_units]" class="form-control extra-input" value="0" min="0" step="1">
                        </td>
                        <td>
                            <input type="text" class="form-control pack-size-display" value="0" readonly>
                        </td>
                        <td>
                            <input type="text" name="items[${idx}][quantity]" class="form-control quantity-display" readonly>
                        </td>
                        <td>
                            <button type="button" class="remove-edit-tr btn btn-danger btn-sm"><b>X</b></button>
                        </td>
                    `;
                    container.appendChild(newRow);
                    initRow(newRow);
                    target.dataset.index = idx + 1;
                });

                document.addEventListener('click', function(e) {
                    var target = e.target.closest('.remove-edit-tr');
                    if (!target) return;
                    var container = target.closest('tbody');
                    if (!container) return;
                    var rows = container.querySelectorAll('tr');
                    if (rows.length > 1) {
                        target.closest('tr').remove();
                    } else {
                        alert('You must have at least one product.');
                    }
                });

                if (typeof $ !== 'undefined') {
                    $('#example1').DataTable({
                        responsive: true,
                        lengthChange: false,
                        autoWidth: false,
                        pageLength: 20,
                        buttons: ["csv", "excel", "pdf", "print"]
                    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
                }
            })();
        </script>
    </div>
</section>
@endsection