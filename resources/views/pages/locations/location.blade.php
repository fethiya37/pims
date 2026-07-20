@extends('inc.frame')

@section('content')

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <div class="p-2 btn btn-primary btn-sm" style="float: left">Business Locations : <b>{{ count($locations) }}</b></div>
                        </h3>
                        <button type="button" class="btn btn-primary btn-sm pull-rigth" style="float: right;" data-toggle="modal" data-target="#modal-lg">
                            ADD New Location
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="row">
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Location Name</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($locations) > 0)
                                        @php $no = 0; @endphp
                                        @foreach ($locations as $location)
                                            @php $no++; @endphp
                                            <tr>
                                                <td>{{ $no }}</td>
                                                <td>{{ $location->name }}</td>
                                                <td>
                                                    @if ($location->type === 'store')
                                                        <span class="badge badge-primary">Store</span>
                                                    @else
                                                        <span class="badge badge-info">Point of Use</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-lg-{{ $location->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a type="button" class="btn btn-danger btn-sm" href="delete-location-{{ $location->id }}" onclick="return confirm('Are you sure?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="modal-lg-{{ $location->id }}">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Location</h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="container-fluid">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="card card-primary">
                                                                            <div class="card-header">
                                                                                <h3 class="card-title">Location <small>Information</small></h3>
                                                                            </div>
                                                                            <form action="/edit-location-{{ $location->id }}" method="POST">
                                                                                @csrf
                                                                                <div class="card-body">
                                                                                    <div class="form-group">
                                                                                        <label>Name</label>
                                                                                        <input type="text" name="name" class="form-control" value="{{ $location->name }}" required>
                                                                                    </div>
                                                                                    <div class="form-group">
                                                                                        <label>Type</label>
                                                                                        @if (auth()->user()->role->role_name === 'Super Admin')
                                                                                            <select name="type" class="form-control" required>
                                                                                                <option value="store" {{ $location->type === 'store' ? 'selected' : '' }}>Store</option>
                                                                                                <option value="point_of_use" {{ $location->type === 'point_of_use' ? 'selected' : '' }}>Point of Use</option>
                                                                                            </select>
                                                                                        @else
                                                                                            <input type="text" class="form-control" value="{{ $location->type === 'store' ? 'Store' : 'Point of Use' }}" readonly>
                                                                                            <input type="hidden" name="type" value="{{ $location->type }}">
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer justify-content-between">
                                                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to save changes?');">Save Changes</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center">No locations found!</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add Location Modal -->
                <div class="modal fade" id="modal-lg">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">New Location</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card card-primary">
                                                <div class="card-header">
                                                    <h3 class="card-title">Location <small>Information</small></h3>
                                                </div>
                                                <form action="/add-location" method="POST">
                                                    @csrf
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label>Location Name</label>
                                                            <input type="text" name="name" class="form-control" placeholder="Enter Location Name" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Type</label>
                                                            @if (auth()->user()->role->role_name === 'Super Admin')
                                                                <select name="type" class="form-control" required>
                                                                    <option value="" disabled selected>Select Type</option>
                                                                    <option value="store">Store</option>
                                                                    <option value="point_of_use">Point of Use</option>
                                                                </select>
                                                            @else
                                                                <input type="text" class="form-control" value="{{ auth()->user()->location->type === 'store' ? 'Store' : 'Point of Use' }}" readonly>
                                                                <input type="hidden" name="type" value="{{ auth()->user()->location->type }}">
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer justify-content-between">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Register</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection