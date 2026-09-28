<button type="button" class="btn btn-primary btn-sm edit-product-btn"
    data-id="{{ $product->id }}">
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