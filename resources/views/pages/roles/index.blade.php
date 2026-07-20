@extends('inc.frame')

@section('content')
<div class="container-fluid">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Roles</h3>
            <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#addRoleModal">
                <i class="fas fa-plus"></i> Add Role
            </button>
        </div>
    </div>

    @if ($errors->any())
        <ul class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <div class="card">
        <div class="card-body">
            <table id="roles-table" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Role Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td><strong>{{ $role->role_name }}</strong></td>
                            <td>
                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#editRoleModal-{{ $role->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="{{ url('/delete-role-'.$role->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('Delete this role?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Edit Role Modal -->
                        <div class="modal fade" id="editRoleModal-{{ $role->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Edit Role – {{ $role->role_name }}</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="{{ url('/edit-role-'.$role->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Role Name</label>
                                                <input type="text" name="role_name" class="form-control" value="{{ $role->role_name }}" required>
                                            </div>
                                            <h5>Permissions</h5>
                                            <hr>

                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" id="select_all_edit_{{ $role->id }}" class="select-all-checkbox">
                                                        <label for="select_all_edit_{{ $role->id }}"><strong>Select All</strong></label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="superadmin" id="superadmin_edit_{{ $role->id }}" class="permission-checkbox" @if($role->superadmin == 'on') checked @endif>
                                                        <label for="superadmin_edit_{{ $role->id }}">Super Admin</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <h6 class="mt-3">Management Permissions</h6>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="manage_user" id="manage_user_edit_{{ $role->id }}" class="permission-checkbox" @if($role->manage_user == 'on') checked @endif>
                                                        <label for="manage_user_edit_{{ $role->id }}"> Users</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="manage_categories" id="manage_categories_edit_{{ $role->id }}" class="permission-checkbox" @if($role->manage_categories == 'on') checked @endif>
                                                        <label for="manage_categories_edit_{{ $role->id }}"> Categories</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="manage_products" id="manage_products_edit_{{ $role->id }}" class="permission-checkbox" @if($role->manage_products == 'on') checked @endif>
                                                        <label for="manage_products_edit_{{ $role->id }}"> Products</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="manage_locations" id="manage_locations_edit_{{ $role->id }}" class="permission-checkbox" @if($role->manage_locations == 'on') checked @endif>
                                                        <label for="manage_locations_edit_{{ $role->id }}"> Locations</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="manage_supplier" id="manage_supplier_edit_{{ $role->id }}" class="permission-checkbox" @if($role->manage_supplier == 'on') checked @endif>
                                                        <label for="manage_supplier_edit_{{ $role->id }}"> Suppliers</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="manage_opening_quantity" id="manage_opening_quantity_edit_{{ $role->id }}" class="permission-checkbox" @if($role->manage_opening_quantity == 'on') checked @endif>
                                                        <label for="manage_opening_quantity_edit_{{ $role->id }}"> Opening Quantity</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <h6 class="mt-3">Inventory Permissions</h6>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="manage_goods_receipt" id="manage_goods_receipt_edit_{{ $role->id }}" class="permission-checkbox" @if($role->manage_goods_receipt == 'on') checked @endif>
                                                        <label for="manage_goods_receipt_edit_{{ $role->id }}"> Goods Receipt</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="manage_inventory_transfer" id="manage_inventory_transfer_edit_{{ $role->id }}" class="permission-checkbox" @if($role->manage_inventory_transfer == 'on') checked @endif>
                                                        <label for="manage_inventory_transfer_edit_{{ $role->id }}"> Inventory Transfers</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="manage_inventory_adjustment" id="manage_inventory_adjustment_edit_{{ $role->id }}" class="permission-checkbox" @if($role->manage_inventory_adjustment == 'on') checked @endif>
                                                        <label for="manage_inventory_adjustment_edit_{{ $role->id }}">Inventory Adjustments</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <h6 class="mt-3">Patient & Treatment Permissions</h6>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="manage_patients" id="manage_patients_edit_{{ $role->id }}" class="permission-checkbox" @if($role->manage_patients == 'on') checked @endif>
                                                        <label for="manage_patients_edit_{{ $role->id }}">Patients</label>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="manage_treatment_consumption" id="manage_treatment_consumption_edit_{{ $role->id }}" class="permission-checkbox" @if($role->manage_treatment_consumption == 'on') checked @endif>
                                                        <label for="manage_treatment_consumption_edit_{{ $role->id }}">Treatment Consumption</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <h6 class="mt-3">Sales Permissions</h6>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="manage_product_sales" id="manage_product_sales_edit_{{ $role->id }}" class="permission-checkbox" @if($role->manage_product_sales == 'on') checked @endif>
                                                        <label for="manage_product_sales_edit_{{ $role->id }}">Product Sales</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <h6 class="mt-3">Reports</h6>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="icheck-success d-inline">
                                                        <input type="checkbox" name="view_reports" id="view_reports_edit_{{ $role->id }}" class="permission-checkbox" @if($role->view_reports == 'on') checked @endif>
                                                        <label for="view_reports_edit_{{ $role->id }}">View Reports</label>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Update Role</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr><td colspan="4" class="text-center">No roles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">New Role</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ url('/add-role') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Role Name</label>
                        <input type="text" name="role_name" class="form-control" required>
                    </div>

                    <h5>Permissions</h5>
                    <hr>

                    <div class="row">
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" id="select_all_add" class="select-all-checkbox">
                                <label for="select_all_add"><strong>Select All</strong></label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="superadmin" id="superadmin_add" class="permission-checkbox">
                                <label for="superadmin_add">Super Admin</label>
                            </div>
                        </div>
                    </div>

                    <h6 class="mt-3">Management Permissions</h6>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="manage_user" id="manage_user_add" class="permission-checkbox">
                                <label for="manage_user_add">Users</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="manage_categories" id="manage_categories_add" class="permission-checkbox">
                                <label for="manage_categories_add">Categories</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="manage_products" id="manage_products_add" class="permission-checkbox">
                                <label for="manage_products_add">Products</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="manage_locations" id="manage_locations_add" class="permission-checkbox">
                                <label for="manage_locations_add">Locations</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="manage_supplier" id="manage_supplier_add" class="permission-checkbox">
                                <label for="manage_supplier_add">Suppliers</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="manage_opening_quantity" id="manage_opening_quantity_add" class="permission-checkbox">
                                <label for="manage_opening_quantity_add">Opening Quantity</label>
                            </div>
                        </div>
                    </div>

                    <h6 class="mt-3">Inventory Permissions</h6>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="manage_goods_receipt" id="manage_goods_receipt_add" class="permission-checkbox">
                                <label for="manage_goods_receipt_add">Goods Receipt</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="manage_inventory_transfer" id="manage_inventory_transfer_add" class="permission-checkbox">
                                <label for="manage_inventory_transfer_add">Inventory Transfers</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="manage_inventory_adjustment" id="manage_inventory_adjustment_add" class="permission-checkbox">
                                <label for="manage_inventory_adjustment_add">Inventory Adjustments</label>
                            </div>
                        </div>
                    </div>

                    <h6 class="mt-3">Patient & Treatment Permissions</h6>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="manage_patients" id="manage_patients_add" class="permission-checkbox">
                                <label for="manage_patients_add">Patients</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="manage_treatment_consumption" id="manage_treatment_consumption_add" class="permission-checkbox">
                                <label for="manage_treatment_consumption_add">Treatment Consumption</label>
                            </div>
                        </div>
                    </div>

                    <h6 class="mt-3">Sales Permissions</h6>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="manage_product_sales" id="manage_product_sales_add" class="permission-checkbox">
                                <label for="manage_product_sales_add">Product Sales</label>
                            </div>
                        </div>
                    </div>

                    <h6 class="mt-3">Reports</h6>
                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="icheck-success d-inline">
                                <input type="checkbox" name="view_reports" id="view_reports_add" class="permission-checkbox">
                                <label for="view_reports_add">View Reports</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(function() {
        $('#roles-table').DataTable({
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            pageLength: 20,
            buttons: ["csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#roles-table_wrapper .col-md-6:eq(0)');

        // Select All functionality for Add modal
        $('#select_all_add').on('change', function() {
            var isChecked = $(this).prop('checked');
            $('#addRoleModal .permission-checkbox').prop('checked', isChecked);
        });

        // Select All functionality for each Edit modal
        $(document).on('change', '.select-all-checkbox', function() {
            var isChecked = $(this).prop('checked');
            var modal = $(this).closest('.modal');
            modal.find('.permission-checkbox').prop('checked', isChecked);
        });
    });
</script>
@endpush
@endsection