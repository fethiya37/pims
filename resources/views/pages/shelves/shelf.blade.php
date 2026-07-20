@extends('inc.frame')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary p-0 card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <div class="btn btn-primary btn-sm" style="float: left">Shelves :
                                    <b>{{ count($shelves) }}</b>
                                </div>
                            </h3>
                            <button type="button" class="btn btn-primary btn-sm pull-right" style="float: right;"
                                data-toggle="modal" data-target="#modal-shelf">
                                ADD New
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
                                            <th>Location</th>
                                            <th>Shelf Code</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($shelves) > 0)
                                            @php $no = 0; @endphp
                                            @foreach ($shelves as $shelf)
                                                @php $no++; @endphp
                                                <tr>
                                                    <td>{{ $no }}</td>
                                                    <td>{{ $shelf->location->name }}</td>
                                                    <td>{{ 'SH_' . $shelf->code }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-primary btn-sm"
                                                            data-toggle="modal"
                                                            data-target="#modal-lg-shelf-{{ $shelf->id }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a type="button" class="btn btn-danger btn-sm"
                                                            href="/delete-shelf-{{ $shelf->id }}"
                                                            onclick="return confirm('Are you sure?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>

                                                <!-- Edit Modal -->
                                                <div class="modal fade" id="modal-lg-shelf-{{ $shelf->id }}">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">Edit Shelf</h4>
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="container-fluid">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="card card-primary">
                                                                                <div class="card-header">
                                                                                    <h3 class="card-title">Shelf
                                                                                        <small>Information</small>
                                                                                    </h3>
                                                                                </div>
                                                                                <form
                                                                                    action="/edit-shelf-{{ $shelf->id }}"
                                                                                    method="POST" id="quickForm">
                                                                                    @csrf
                                                                                    <div class="card-body">
                                                                                        <div class="form-group">
                                                                                            <label>Location</label>
                                                                                            <select name="location_id"
                                                                                                class="form-control"
                                                                                                required>
                                                                                                @foreach ($locations as $location)
                                                                                                    <option
                                                                                                        value="{{ $location->id }}"
                                                                                                        @if ($shelf->location_id == $location->id) selected @endif>
                                                                                                        {{ $location->name }}
                                                                                                    </option>
                                                                                                @endforeach
                                                                                            </select>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                            <label>Shelf Code</label>
                                                                                            <input type="text"
                                                                                                name="code"
                                                                                                class="form-control"
                                                                                                value="{{ $shelf->code }}"
                                                                                                required>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div
                                                                                        class="modal-footer justify-content-between">
                                                                                        <button type="button"
                                                                                            class="btn btn-default"
                                                                                            data-dismiss="modal">{{ __('messages.close') }}</button>
                                                                                        <button type="submit"
                                                                                            class="btn btn-primary swalDefaultSuccess"
                                                                                            onclick="return confirm('Are you sure you want to save changes ?');">Save
                                                                                            Change</button>
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
                                                <!-- End Edit Modal -->
                                            @endforeach
                                        @else
                                            <h4>No shelves found!</h4>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Create Modal -->
                    <div class="modal fade" id="modal-shelf">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">New Shelf</h4>
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
                                                        <h3 class="card-title">Shelf <small>Information</small></h3>
                                                    </div>
                                                    <form action="/add-shelf" method="POST" id="quickForm">
                                                        @csrf
                                                        <div class="card-body">
                                                            <div class="form-group">
                                                                <label>Location</label>
                                                                <select name="location_id" class="form-control" required>
                                                                    <option value="">Select Location</option>
                                                                    @foreach ($locations as $location)
                                                                        <option value="{{ $location->id }}">
                                                                            {{ $location->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Shelf Code</label>
                                                                <input type="text" name="code" class="form-control"
                                                                    placeholder="Shelf Code (e.g. 001)" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer justify-content-between">
                                                            <button type="button" class="btn btn-default"
                                                                data-dismiss="modal">{{ __('messages.close') }}</button>
                                                            <button type="submit"
                                                                class="btn btn-primary swalDefaultSuccess">{{ __('messages.register') }}</button>
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
                    <!-- End Create Modal -->
                </div>
                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <div class="btn btn-primary btn-sm" style="float: left">Item Shelf Locations :
                                    <b>{{ count($records) }}</b>
                                </div>
                            </h3>
                            <button type="button" class="btn btn-primary btn-sm pull-right" style="float: right;"
                                data-toggle="modal" data-target="#modal-lg">
                                ADD New
                            </button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="row">
                            <div class="card-body">
                                <table id="example3" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Product</th>
                                            <th>Location</th>
                                            <th>Shelf</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($records) > 0)
                                            @php $no = 0; @endphp
                                            @foreach ($records as $entry)
                                                @php $no++; @endphp
                                                <tr>
                                                    <td>{{ $no }}</td>
                                                    <td>{{ $entry->product->name ?? '-' }}</td>
                                                    <td>{{ $entry->shelf->location->name ?? '-' }}</td>
                                                    <td>{{ 'SH_' . $entry->shelf->code }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-primary btn-sm"
                                                            data-toggle="modal"
                                                            data-target="#modal-lg-{{ $entry->id }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a type="button" class="btn btn-danger btn-sm"
                                                            href="/item-shelf-locations/delete/{{ $entry->id }}"
                                                            onclick="return confirm('Are you sure?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>

                                                <!-- Edit Modal -->
                                                <div class="modal fade" id="modal-lg-{{ $entry->id }}">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">Edit Item Shelf Location</h4>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form action="/item-shelf-locations/{{ $entry->id }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <div class="form-group">
                                                                        <label>Product</label>
                                                                        <select name="product_id" class="form-control"
                                                                            required>
                                                                            <option value="">Select</option>
                                                                            @foreach ($products as $product)
                                                                                <option value="{{ $product->id }}"
                                                                                    {{ $entry->product_id == $product->id ? 'selected' : '' }}>
                                                                                    {{ $product->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>Shelf</label>
                                                                        <select name="shelf_id" class="form-control"
                                                                            required>
                                                                            <option value="">Select</option>
                                                                            @foreach ($shelves as $shelf)
                                                                                <option value="{{ $shelf->id }}"
                                                                                    {{ $entry->shelf_id == $shelf->id ? 'selected' : '' }}>
                                                                                    SH_{{ $shelf->code }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                    <div class="modal-footer justify-content-between">
                                                                        <button type="button" class="btn btn-default"
                                                                            data-dismiss="modal">{{ __('messages.close') }}</button>
                                                                        <button type="submit"
                                                                            class="btn btn-primary">Save Change</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Edit Modal -->
                                            @endforeach
                                        @else
                                            <h4>No records found!</h4>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Create Modal -->
                    <div class="modal fade" id="modal-lg">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">New Item Shelf Location</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form action="/item-shelf-locations" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label>Product</label>
                                            <select name="product_id" class="form-control" required>
                                                <option value="">Select</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Shelf</label>
                                            <select name="shelf_id" class="form-control" required>
                                                <option value="">Select</option>
                                                @foreach ($shelves as $shelf)
                                                    <option value="{{ $shelf->id }}">SH_{{ $shelf->code }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="modal-footer justify-content-between">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal">{{ __('messages.close') }}</button>
                                            <button type="submit" class="btn btn-primary">{{ __('messages.register') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Create Modal -->
                </div>
            </div>
        </div>
    </section>
@endsection
