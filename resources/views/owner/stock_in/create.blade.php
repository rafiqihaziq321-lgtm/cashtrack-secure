<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock In - CashTrack Secure</title>
</head>
<body>

    <h1>CashTrack Secure</h1>
    <h2>Stock In</h2>

    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST"
          action="{{ route('owner.stock-in.store') }}">

        @csrf

        <div>
            <label>Product</label>

            <select name="product_id" required>
                <option value="">-- Select Product --</option>

                @foreach($products as $product)
                    <option value="{{ $product->id }}">
                        {{ $product->sku }} - {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <br>

        <div>
            <label>Quantity</label>

            <input
                type="number"
                name="quantity"
                min="1"
                required
            >
        </div>

        <br>

        <div>
            <label>Notes</label>

            <textarea name="notes"
                      placeholder="Example: Opening stock"></textarea>
        </div>

        <br>

        <button type="submit">
            Record Stock In
        </button>

    </form>

    <br>

    <a href="{{ route('owner.stock-counts.create') }}">
        Go to Stock Count
    </a>

</body>
</html>