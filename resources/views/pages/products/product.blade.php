@extends('inc.frame')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-8 lg">
                                <b>Total Product: {{ count($products) }}</b>
                            </div>
                            <div class="col-4 lg">
                                <button type="button" class="btn btn-primary pull-right btn-sm" style="float: right;"
                                    data-toggle="modal" data-target="#modal-lg">
                                    New Product
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card col-md-12">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Item Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Unit (UoM)</th>
                                    <th>Packaging</th>
                                    <th>Status</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id='list'>
                                @if (count($products) > 0)
                                    @php $no = 0; @endphp
                                    @foreach ($products as $product)
                                        @php $no++; @endphp
                                        <tr>
                                            <td>{{ $no }}</td>
                                            <td><span class="badge badge-info">{{ $product->item_code }}</span></td>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ optional($product->category)->name ?? '—' }}</td>
                                            <td>{{ $product->unit ?? '—' }}</td>
                                            <td>
                                                @if ($product->packaging_type === 'pack')
                                                    <span class="badge badge-warning">Pack</span>
                                                    <small class="d-block">{{ $product->default_pack_size ?? 1 }} × {{ $product->unit }}</small>
                                                @else
                                                    <span class="badge badge-secondary">Unit</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($product->status === 'active')
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $product->description ?? '—' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm"
                                                    data-toggle="modal" data-target="#modal-lg-{{ $product->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="delete-product-{{ $product->id }}"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure you want to delete this product?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <a href="{{ route('products.opening-quantities', $product->id) }}"
                                                    class="btn btn-sm btn-success"
                                                    title="Manage Opening Quantity">
                                                    <i class="fas fa-warehouse"></i>
                                                </a>
                                                <a href="{{ route('products.reorder-settings', $product->id) }}"
                                                    class="btn btn-sm btn-info"
                                                    title="Reorder Settings">
                                                    <i class="fas fa-bell"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="modal-lg-{{ $product->id }}">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Edit {{ $product->name }}</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="container-fluid">
                                                            <div class="card card-primary">
                                                                <div class="card-header">
                                                                    <h3 class="card-title">Product
                                                                        <small>Information</small>
                                                                    </h3>
                                                                </div>
                                                                <form action="/edit-product-{{ $product->id }}"
                                                                    method="POST" id="quickForm">
                                                                    @csrf
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-4">
                                                                                <div class="form-group">
                                                                                    <label>Category</label>
                                                                                    <a data-toggle="modal" data-target="#modal-lg-category"
                                                                                        class="btn btn-xs btn-primary" style="float:right">
                                                                                        <i class="fas fa-plus"></i>
                                                                                    </a>
                                                                                    <select name="category_id"
                                                                                        class="form-control">
                                                                                        <option value="">Select
                                                                                            Category</option>
                                                                                        @foreach ($categories as $category)
                                                                                            <option
                                                                                                value="{{ $category->id }}"
                                                                                                {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                                                                                {{ $category->name }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-4">
                                                                                <div class="form-group">
                                                                                    <label>Item Code *</label>
                                                                                    <input type="text" name="item_code"
                                                                                        class="form-control"
                                                                                        value="{{ $product->item_code }}"
                                                                                        required>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-4">
                                                                                <div class="form-group">
                                                                                    <label>Product Name *</label>
                                                                                    <input type="text" name="name"
                                                                                        class="form-control"
                                                                                        value="{{ $product->name }}"
                                                                                        required>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-4">
                                                                                <div class="form-group">
                                                                                    <label>Packaging Type</label>
                                                                                    <select name="packaging_type" class="form-control packaging-type-select">
                                                                                        <option value="unit" {{ $product->packaging_type == 'unit' ? 'selected' : '' }}>Unit</option>
                                                                                        <option value="pack" {{ $product->packaging_type == 'pack' ? 'selected' : '' }}>Pack</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-4">
                                                                                <div class="form-group">
                                                                                    <label>Pack Size</label>
                                                                                    <input type="number" name="default_pack_size"
                                                                                        class="form-control pack-size-input" min="1"
                                                                                        value="{{ $product->default_pack_size ?? 1 }}">
                                                                                    <small class="text-muted">Units per pack</small>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-4">
                                                                                <div class="form-group">
                                                                                    <label>Unit (Base UoM)</label>
                                                                                    <input type="text" name="unit"
                                                                                        class="form-control"
                                                                                        value="{{ $product->unit }}"
                                                                                        placeholder="e.g., piece, mL">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-4">
                                                                                <div class="form-group">
                                                                                    <label>Status</label>
                                                                                    <select name="status"
                                                                                        class="form-control">
                                                                                        <option value="active"
                                                                                            {{ $product->status == 'active' ? 'selected' : '' }}>
                                                                                            Active
                                                                                        </option>
                                                                                        <option value="inactive"
                                                                                            {{ $product->status == 'inactive' ? 'selected' : '' }}>
                                                                                            Inactive
                                                                                        </option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-8">
                                                                                <div class="form-group">
                                                                                    <label>Description</label>
                                                                                    <input type="text" name="description"
                                                                                        class="form-control"
                                                                                        value="{{ $product->description }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="modal-footer justify-content-between">
                                                                            <button type="button"
                                                                                class="btn btn-default"
                                                                                data-dismiss="modal">
                                                                                {{ __('messages.close') }}
                                                                            </button>
                                                                            <button type="submit"
                                                                                class="btn btn-primary swalDefaultSuccess"
                                                                                onclick="return confirm('Are you sure? Save Changes !!!');">
                                                                                Save Change
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="9" class="text-center">No products found!</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal-lg">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">New Product</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Product <small>Information</small></h3>
                                    </div>
                                    <form action="/add-product" method="POST" id="quickForm">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label>Category</label>
                                                        <a data-toggle="modal" data-target="#modal-lg-category"
                                                            class="btn btn-xs btn-primary" style="float:right">
                                                            <i class="fas fa-plus"></i>
                                                        </a>
                                                        <select name="category_id" class="form-control">
                                                            <option value="">Select Category</option>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}">
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label>Item Code *</label>
                                                        <input type="text" name="item_code" class="form-control"
                                                            placeholder="e.g., PRD-001" required>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label>Product Name *</label>
                                                        <input type="text" name="name" class="form-control"
                                                            placeholder="Product Name" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label>Packaging Type</label>
                                                        <select name="packaging_type" class="form-control packaging-type-select">
                                                            <option value="unit">Unit</option>
                                                            <option value="pack">Pack</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label>Pack Size</label>
                                                        <input type="number" name="default_pack_size"
                                                            class="form-control pack-size-input" min="1" value="1"
                                                            placeholder="Units per pack">
                                                        <small class="text-muted">How many units in one pack</small>
                                                    </div>
                                                </div>
                                                <div class="col-4">
                                                    <div class="form-group">
                                                        <label>Unit (Base UoM)</label>
                                                        <input type="text" name="unit" class="form-control"
                                                            placeholder="e.g., piece, mL, kg">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select name="status" class="form-control">
                                                            <option value="active">Active</option>
                                                            <option value="inactive">Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group">
                                                        <label>Description</label>
                                                        <input type="text" name="description" class="form-control"
                                                            placeholder="Description">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer justify-content-between">
                                                <button type="button" class="btn btn-default"
                                                    data-dismiss="modal">{{ __('messages.close') }}</button>
                                                <button type="submit"
                                                    class="btn btn-primary swalDefaultSuccess">{{ __('messages.register') }}</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal-lg-category">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Manage Categories</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">Add New Category</h3>
                                    </div>
                                    <form action="/add-category" method="POST">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label>Category Name</label>
                                                        <input type="text" name="category_name"
                                                            class="form-control" placeholder="Category Name" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer justify-content-between">
                                                <button type="button" class="btn btn-default"
                                                    data-dismiss="modal">{{ __('messages.close') }}</button>
                                                <button type="submit"
                                                    class="btn btn-primary swalDefaultSuccess">{{ __('messages.register') }}</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="row mt-3">
                                    @forelse ($categories as $category)
                                        <div class="col-md-3">
                                            <div class="text-center rounded shadow-sm p-2">
                                                <form action="/edit-category-{{ $category->id }}" method="POST"
                                                      class="d-flex flex-column align-items-center">
                                                    @csrf
                                                    <input type="text" name="category_name"
                                                        class="form-control mb-2 text-center"
                                                        value="{{ $category->name }}"
                                                        required>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button type="submit" class="btn btn-sm btn-outline-primary"
                                                                title="Save"
                                                                onclick="return confirm('Update this category?');">
                                                            <i class="fas fa-save"></i>
                                                        </button>
                                                        <a href="/delete-category-{{ $category->id }}"
                                                           class="btn btn-sm btn-outline-danger"
                                                           onclick="return confirm('Are you sure you want to delete this category?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center">
                                            <p>No categories found.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function togglePackSize(select) {
            var modal = select.closest('.modal');
            if (!modal) return;
            var packSizeInput = modal.querySelector('.pack-size-input');
            if (!packSizeInput) return;
            if (select.value === 'unit') {
                packSizeInput.value = 1;
                packSizeInput.readOnly = true;
            } else {
                packSizeInput.readOnly = false;
                if (packSizeInput.value < 1) {
                    packSizeInput.value = 1;
                }
            }
        }

        var selects = document.querySelectorAll('.packaging-type-select');
        selects.forEach(function(select) {
            togglePackSize(select);
            select.addEventListener('change', function() {
                togglePackSize(this);
            });
        });
    });
</script>
@endsection