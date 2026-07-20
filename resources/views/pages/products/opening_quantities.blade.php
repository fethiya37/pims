@extends('inc.frame')
@section('content')
<div class="container-fluid">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Opening Quantities for <b>{{ $product->name }}</b></h3>
            <a href="{{ route('products.index') }}" class="btn btn-secondary float-right">Back to Products</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Lot/Batch No</th>
                            @if($product->packaging_type === 'pack')
                            <th>Packages</th>
                            <th>Units</th>
                            @endif
                            <th>Total Quantity</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($product->openingQuantities as $oq)
                            <tr>
                                <td>{{ $oq->location->name }}</td>
                                <td>{{ $oq->lot_number ?? 'N/A' }}</td>
                                @if($product->packaging_type === 'pack')
                                <td>{{ $oq->package ?? 0 }}</td>
                                <td>{{ ($oq->quantity - ($oq->package ?? 0) * $product->default_pack_size) > 0 ? ($oq->quantity - ($oq->package ?? 0) * $product->default_pack_size) : 0 }}</td>
                                @endif
                                <td><span class="badge badge-primary">{{ $oq->quantity }}</span></td>
                                <td>{{ $oq->expiry_date ?? 'N/A' }}</td>
                                <td>
                                    <form action="{{ route('products.opening-quantities.destroy', $oq->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this entry?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $product->packaging_type === 'pack' ? 7 : 5 }}" class="text-center">No opening quantities recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <hr>

            <h5>Add New Opening Quantity</h5>
            @if($product->packaging_type === 'pack')
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Enter <strong>Full Packages</strong> and <strong>Units</strong> (from opened packages).
                Total quantity is calculated automatically. Pack size: <strong>{{ $product->default_pack_size }}</strong> units/package.
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Enter the quantity in the base unit (<strong>{{ $product->unit }}</strong>).
            </div>
            @endif

            <form action="{{ route('products.opening-quantities.store', $product->id) }}" method="POST">
                @csrf
                <div id="entry-container">
                    @php $firstRow = 0; @endphp
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Location</label>
                                <select name="entries[{{ $firstRow }}][location_id]" class="form-control" required>
                                    <option value="">Select</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Lot/Batch No</label>
                                <input type="text" name="entries[{{ $firstRow }}][lot_number]" class="form-control" placeholder="Optional">
                            </div>
                        </div>
                        @if($product->packaging_type === 'pack')
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>Full Packages</label>
                                <input type="number" name="entries[{{ $firstRow }}][packages]" class="form-control packages-input" value="0" min="0" step="1" required>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>Units</label>
                                <input type="number" name="entries[{{ $firstRow }}][extra_units]" class="form-control extra-input" value="0" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>Pack Size</label>
                                <input type="text" class="form-control" value="{{ $product->default_pack_size }}" readonly>
                            </div>
                        </div>
                        @else
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Quantity ({{ $product->unit }})</label>
                                <input type="number" name="entries[{{ $firstRow }}][quantity]" class="form-control quantity-input" value="0" min="0" step="0.01" required>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Total Quantity</label>
                                <input type="text" name="entries[{{ $firstRow }}][quantity]" class="form-control quantity-display" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Expiry Date</label>
                                <input type="date" name="entries[{{ $firstRow }}][expiry_date]" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="additional-entries"></div>

                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-more-row">
                        <i class="fas fa-plus"></i> Add More
                    </button>
                    <button type="submit" class="btn btn-sm btn-success float-right">
                        <i class="fas fa-save"></i> Save All
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="opening-quantities-data"
     data-pack-size="{{ $product->default_pack_size }}"
     data-packaging-type="{{ $product->packaging_type }}"
     data-unit="{{ $product->unit }}"
     data-locations='{{ json_encode($locations->map(fn($loc) => ['id' => $loc->id, 'name' => $loc->name])->toArray()) }}'>
</div>

<script>
(function() {
    var dataEl = document.getElementById('opening-quantities-data');
    var packSize = parseInt(dataEl.dataset.packSize) || 1;
    var packagingType = dataEl.dataset.packagingType || 'unit';
    var unit = dataEl.dataset.unit || '';
    var locations = JSON.parse(dataEl.dataset.locations || '[]');
    var index = 1;

    function buildLocationOptions(selectedId) {
        var html = '<option value="">Select</option>';
        selectedId = selectedId || '';
        locations.forEach(function(loc) {
            var selected = (loc.id == selectedId) ? 'selected' : '';
            html += '<option value="' + loc.id + '" ' + selected + '>' + loc.name + '</option>';
        });
        return html;
    }

    function updateQuantity(row) {
        var display = row.querySelector('.quantity-display');
        if (packagingType === 'pack') {
            var packagesInput = row.querySelector('.packages-input');
            var extraInput = row.querySelector('.extra-input');
            var packages = parseFloat(packagesInput.value) || 0;
            var extra = parseFloat(extraInput.value) || 0;
            var total = (packages * packSize) + extra;
            display.value = total.toFixed(2);
        } else {
            var qtyInput = row.querySelector('.quantity-input');
            var qty = parseFloat(qtyInput.value) || 0;
            display.value = qty.toFixed(2);
        }
    }

    function initRow(row) {
        if (packagingType === 'pack') {
            var packagesInput = row.querySelector('.packages-input');
            var extraInput = row.querySelector('.extra-input');
            packagesInput.addEventListener('input', function() { updateQuantity(row); });
            extraInput.addEventListener('input', function() { updateQuantity(row); });
        } else {
            var qtyInput = row.querySelector('.quantity-input');
            qtyInput.addEventListener('input', function() { updateQuantity(row); });
        }
        updateQuantity(row);
    }

    document.querySelectorAll('#entry-container .row').forEach(function(row) {
        initRow(row);
    });

    document.getElementById('add-more-row').addEventListener('click', function() {
        var container = document.getElementById('additional-entries');
        var row = document.createElement('div');
        row.className = 'row mt-2';
        var html = '';
        html += '<div class="col-md-2"><div class="form-group"><select name="entries[' + index + '][location_id]" class="form-control" required>' +
                buildLocationOptions() +
                '</select></div></div>';
        html += '<div class="col-md-2"><div class="form-group"><input type="text" name="entries[' + index + '][lot_number]" class="form-control" placeholder="Optional"></div></div>';
        if (packagingType === 'pack') {
            html += '<div class="col-md-1"><div class="form-group"><input type="number" name="entries[' + index + '][packages]" class="form-control packages-input" value="0" min="0" step="1" required></div></div>';
            html += '<div class="col-md-1"><div class="form-group"><input type="number" name="entries[' + index + '][extra_units]" class="form-control extra-input" value="0" min="0" step="0.01"></div></div>';
            html += '<div class="col-md-1"><div class="form-group"><input type="text" class="form-control" value="' + packSize + '" readonly></div></div>';
        } else {
            html += '<div class="col-md-2"><div class="form-group"><input type="number" name="entries[' + index + '][quantity]" class="form-control quantity-input" value="0" min="0" step="0.01" required></div></div>';
        }
        html += '<div class="col-md-2"><div class="form-group"><input type="text" name="entries[' + index + '][quantity]" class="form-control quantity-display" readonly></div></div>';
        html += '<div class="col-md-2"><div class="form-group"><input type="date" name="entries[' + index + '][expiry_date]" class="form-control" required></div></div>';
        html += '<div class="col-md-1"><button type="button" class="btn btn-danger btn-sm remove-row" style="margin-top:2px;"><i class="fas fa-times"></i></button></div>';
        row.innerHTML = html;
        container.appendChild(row);
        initRow(row);
        index++;
    });

    document.addEventListener('click', function(e) {
        var target = e.target.closest('.remove-row');
        if (target) {
            var row = target.closest('.row');
            var container = row.parentNode;
            if (container.children.length > 1) {
                row.remove();
            } else {
                alert('You must have at least one row.');
            }
        }
    });
})();
</script>
@endsection