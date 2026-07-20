@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <div class="text-warning" style="float: left">Total Users : <b>{{ count($users) }}</b></div>
                        </h3>
                        <button type="button" class="btn btn-primary pull-right" style="float: right;"
                            data-toggle="modal" data-target="#modal-lg">
                            ADD New User
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="stock-transfers-table" class="table table-bordered table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>UserName</th>
                                        <th>Email</th>
                                        <th>Current Role</th>
                                        <th>Location</th>
                                        <th>Registration Date</th>
                                        <th>Set/Change</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($users) > 0)
                                        @foreach ($users as $user)
                                            @php
                                                $currentRole = $roles->firstWhere('id', $user->role_id);
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    @if ($currentRole)
                                                        <span class="badge badge-info">{{ $currentRole->role_name }}</span>
                                                    @else
                                                        <span class="badge badge-secondary">No Role</span>
                                                    @endif
                                                </td>
                                                <td>{{ optional($user->location)->name ?? 'N/A' }}</td>
                                                <td>{{ $user->created_at->toDateString() }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-success btn-sm"
                                                        data-toggle="modal"
                                                        data-target="#modal-lg-role-{{ $user->id }}">
                                                        Set Role
                                                    </button>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm"
                                                        data-toggle="modal"
                                                        data-target="#modal-lg-{{ $user->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a type="button" class="btn btn-danger btn-sm"
                                                        href="delete-user-{{ $user->id }}"
                                                        onclick="return confirm('Are you sure?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>

                                            <!-- Edit User Modal -->
                                            <div class="modal fade" id="modal-lg-{{ $user->id }}">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit User</h4>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form action="/editUser-{{ $user->id }}" method="POST">
                                                                @csrf
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col-6">
                                                                            <div class="form-group">
                                                                                <label>Full Name</label>
                                                                                <input type="text" name="full_name"
                                                                                    class="form-control"
                                                                                    value="{{ $user->name }}" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <div class="form-group">
                                                                                <label>Email</label>
                                                                                <input type="email" name="email"
                                                                                    class="form-control"
                                                                                    value="{{ $user->email }}" required>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-6">
                                                                            <div class="form-group">
                                                                                <label>Phone</label>
                                                                                <input type="text" name="phone"
                                                                                    class="form-control"
                                                                                    value="{{ $user->phone }}"
                                                                                    pattern="[+ , 0]{1}[0-9]{9,14}">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <div class="form-group">
                                                                                <label>Location</label>
                                                                                <select name="location_id"
                                                                                    class="form-control" required>
                                                                                    <option value="">Select Location
                                                                                    </option>
                                                                                    @foreach ($locations as $location)
                                                                                        <option
                                                                                            value="{{ $location->id }}"
                                                                                            @if ($user->location_id == $location->id) selected @endif>
                                                                                            {{ $location->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>New Password
                                                                            <small>(leave blank to keep current)</small>
                                                                        </label>
                                                                        <input type="password" name="password"
                                                                            class="form-control"
                                                                            placeholder="New Password">
                                                                    </div>
                                                                </div>

                                                                <div class="modal-footer justify-content-between">
                                                                    <button type="button" class="btn btn-default"
                                                                        data-dismiss="modal">{{ __('messages.close') }}</button>
                                                                    <button type="submit"
                                                                        class="btn btn-primary"
                                                                        onclick="return confirm('Are you sure?');">Save
                                                                        Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Set Role Modal -->
                                            <div class="modal fade" id="modal-lg-role-{{ $user->id }}">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Set Role for
                                                                {{ $user->name }}</h4>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <form action="/set-role-{{ $user->id }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    @forelse ($roles as $role)
                                                                        <div class="col-sm-4">
                                                                            <div class="form-group clearfix">
                                                                                <div class="icheck-success d-inline">
                                                                                    <input type="radio"
                                                                                        id="role_{{ $role->id }}_{{ $user->id }}"
                                                                                        name="role"
                                                                                        value="{{ $role->id }}"
                                                                                        @if ($role->id == $user->role_id) checked @endif>
                                                                                    <label
                                                                                        for="role_{{ $role->id }}_{{ $user->id }}">
                                                                                        {{ $role->role_name }}
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @empty
                                                                        <div class="col-12">
                                                                            <p>No roles available. Please create one
                                                                                first.</p>
                                                                        </div>
                                                                    @endforelse
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default"
                                                                    data-dismiss="modal">{{ __('messages.close') }}</button>
                                                                <button type="submit"
                                                                    class="btn btn-primary">{{ __('messages.register') }}</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="8" class="text-center">No users found!</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Add User Modal -->
                <div class="modal fade" id="modal-lg">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">New User</h4>
                                <button type="button" class="close" data-dismiss="modal">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="/add-user" method="POST">
                                    @csrf
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Full Name</label>
                                                    <input type="text" name="full_name" class="form-control"
                                                        placeholder="Full Name" required>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="email" name="email" class="form-control"
                                                        placeholder="Email" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label>Phone</label>
                                                    <input type="text" name="phone" class="form-control"
                                                        placeholder="+251" pattern="[+ , 0]{1}[0-9]{9,14}">
                                                </div>
                                            </div>
                                            <div class="col-6">
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
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" name="password" class="form-control"
                                                placeholder="Password" required>
                                        </div>
                                    </div>

                                    <div class="modal-footer justify-content-between">
                                        <button type="button" class="btn btn-default"
                                            data-dismiss="modal">{{ __('messages.close') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary">{{ __('messages.register') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection