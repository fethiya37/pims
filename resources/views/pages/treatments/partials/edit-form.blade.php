<form action="{{ route('treatments.update', $consumption->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="invoice p-3 mb-3">
        <div class="row">
            <div class="col-12">
                <h4>
                    <i class="fas fa-user-md"></i> Edit Treatment #{{ $consumption->id }}
                    <small class="float-right">
                        Created: {{ \Carbon\Carbon::parse($consumption->created_at)->toFormattedDateString() }}
                    </small>
                </h4>
            </div>
        </div>

        <div class="row invoice-info mb-4">
            <div class="col-sm-4">
                <div class="form-group">
                    <label>Patient</label>
                    <select name="patient_id" class="form-control">
                        <option value="">Select Patient</option>
                        @foreach ($patients as $patient)
                            <option value="{{ $patient->id }}"
                                {{ $patient->id == $consumption->patient_id ? 'selected' : '' }}>
                                {{ $patient->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label>Location *</label>
                    <select name="location_id" class="form-control" required
                        {{ !$isSuperAdmin ? 'disabled' : '' }}>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}"
                                {{ $location->id == $consumption->location_id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                    @if (!$isSuperAdmin)
                        <input type="hidden" name="location_id" value="{{ $consumption->location_id }}">
                    @endif
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label>Treatment Date</label>
                    <input type="date" name="treatment_date" class="form-control"
                        value="{{ $consumption->treatment_date ? \Carbon\Carbon::parse($consumption->treatment_date)->format('Y-m-d') : '' }}">
                </div>
            </div>
        </div>

        <div class="row invoice-info mb-4">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Diagnosis</label>
                    <input type="text" name="diagnosis" class="form-control"
                        value="{{ $consumption->diagnosis }}" placeholder="Diagnosis">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Notes</label>
                    <input type="text" name="notes" class="form-control"
                        value="{{ $consumption->notes }}" placeholder="Notes">
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
                    <tbody id="edit_items_{{ $consumption->id }}">
                        @foreach ($consumption->items as $index => $item)
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
                    data-target="edit_items_{{ $consumption->id }}"
                    data-index="{{ count($consumption->items) }}">
                    <i class="fa fa-plus-circle"></i> Add Product
                </button>
            </div>
        </div>

        <div class="row no-print">
            <div class="col-12">
                <button type="submit" class="btn btn-success float-right">Update Treatment</button>
            </div>
        </div>
    </div>
</form>