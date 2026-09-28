<div class="container-fluid">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Product <small>Information</small></h3>
        </div>
        <form action="/edit-product-{{ $product->id }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" class="form-control category-dropdown">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Item Code</label>
                            <input type="text" name="item_code" class="form-control"
                                value="{{ $product->item_code }}" readonly>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Product Name *</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ $product->name }}" required>
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
                            <input type="text" name="unit" class="form-control"
                                value="{{ $product->unit }}" placeholder="e.g., piece, mL">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="active" {{ $product->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $product->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="description" class="form-control"
                                value="{{ $product->description }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    {{ __('messages.close') }}
                </button>
                <button type="submit" class="btn btn-primary"
                    onclick="return confirm('Are you sure? Save Changes !!!');">
                    Save Change
                </button>
            </div>
        </form>
    </div>
</div>