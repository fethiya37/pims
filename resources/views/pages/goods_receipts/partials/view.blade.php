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
        <div class="col-sm-3">
            <strong>Location</strong>
            <address class="mb-0">{{ optional($receipt->location)->name ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-3">
            <strong>Supplier</strong>
            <address class="mb-0">{{ optional($receipt->supplier)->name ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-3">
            <strong>Reference</strong>
            <address class="mb-0">{{ $receipt->reference_number ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-3">
            <strong>Delivered By</strong>
            <address class="mb-0">{{ $receipt->delivered_by ?? 'N/A' }}</address>
        </div>
    </div>

    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>#</th><th>Product</th><th>Lot #</th><th>Expiry</th><th>Qty</th>
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

<div class="modal-footer justify-content-between">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    @if ($receipt->status == 'draft')
        <a href="{{ route('goods-receipts.receive', $receipt->id) }}" class="btn btn-success"
            onclick="return confirm('Mark this receipt as received?');">
            Receive
        </a>
    @endif
</div>