<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - CashTrack Secure</title>
</head>

<body>

    <h1>CashTrack Secure</h1>

    <h2>Add Product</h2>

    <form method="POST" action="{{ route('owner.products.store') }}">

        @csrf

        <div>
            <label>SKU</label>
            <input
                type="text"
                name="sku"
                value="{{ old('sku') }}"
                required
            >
        </div>

        <br>

        <div>
            <label>Product Name</label>
            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
            >
        </div>

        <br>

        <div>
            <label>Description</label>
            <textarea name="description">{{ old('description') }}</textarea>
        </div>

        <br>

        <div>
            <label>Selling Price (RM)</label>
            <input
                type="number"
                name="selling_price"
                step="0.01"
                min="0"
                value="{{ old('selling_price') }}"
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
                value="{{ old('reorder_level', 0) }}"
                required
            >
        </div>

        <br>

        <button type="submit">
            Save Product
        </button>

        <a href="{{ route('owner.products.index') }}">
            Cancel
        </a>

    </form>

</body>
</html>