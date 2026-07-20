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
                                <b>Goods Receipts: {{ $receipts->count() }}</b>
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
                        <table id="example1" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Receipt #</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Supplier</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($receipts as $index => $receipt)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $receipt->reference_number ?? '#' . $receipt->id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($receipt->receipt_date)->toFormattedDateString() }}
                                        </td>
                                        <td>{{ optional($receipt->location)->name ?? 'N/A' }}</td>
                                        <td>{{ optional($receipt->supplier)->name ?? 'N/A' }}</td>
                                        <td>
                                            @if ($receipt->items->count() > 0)
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Product</th>
                                                            <th>Lot #</th>
                                                            <th>Qty</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($receipt->items as $item)
                                                            <tr>
                                                                <td>{{ $item->product->name }}</td>
                                                                <td>{{ $item->lot_number ?? 'N/A' }}</td>
                                                                <td>{{ $item->quantity }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <span class="text-muted">No items</span>
                                            @endif
                                        </td>
                                        <td><span class="badge badge-{{ $receipt->status == 'received' ? 'success' : ($receipt->status == 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($receipt->status) }}</span></td>
                                        <td>
                                            @if ($receipt->status == 'draft')
                                                <a type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                                    data-target="#editModal-{{ $receipt->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('goods-receipts.destroy', $receipt->id) }}"
                                                    class="btn btn-danger btn-sm delete-btn"
                                                    onclick="return confirm('Delete this receipt?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <a href="{{ route('goods-receipts.receive', $receipt->id) }}"
                                                    class="btn btn-success btn-sm"
                                                    onclick="return confirm('Mark this receipt as received?');">
                                                    Receive
                                                </a>
                                            @endif
                                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal"
                                                data-target="#viewModal-{{ $receipt->id }}">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No receipts found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- CREATE MODAL --}}
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

                                {{-- Lines Table (Create) --}}
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

        {{-- EDIT MODALS --}}
        @foreach ($receipts as $receipt)
            @if ($receipt->status == 'draft')
                <div class="modal fade edit-modal-class" id="editModal-{{ $receipt->id }}">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Goods Receipt - #{{ $receipt->id }}</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="{{ route('goods-receipts.update', $receipt->id) }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="invoice p-3 mb-3">
                                        <div class="row">
                                            <div class="col-12">
                                                <h4>
                                                    <i class="fas fa-globe"></i> Prime Medicare PLC
                                                    <small class="float-right">Receipt Date:
                                                        {{ \Carbon\Carbon::parse($receipt->receipt_date)->toFormattedDateString() }}</small>
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
                                                                {{ $location->id == $receipt->location_id ? 'selected' : '' }}>
                                                                {{ $location->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if (!$isSuperAdmin)
                                                        <input type="hidden" name="location_id" value="{{ $receipt->location_id }}">
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Supplier</label>
                                                    <select name="supplier_id" class="form-control">
                                                        <option value="">Select Supplier</option>
                                                        @foreach ($suppliers as $supplier)
                                                            <option value="{{ $supplier->id }}"
                                                                {{ $supplier->id == $receipt->supplier_id ? 'selected' : '' }}>
                                                                {{ $supplier->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label>Receipt Date</label>
                                                    <input type="date" name="receipt_date" class="form-control"
                                                        required
                                                        value="{{ \Carbon\Carbon::parse($receipt->receipt_date)->format('Y-m-d') }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row invoice-info mb-4">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label>Reference Number</label>
                                                    <input type="text" name="reference_number" class="form-control"
                                                        value="{{ $receipt->reference_number }}"
                                                        placeholder="Supplier Invoice #">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label>Delivered By</label>
                                                    <input type="text" name="delivered_by" class="form-control"
                                                        value="{{ $receipt->delivered_by }}"
                                                        placeholder="Who delivered the goods?">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row invoice-info mb-4">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label>Notes</label>
                                                    <input type="text" name="notes" class="form-control"
                                                        value="{{ $receipt->notes }}" placeholder="Notes">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Lines Table (Edit) --}}
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
                                                    <tbody id="edit_items_{{ $receipt->id }}">
                                                        @foreach ($receipt->items as $index => $item)
                                                            @php
                                                                $product = $item->product;
                                                                $packSize = $product->default_pack_size;
                                                                $fullPkgs = floor($item->quantity / $packSize);
                                                                $extra = $item->quantity - ($fullPkgs * $packSize);
                                                            @endphp
                                                            <tr>
                                                                <td style="width: 180px;">
                                                                    <select
                                                                        name="items[{{ $index }}][product_id]"
                                                                        class="form-control product-select" required>
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
                                                                                {{ $product->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="text"
                                                                        name="items[{{ $index }}][lot_number]"
                                                                        class="form-control"
                                                                        value="{{ $item->lot_number }}"
                                                                        placeholder="Lot #">
                                                                </td>
                                                                <td>
                                                                    <input type="number"
                                                                        name="items[{{ $index }}][full_packages]"
                                                                        class="form-control packages-input"
                                                                        value="{{ $fullPkgs }}" min="0" step="1" required>
                                                                </td>
                                                                <td>
                                                                    <input type="number"
                                                                        name="items[{{ $index }}][extra_units]"
                                                                        class="form-control extra-input"
                                                                        value="{{ $extra }}" min="0" step="1">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control pack-size-display"
                                                                        value="{{ $packSize }}" readonly>
                                                                </td>
                                                                <td>
                                                                    <input type="text"
                                                                        name="items[{{ $index }}][quantity]"
                                                                        class="form-control quantity-display"
                                                                        value="{{ $item->quantity }}" readonly>
                                                                </td>
                                                                <td>
                                                                    <input type="date"
                                                                        name="items[{{ $index }}][expiry_date]"
                                                                        class="form-control" required
                                                                        value="{{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('Y-m-d') : '' }}">
                                                                </td>
                                                                <td>
                                                                    <button type="button"
                                                                        class="remove-edit-tr btn btn-danger btn-sm"><b>X</b></button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>

                                                <button type="button" class="btn btn-success btn-sm add-edit-row"
                                                    data-target="edit_items_{{ $receipt->id }}"
                                                    data-index="{{ count($receipt->items) }}">
                                                    <i class="fa fa-plus-circle"></i> Add Product
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row no-print">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-success float-right">
                                                    Update Receipt
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

        {{-- VIEW MODALS --}}
        @foreach ($receipts as $receipt)
            <div class="modal fade" id="viewModal-{{ $receipt->id }}">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">
                                View Goods Receipt #{{ $receipt->id }}
                                <small class="ml-2">
                                    <span class="badge badge-{{ $receipt->status == 'received' ? 'success' : ($receipt->status == 'cancelled' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($receipt->status) }}
                                    </span>
                                </small>
                            </h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="invoice p-3 mb-3">
                                <div class="row">
                                    <div class="col-12">
                                        <h4>
                                            <i class="fas fa-globe"></i> Prime Medicare PLC
                                            <small class="float-right">
                                                Date: {{ \Carbon\Carbon::parse($receipt->receipt_date)->toFormattedDateString() }}
                                            </small>
                                        </h4>
                                    </div>
                                </div>

                                <div class="row invoice-info mb-3">
                                    <div class="col-sm-3 invoice-col">
                                        <strong>Location</strong>
                                        <address class="mb-0">
                                            {{ optional($receipt->location)->name ?? 'N/A' }}
                                        </address>
                                    </div>
                                    <div class="col-sm-3 invoice-col">
                                        <strong>Supplier</strong>
                                        <address class="mb-0">
                                            {{ optional($receipt->supplier)->name ?? 'N/A' }}
                                        </address>
                                    </div>
                                    <div class="col-sm-3 invoice-col">
                                        <strong>Reference</strong>
                                        <address class="mb-0">
                                            {{ $receipt->reference_number ?? 'N/A' }}
                                        </address>
                                    </div>
                                    <div class="col-sm-3 invoice-col">
                                        <strong>Delivered By</strong>
                                        <address class="mb-0">
                                            {{ $receipt->delivered_by ?? 'N/A' }}
                                        </address>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 table-responsive">
                                        <table class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Product</th>
                                                    <th>Lot #</th>
                                                    <th>Expiry</th>
                                                    <th>Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($receipt->items as $i => $item)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ optional($item->product)->name ?? 'N/A' }}</td>
                                                        <td>{{ $item->lot_number ?? 'N/A' }}</td>
                                                        <td>{{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('Y-m-d') : 'N/A' }}</td>
                                                        <td>{{ $item->quantity }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="5" class="text-center text-muted">No items</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                @if ($receipt->notes)
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="lead">Notes</p>
                                            <p class="text-muted">{{ $receipt->notes }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            @if ($receipt->status == 'draft')
                                <a href="{{ route('goods-receipts.receive', $receipt->id) }}" class="btn btn-success"
                                    onclick="return confirm('Mark this receipt as received?');">
                                    Receive
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div id="goods-receipt-data"
             data-products='{{ json_encode($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'pack_size' => $p->default_pack_size, 'packaging_type' => $p->packaging_type ?? 'pack'])->toArray()) }}'>
        </div>

        <script type="text/javascript">
            (function() {
                var dataEl = document.getElementById('goods-receipt-data');
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

                // ---- CREATE MODAL ROWS ----
                var createRows = document.querySelectorAll('#add_items tr');
                createRows.forEach(function(row) { initRow(row); });

                // ---- ADD ROW (create modal) ----
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
                            <input type="text" name="items[${rowCount}][lot_number]" class="form-control" placeholder="Lot #">
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
                            <input type="date" name="items[${rowCount}][expiry_date]" class="form-control" required>
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

                // ---- REMOVE ROW (create modal) ----
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

                // ---- EDIT MODALS ----
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
                    if (!modal.classList.contains('edit-modal-class')) return;
                    var modalId = modal.id;
                    var receiptId = modalId.replace('editModal-', '');
                    var containerId = 'edit_items_' + receiptId;
                    setupEditRows(containerId);
                });

                // ---- ADD ROW (edit modal) ----
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
                            <input type="text" name="items[${idx}][lot_number]" class="form-control" placeholder="Lot #">
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
                            <input type="date" name="items[${idx}][expiry_date]" class="form-control" required>
                        </td>
                        <td>
                            <button type="button" class="remove-edit-tr btn btn-danger btn-sm"><b>X</b></button>
                        </td>
                    `;
                    container.appendChild(newRow);
                    initRow(newRow);
                    target.dataset.index = idx + 1;
                });

                // ---- REMOVE ROW (edit modal) ----
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

                // ---- DATA TABLES ----
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