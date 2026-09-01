<div class="table-responsive">
    <table id="overall_table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Product</th>
                <th>Total Quantity (units)</th>
                @if(isset($overallBalances) && $overallBalances->contains(fn($item) => $item->packaging_type === 'pack'))
                <th>Total Quantity (pack)</th>
                @endif
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($overallBalances ?? [] as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td>{{ $item->quantity_units ?? '0' }}</td>
                    @if(isset($overallBalances) && $overallBalances->contains(fn($i) => $i->packaging_type === 'pack'))
                    <td>{{ $item->quantity_pack_display ?? '-' }}</td>
                    @endif
                    <td>{{ isset($item->last_updated) ? \Carbon\Carbon::parse($item->last_updated)->format('Y-m-d H:i') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No stock data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
$(function() {
    $('#overall_table').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        pageLength: 20,
        buttons: ["csv", "excel", "pdf", "print"],
        order: [[3, 'desc']]
    }).buttons().container().appendTo('#overall_table_wrapper .col-md-6:eq(0)');
});
</script>
@endpush