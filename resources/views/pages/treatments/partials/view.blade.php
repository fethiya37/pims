<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-user-md"></i> Treatment Record #{{ $consumption->id }}
                <small class="float-right">
                    Created: {{ \Carbon\Carbon::parse($consumption->created_at)->toFormattedDateString() }}
                </small>
            </h4>
        </div>
    </div>

    <div class="row invoice-info mb-3">
        <div class="col-sm-4">
            <strong>Patient</strong>
            <address class="mb-0">{{ optional($consumption->patient)->full_name ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-4">
            <strong>Location</strong>
            <address class="mb-0">{{ optional($consumption->location)->name ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-4">
            <strong>Status</strong>
            <address class="mb-0">
                <span class="badge badge-{{ $statusClass }}">{{ ucfirst($consumption->status) }}</span>
            </address>
        </div>
    </div>

    <div class="row invoice-info mb-3">
        <div class="col-sm-4">
            <strong>Doctor</strong>
            <address class="mb-0">{{ optional($consumption->doctor)->name ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-4">
            <strong>Treatment Date</strong>
            <address class="mb-0">{{ $consumption->treatment_date ? \Carbon\Carbon::parse($consumption->treatment_date)->format('d M Y') : 'N/A' }}</address>
        </div>
        <div class="col-sm-4">
            <strong>Diagnosis</strong>
            <address class="mb-0">{{ $consumption->diagnosis ?? 'N/A' }}</address>
        </div>
    </div>

    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>#</th><th>Product</th><th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($consumption->items as $i => $item)
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

    @if ($consumption->notes)
        <div class="row">
            <div class="col-12">
                <p class="lead">Notes</p>
                <p class="text-muted">{{ $consumption->notes }}</p>
            </div>
        </div>
    @endif
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>