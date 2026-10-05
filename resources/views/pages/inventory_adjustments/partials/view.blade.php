<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-sliders-h"></i> Adjustment Request #{{ $adjustment->id }}
                <small class="float-right">
                    Created: {{ \Carbon\Carbon::parse($adjustment->created_at)->toFormattedDateString() }}
                </small>
            </h4>
        </div>
    </div>

    <div class="row invoice-info mb-3">
        <div class="col-sm-3">
            <strong>Location</strong>
            <address class="mb-0">{{ optional($adjustment->location)->name ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-3">
            <strong>Status</strong>
            <address class="mb-0"><span class="badge badge-{{ $statusClass }}">{{ ucfirst($adjustment->status) }}</span></address>
        </div>
        <div class="col-sm-3">
            <strong>Requested By</strong>
            <address class="mb-0">{{ optional($adjustment->requestedBy)->name ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-3">
            <strong>Approved By</strong>
            <address class="mb-0">{{ optional($adjustment->approvedBy)->name ?? '—' }}</address>
        </div>
    </div>

    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Lot #</th>
                        <th>Expiry</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($adjustment->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ optional($item->product)->name ?? 'N/A' }}</td>
                            <td>
                                @if ($item->adjustment_type === 'IN')
                                    <span class="badge badge-success">Stock In</span>
                                @else
                                    <span class="badge badge-danger">Stock Out</span>
                                @endif
                            </td>
                            <td>{{ $item->lot_number ?? 'N/A' }}</td>
                            <td>{{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('Y-m-d') : 'N/A' }}</td>
                            <td>{{ $item->quantity }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No items</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($adjustment->reason)
        <div class="row">
            <div class="col-12">
                <p class="lead">Reason</p>
                <p class="text-muted">{{ $adjustment->reason }}</p>
            </div>
        </div>
    @endif
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>