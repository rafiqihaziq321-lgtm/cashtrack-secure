<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - CashTrack Secure</title>
</head>

<body>

    <h1>CashTrack Secure</h1>

    <h2>Edit Product</h2>

    <form method="POST"
          action="{{ route('owner.products.update', $product) }}">

        @csrf
        @method('PUT')

        <div>
            <label>SKU</label>

            <input
                type="text"
                name="sku"
                value="{{ old('sku', $product->sku) }}"
                required
            >
        </div>

        <br>

        <div>
            <label>Product Name</label>

            <input
                type="text"
                name="name"
                value="{{ old('name', $product->name) }}"
                required
            >
        </div>

        <br>

        <div>
            <label>Description</label>

            <textarea name="description">{{ old('description', $product->description) }}</textarea>
        </div>

        <br>

        <div>
            <label>Selling Price (RM)</label>

            <input
                type="number"
                name="selling_price"
                step="0.01"
                min="0"
                value="{{ old('selling_price', $product->selling_price) }}"
                required
            >
        </div>

        <br>

        <div>
            <label>Reorder Level</label>

            <input
                type="number"
                name="reorder_level"
                min="0"
                value="{{ old('reorder_level', $product->reorder_level) }}"
                required
            >
        </div>

        <br>

        <button type="submit">
            Update Product
        </button>

        <a href="{{ route('owner.products.index') }}">
            Cancel
        </a>

    </form>

</body>
</html>