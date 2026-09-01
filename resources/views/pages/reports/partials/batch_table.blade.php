<div class="table-responsive">
    <table id="batch_table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Product</th>
                <th>Location</th>
                <th>Lot #</th>
                <th>Expiry</th>
                <th>Quantity (units)</th>
                @if(isset($batchBalances) && $batchBalances->contains(fn($item) => $item->packaging_type === 'pack'))
                <th>Quantity (pack)</th>
                @endif
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($batchBalances ?? [] as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td>{{ $item->location->name ?? 'N/A' }}</td>
                    <td>{{ $item->lot_number ?? 'N/A' }}</td>
                    <td>{{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ $item->quantity_units ?? '0' }}</td>
                    @if(isset($batchBalances) && $batchBalances->contains(fn($i) => $i->packaging_type === 'pack'))
                    <td>{{ $item->quantity_pack_display ?? '-' }}</td>
                    @endif
                    <td>{{ isset($item->updated_at) ? \Carbon\Carbon::parse($item->updated_at)->format('Y-m-d H:i') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No stock data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
$(function() {
    $('#batch_table').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        pageLength: 20,
        buttons: ["csv", "excel", "pdf", "print"],
        order: [[6, 'desc']]
    }).buttons().container().appendTo('#batch_table_wrapper .col-md-6:eq(0)');
});
</script>
@endpush