@extends('inc.frame')

@section('content')
<div class="container-fluid px-4">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Inventory Adjustments</h3>
            <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#adjustmentModal">
                <i class="fas fa-plus"></i> Create Adjustment
            </button>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Location</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Lot #</th>
                            <th>Quantity</th>
                            <th>Expiry</th>
                            <th>Reason</th>
                            <th>User</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($adjustments as $adjustment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $adjustment->location->name }}</td>
                                <td>{{ $adjustment->product->name }}</td>
                                <td>
                                    @if ($adjustment->adjustment_type === 'IN')
                                        <span class="badge badge-success">Stock In</span>
                                    @else
                                        <span class="badge badge-danger">Stock Out</span>
                                    @endif
                                </td>
                                <td>{{ $adjustment->lot_number ?? 'N/A' }}</td>
                                <td>{{ $adjustment->quantity }}</td>
                                <td>{{ $adjustment->expiry_date ?? 'N/A' }}</td>
                                <td>{{ $adjustment->reason ?? '-' }}</td>
                                <td>{{ $adjustment->user->name ?? '-' }}</td>
                                <td>{{ $adjustment->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Adjustment Modal -->
<div class="modal fade" id="adjustmentModal" tabindex="-1" aria-labelledby="adjustmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="adjustmentModalLabel">New Inventory Adjustment</h5>
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
                                <label>Product</label>
                                <select name="product_id" class="form-control" required>
                                    <option value="">Select Product</option>
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
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Adjustment Type</label>
                                <select name="adjustment_type" class="form-control" required>
                                    <option value="IN">Stock In</option>
                                    <option value="OUT">Stock Out</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Lot/Batch No</label>
                                <input type="text" name="lot_number" class="form-control" placeholder="Optional">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Full Packages</label>
                                <input type="number" name="full_packages" class="form-control packages-input" value="0" min="0" step="1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Units</label>
                                <input type="number" name="extra_units" class="form-control extra-input" value="0" min="0" step="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Pack Size</label>
                                <input type="text" class="form-control pack-size-display" value="0" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Total Quantity (units)</label>
                                <input type="text" name="quantity" class="form-control quantity-display" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Reason</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Why is this adjustment needed?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.close') }}</button>
                    <button type="submit" class="btn btn-primary">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        var packSize = 0;
        var packagingType = 'pack';

        function getProductPackSize(productId) {
            var select = document.querySelector('select[name="product_id"]');
            var option = select.querySelector('option[value="' + productId + '"]');
            return option ? parseInt(option.dataset.packSize) || 0 : 0;
        }

        function getProductPackagingType(productId) {
            var select = document.querySelector('select[name="product_id"]');
            var option = select.querySelector('option[value="' + productId + '"]');
            return option ? option.dataset.packagingType || 'pack' : 'pack';
        }

        function updatePackSize() {
            var select = document.querySelector('select[name="product_id"]');
            var display = document.querySelector('.pack-size-display');
            if (!select || !display) return;
            var productId = select.value;
            packSize = getProductPackSize(productId);
            packagingType = getProductPackagingType(productId);
            display.value = packSize;
        }

        function updatePackagesDisabled() {
            var packagesInput = document.querySelector('.packages-input');
            if (!packagesInput) return;
            if (packagingType === 'unit') {
                packagesInput.readOnly = true;
                packagesInput.value = 0;
            } else {
                packagesInput.readOnly = false;
            }
        }

        function updateQuantity() {
            var packagesInput = document.querySelector('.packages-input');
            var extraInput = document.querySelector('.extra-input');
            var quantityDisplay = document.querySelector('.quantity-display');
            if (!packagesInput || !extraInput || !quantityDisplay) return;

            var packages = parseInt(packagesInput.value) || 0;
            var extra = parseFloat(extraInput.value) || 0;
            var total = (packages * packSize) + extra;
            quantityDisplay.value = total.toFixed(0);
        }

        function refreshFields() {
            updatePackSize();
            updatePackagesDisabled();
            updateQuantity();
        }

        document.addEventListener('DOMContentLoaded', function() {
            var productSelect = document.querySelector('select[name="product_id"]');
            var packagesInput = document.querySelector('.packages-input');
            var extraInput = document.querySelector('.extra-input');

            if (productSelect) {
                productSelect.addEventListener('change', refreshFields);
                // Initial
                refreshFields();
            }

            if (packagesInput) packagesInput.addEventListener('input', updateQuantity);
            if (extraInput) extraInput.addEventListener('input', updateQuantity);
        });
    })();
</script>
@endsection