@extends('inc.frame')
@section('content')
<div class="container-fluid">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Reorder Level Settings for <b>{{ $product->name }}</b></h3>
            <a href="{{ route('products.index') }}" class="btn btn-secondary float-right">Back to Products</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                @if ($product->packaging_type === 'pack')
                    Set the minimum stock level in <strong>packs</strong> (each pack contains {{ $product->default_pack_size }} {{ $product->unit }}).
                    When the available stock falls below this level, a low stock alert will be triggered.
                @else
                    Set the minimum stock level in <strong>{{ $product->unit }}</strong> units.
                    When the available stock falls below this level, a low stock alert will be triggered.
                @endif
            </div>

            <form action="{{ route('products.reorder-settings.store', $product->id) }}" method="POST">
                @csrf

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Location</th>
                                @if($product->packaging_type === 'pack')
                                <th>Pack Size</th>
                                @endif
                                <th>Current Stock (unit)</th>
                                @if($product->packaging_type === 'pack')
                                <th>Current Stock (pack)</th>
                                @endif
                                @if($product->packaging_type === 'pack')
                                <th>Reorder Level (pack)</th>
                                <th>Reorder Level (units)</th>
                                @else
                                <th>Reorder Level (unit)</th>
                                @endif
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($locations as $location)
                                @php
                                    $currentStock = \App\Models\StockBatch::where('product_id', $product->id)
                                        ->where('location_id', $location->id)
                                        ->sum('quantity');

                                    $reorderQtyUnits = $settings[$location->id]->reorder_quantity ?? 0;

                                    if ($product->packaging_type === 'pack') {
                                        $packSize = $product->default_pack_size ?? 1;
                                        $fullPacks = floor($currentStock / $packSize);
                                        $extraUnits = $currentStock % $packSize;
                                        $currentStockUnitDisplay = $currentStock . ' ' . $product->unit;
                                        $currentStockPackDisplay = $fullPacks . ' pack' . ($fullPacks != 1 ? 's' : '') .
                                            ($extraUnits > 0 ? ' + ' . $extraUnits . ' ' . $product->unit : '');
                                        $reorderLevelPacks = floor($reorderQtyUnits / $packSize);
                                        $isLowStock = ($currentStock <= $reorderQtyUnits) && $currentStock > 0;
                                    } else {
                                        $currentStockUnitDisplay = $currentStock . ' ' . $product->unit;
                                        $reorderLevelPacks = $reorderQtyUnits;
                                        $isLowStock = ($currentStock <= $reorderQtyUnits) && $currentStock > 0;
                                    }
                                    $isOutOfStock = $currentStock == 0;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $location->name }}</strong>
                                        <small class="text-muted d-block">{{ $location->type }}</small>
                                    </td>
                                    @if($product->packaging_type === 'pack')
                                    <td>{{ $product->default_pack_size }}</td>
                                    @endif
                                    <td>
                                        <span class="badge badge-{{ $isOutOfStock ? 'danger' : ($isLowStock ? 'warning' : 'success') }}">
                                            {{ $currentStockUnitDisplay }}
                                        </span>
                                    </td>
                                    @if($product->packaging_type === 'pack')
                                    <td>
                                        <span class="badge badge-{{ $isOutOfStock ? 'danger' : ($isLowStock ? 'warning' : 'success') }}">
                                            {{ $currentStockPackDisplay }}
                                        </span>
                                    </td>
                                    @endif

                                    {{-- Reorder Level column(s) --}}
                                    @if($product->packaging_type === 'pack')
                                    <td>
                                        <input type="hidden" name="reorder_levels[{{ $loop->index }}][location_id]" value="{{ $location->id }}">
                                        <input type="number"
                                               name="reorder_levels[{{ $loop->index }}][reorder_quantity]"
                                               class="form-control reorder-input"
                                               min="0"
                                               step="1"
                                               value="{{ $reorderLevelPacks }}"
                                               style="width: 120px;"
                                               data-pack-size="{{ $product->default_pack_size }}">
                                        <small class="text-muted">in packs</small>
                                    </td>
                                    <td>
                                        <span class="reorder-units-display badge badge-secondary" style="font-size: 1em;">
                                            {{ $reorderLevelPacks * $product->default_pack_size }} {{ $product->unit }}
                                        </span>
                                    </td>
                                    @else
                                    <td>
                                        <input type="hidden" name="reorder_levels[{{ $loop->index }}][location_id]" value="{{ $location->id }}">
                                        <input type="number"
                                               name="reorder_levels[{{ $loop->index }}][reorder_quantity]"
                                               class="form-control"
                                               min="0"
                                               step="1"
                                               value="{{ $reorderQtyUnits }}"
                                               style="width: 120px;">
                                        <small class="text-muted">in {{ $product->unit }}</small>
                                    </td>
                                    @endif

                                    <td>
                                        @if ($isOutOfStock)
                                            <span class="badge badge-danger">OUT OF STOCK</span>
                                        @elseif ($isLowStock)
                                            <span class="badge badge-warning">Low Stock</span>
                                        @elseif ($reorderQtyUnits > 0)
                                            <span class="badge badge-success">OK</span>
                                        @else
                                            <span class="badge badge-secondary">Not Set</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Reorder Levels
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var packagingType = '{{ $product->packaging_type }}';
        var unit = '{{ $product->unit }}';

        if (packagingType === 'pack') {
            document.querySelectorAll('.reorder-input').forEach(function(input) {
                var packSize = parseInt(input.dataset.packSize) || 1;
                var unitsDisplay = input.closest('tr').querySelector('.reorder-units-display');

                function updateUnits() {
                    var packs = parseInt(input.value) || 0;
                    var units = packs * packSize;
                    unitsDisplay.textContent = units + ' ' + unit;
                }

                input.addEventListener('input', updateUnits);
                updateUnits();
            });
        }
    });
</script>
@endsection