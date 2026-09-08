<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Count - CashTrack Secure</title>
</head>

<body>

    <h1>CashTrack Secure</h1>

    <h2>Stock Count</h2>

    @if($errors->any())
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST"
          action="{{ route('owner.stock-counts.store') }}">

        @csrf

        <div>
            <label>Product</label>

            <select name="product_id" required>

                <option value="">
                    -- Select Product --
                </option>

                @foreach($products as $product)

                    <option value="{{ $product->id }}">
                        {{ $product->sku }}
                        -
                        {{ $product->name }}
                    </option>

                @endforeach

            </select>
        </div>

        <br>

        <div>
            <label>Physical Quantity</label>

            <input
                type="number"
                name="physical_quantity"
                min="0"
                required
            >
        </div>

        <br>

        <div>
            <label>Notes</label>

            <textarea name="notes"></textarea>
        </div>

        <br>

        <button type="submit">
            Record Stock Count
        </button>

    </form>

    <br>

    <a href="{{ route('owner.stock-counts.index') }}">
        View Stock Count History
    </a>

</body>
</html>