<form action="{{ route('inventory-transfers.update', $transfer->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="invoice p-3 mb-3">
        <div class="row">
            <div class="col-12">
                <h4>
                    <i class="fas fa-exchange-alt"></i> Edit Transfer #{{ $transfer->id }}
                    <small class="float-right">
                        Requested: {{ \Carbon\Carbon::parse($transfer->requested_date)->toFormattedDateString() }}
                    </small>
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
                    <label>To (Sale / Point of Use)</label>
                    <select name="to_location_id" class="form-control" required
                        {{ !$isSuperAdmin ? 'disabled' : '' }}>
                        @foreach ($saleStores as $saleStore)
                            <option value="{{ $saleStore->id }}"
                                {{ $saleStore->id == $transfer->to_location_id ? 'selected' : '' }}>
                                {{ $saleStore->name }} (Sale)
                            </option>
                        @endforeach
                        @foreach ($pointOfUseStores as $pointOfUse)
                            <option value="{{ $pointOfUse->id }}"
                                {{ $pointOfUse->id == $transfer->to_location_id ? 'selected' : '' }}>
                                {{ $pointOfUse->name }} (Point of Use)
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
                                $isUnitType = $product->packaging_type === 'unit';

                                if ($isUnitType) {
                                    $fullPkgs = 0;
                                    $extra = $item->unit ?? $item->quantity;
                                } else {
                                    $fullPkgs = $item->package ?? floor($item->quantity / $packSize);
                                    $extra = $item->unit ?? ($item->quantity - ($fullPkgs * $packSize));
                                }
                            @endphp
                            <tr>
                                <td style="width: 180px;">
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
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][full_packages]"
                                        class="form-control packages-input"
                                        value="{{ $fullPkgs }}" min="0" step="1" required
                                        {{ $isUnitType ? 'readonly' : '' }}>
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
                <button type="submit" class="btn btn-success float-right">Update Transfer</button>
            </div>
        </div>
    </div>
</form>