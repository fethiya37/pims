<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-12">
            <h4>
                <i class="fas fa-cash-register"></i> Sale Record #{{ $sale->id }}
                <small class="float-right">
                    Created: {{ \Carbon\Carbon::parse($sale->created_at)->toFormattedDateString() }}
                </small>
            </h4>
        </div>
    </div>

    <div class="row invoice-info mb-3">
        <div class="col-sm-4">
            <strong>Location</strong>
            <address class="mb-0">{{ optional($sale->location)->name ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-4">
            <strong>Invoice</strong>
            <address class="mb-0">{{ $sale->invoice_no ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-4">
            <strong>Status</strong>
            <address class="mb-0">
                <span class="badge badge-{{ $statusClass }}">{{ ucfirst($sale->status) }}</span>
            </address>
        </div>
    </div>

    <div class="row invoice-info mb-3">
        <div class="col-sm-4">
            <strong>Payment Type</strong>
            <address class="mb-0">{{ $sale->payment_type ?? 'N/A' }}</address>
        </div>
        <div class="col-sm-4">
            <strong>VAT Rate</strong>
            <address class="mb-0">{{ $sale->vat_rate ?? 0 }}%</address>
        </div>
        <div class="col-sm-4">
            <strong>User</strong>
            <address class="mb-0">{{ optional($sale->user)->name ?? 'N/A' }}</address>
        </div>
    </div>

    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>#</th><th>Product</th><th>Qty</th><th>Unit Price</th><th>Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sale->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ optional($item->product)->name ?? 'N/A' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No items</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-7">
            @if ($sale->notes)
                <p class="lead">Notes</p>
                <p class="text-muted">{{ $sale->notes }}</p>
            @endif
        </div>
        <div class="col-5">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th style="width:50%">Subtotal:</th>
                        <td class="text-right">{{ number_format($sale->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <th>VAT ({{ number_format($sale->vat_rate ?? 0, 2) }}%)</th>
                        <td class="text-right">{{ number_format($sale->total_tax, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Grand Total:</th>
                        <td class="text-right"><strong>{{ number_format($sale->total_amount, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>