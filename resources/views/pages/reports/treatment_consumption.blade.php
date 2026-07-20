@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3>Treatment Consumption Report</h3>
                <small>From {{ $fromDate }} to {{ $toDate }}</small>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('reports.treatment-consumption') }}" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="patient_id" class="form-control select2" onchange="this.form.submit()">
                                <option value="">All Patients</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="product_id" class="form-control select2" onchange="this.form.submit()">
                                <option value="">All Products</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="to_date" value="{{ $toDate }}" class="form-control" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-1">
                            <a href="{{ route('reports.treatment-consumption') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-stethoscope"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Treatments</span>
                                <span class="info-box-number">{{ $summary->total_treatments ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-boxes"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Items Consumed</span>
                                <span class="info-box-number">{{ number_format($summary->total_items_consumed ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <table id="treatment_table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Patient</th>
                            <th>Location</th>
                            <th>Doctor</th>
                            <th>Items</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($consumptions as $c)
                            <tr>
                                <td>{{ $c->created_at->format('Y-m-d H:i') }}</td>
                                <td>TC-#{{ $c->id }}</td>
                                <td>{{ $c->patient->full_name ?? 'N/A' }}</td>
                                <td>{{ $c->location->name ?? 'N/A' }}</td>
                                <td>{{ $c->doctor->name ?? 'N/A' }}</td>
                                <td>
                                    <ul class="mb-0">
                                        @foreach ($c->items as $item)
                                            <li>{{ $item->product->name ?? 'N/A' }}: {{ $item->quantity }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $c->status == 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($c->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function() {
    $('.select2').select2();
    const dt = $('#treatment_table').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        pageLength: 20,
        buttons: ["csv","excel","pdf","print"]
    });
    dt.buttons().container().appendTo('#treatment_table_wrapper .col-md-6:eq(0)');
});
</script>
@endpush