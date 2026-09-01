<div class="table-responsive">
    <table id="location_table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Product</th>
                <th>Location</th>
                <th>Quantity (units)</th>
                @if(isset($locationBalances) && $locationBalances->contains(fn($item) => $item->packaging_type === 'pack'))
                <th>Quantity (pack)</th>
                @endif
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($locationBalances ?? [] as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td>{{ $item->location->name ?? 'N/A' }}</td>
                    <td>{{ $item->quantity_units ?? '0' }}</td>
                    @if(isset($locationBalances) && $locationBalances->contains(fn($i) => $i->packaging_type === 'pack'))
                    <td>{{ $item->quantity_pack_display ?? '-' }}</td>
                    @endif
                    <td>{{ isset($item->last_updated) ? \Carbon\Carbon::parse($item->last_updated)->format('Y-m-d H:i') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No stock data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
$(function() {
    $('#location_table').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        pageLength: 20,
        buttons: ["csv", "excel", "pdf", "print"],
        order: [[4, 'desc']]
    }).buttons().container().appendTo('#location_table_wrapper .col-md-6:eq(0)');
});
</script>
@endpush