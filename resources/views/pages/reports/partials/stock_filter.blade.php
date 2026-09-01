<form method="GET" action="{{ route($route) }}" class="mb-3">
    <div class="row">
        <div class="col-md-3">
            <select name="product_id" class="form-control select2" onchange="this.form.submit()">
                <option value="">All Products</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" {{ (isset($productId) && $productId == $product->id) ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="location_id" class="form-control select2" onchange="this.form.submit()">
                <option value="">All Locations</option>
                @foreach ($allowedLocations as $location)
                    <option value="{{ $location->id }}" {{ (isset($locationId) && $locationId == $location->id) ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <a href="{{ route($route) }}" class="btn btn-secondary">Reset</a>
        </div>
    </div>
</form>