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
                            <select name="location_id" class="form-control select2" onchange="this.form.submit()">
                                <option value="">All Locations</option>
                                @foreach ($allowedLocations as $location)
                                    <option value="{{ $location->id }}" {{ $locationId == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
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
                            <th>No</th>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Patient</th>
                            <th>Location</th>
                            <th>Doctor</th>
                            <th>Items</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(function () {
    $('.select2').select2();
    $('#treatment_table').DataTable({
        processing: true, serverSide: true,
        dom: "<'row mb-2'<'col-md-6'B><'col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: ["csv", "excel", "pdf", "print"],
        ajax: {
            url: '{{ route('reports.treatment-consumption.data') }}',
            data: {
                patient_id: '{{ request('patient_id') }}',
                product_id: '{{ request('product_id') }}',
                location_id: '{{ $locationId }}',
                from_date: '{{ $fromDate }}',
                to_date: '{{ $toDate }}'
            }
        },
        columns: [
            { data: 'no', orderable: false, searchable: false },
            { data: 'date' },
            { data: 'reference', orderable: false },
            { data: 'patient' },
            { data: 'location' },
            { data: 'doctor' },
            { data: 'items', orderable: false, searchable: false },
            { data: 'status' }
        ],
        order: [[1, 'desc']],
        pageLength: 100
    });
});
</script>
@endpush