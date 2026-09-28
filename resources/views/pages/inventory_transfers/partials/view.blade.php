<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-exchange-alt"></i> Transfer Request #{{ $transfer->id }}
                <small class="float-right">
                    Requested: {{ \Carbon\Carbon::parse($transfer->requested_date)->toFormattedDateString() }}
                </small>
            </h4>
        </div>
    </div>

    <div class="row invoice-info mb-3">
        <div class="col-sm-4">
            <strong>From</strong>
            <address class="mb-0">{{ optional($transfer->fromLocation)->name ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-4">
            <strong>To</strong>
            <address class="mb-0">{{ optional($transfer->toLocation)->name ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-4">
            <strong>Status</strong>
            <address class="mb-0"><span class="badge badge-{{ $statusClass }}">{{ ucfirst($transfer->status) }}</span></address>
        </div>
    </div>

    <div class="row invoice-info mb-3">
        <div class="col-sm-4">
            <strong>Requested By</strong>
            <address class="mb-0">{{ optional($transfer->requestedBy)->name ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-4">
            <strong>Approved By</strong>
            <address class="mb-0">{{ optional($transfer->approvedBy)->name ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-4">
            <strong>Collected By</strong>
            <address class="mb-0">{{ $transfer->collected_by ?? 'N/A' }}</address>
        </div>
    </div>

    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr><th>#</th><th>Product</th><th>Quantity</th></tr>
                </thead>
                <tbody>
                    @forelse ($transfer->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ optional($item->product)->name ?? 'N/A' }}</td>
                            <td>{{ $item->quantity }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">No items</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($transfer->remarks)
        <div class="row">
            <div class="col-12">
                <p class="lead">Remarks</p>
                <p class="text-muted">{{ $transfer->remarks }}</p>
            </div>
        </div>
    @endif
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>