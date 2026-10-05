<form action="{{ route('inventory-adjustments.update', $adjustment->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="invoice p-3 mb-3">
        <div class="row">
            <div class="col-12">
                <h4>
                    <i class="fas fa-sliders-h"></i> Edit Adjustment Request #{{ $adjustment->id }}
                </h4>
            </div>
        </div>

        <div class="row invoice-info mb-4">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Location</label>
                    <select name="location_id" class="form-control" required
                        {{ !$isSuperAdmin ? 'disabled' : '' }}>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}"
                                {{ $location->id == $adjustment->location_id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                    @if (!$isSuperAdmin)
                        <input type="hidden" name="location_id" value="{{ $adjustment->location_id }}">
                    @endif
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Reason</label>
                    <input type="text" name="reason" class="form-control"
                        value="{{ $adjustment->reason }}" placeholder="Why is this adjustment needed?">
                </div>
            </div>
        </div>

        <div id="edit_items_{{ $adjustment->id }}">
            @foreach ($adjustment->items as $index => $item)
                @php
                    $product = $item->product;
                    $packSize = $product->default_pack_size;
                    $isUnitType = $product->packaging_type === 'unit';

                    if ($isUnitType) {
                        $fullPkgs = 0;
                        $extra = $item->unit ?? $item->quantity;
                    } else {
                        $fullPkgs = $item->package ?? floor($item->quantity / $packSize);
                        $extra = $item->unit ?? ($item->quantity - ($fullPkgs * $packSize));
                    }
                @endphp
                <div class="card card-outline card-secondary adjustment-item mb-2" data-index="{{ $index }}">
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small mb-1">Product</label>
                                    <select name="items[{{ $index }}][product_id]" class="form-control product-select" required>
                                        <option value="">Select</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}"
                                                data-pack-size="{{ $product->default_pack_size }}"
                                                data-packaging-type="{{ $product->packaging_type ?? 'pack' }}"
                                                {{ $product->id == $item->product_id ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="small mb-1">Type</label>
                                    <select name="items[{{ $index }}][adjustment_type]" class="form-control">
                                        <option value="IN" {{ $item->adjustment_type === 'IN' ? 'selected' : '' }}>Stock In</option>
                                        <option value="OUT" {{ $item->adjustment_type === 'OUT' ? 'selected' : '' }}>Stock Out</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="small mb-1">Lot / Batch No</label>
                                    <input type="text" name="items[{{ $index }}][lot_number]" class="form-control" value="{{ $item->lot_number }}" placeholder="Optional">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="small mb-1">Expiry Date</label>
                                    <input type="date" name="items[{{ $index }}][expiry_date]" class="form-control" required
                                        value="{{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('Y-m-d') : '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="small mb-1">Full Packages</label>
                                    <input type="number" name="items[{{ $index }}][full_packages]" class="form-control packages-input"
                                        value="{{ $fullPkgs }}" min="0" step="1" required
                                        {{ $isUnitType ? 'readonly' : '' }}>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="small mb-1">Units</label>
                                    <input type="number" name="items[{{ $index }}][extra_units]" class="form-control extra-input"
                                        value="{{ $extra }}" min="0" step="1">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="small mb-1">Pack Size</label>
                                    <input type="text" class="form-control pack-size-display" value="{{ $packSize }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="small mb-1">Total Quantity</label>
                                    <input type="text" name="items[{{ $index }}][quantity]" class="form-control quantity-display"
                                        value="{{ $item->quantity }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3 text-right">
                                <button type="button" class="btn btn-danger btn-sm remove-edit-card">
                                    <i class="fa fa-times"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" class="btn btn-success btn-sm add-edit-card"
            data-target="edit_items_{{ $adjustment->id }}">
            <i class="fa fa-plus-circle"></i> Add Another Product
        </button>

        <div class="row no-print mt-3">
            <div class="col-12">
                <button type="submit" class="btn btn-success float-right">Update Request</button>
            </div>
        </div>
    </div>
</form>