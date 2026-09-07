<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Record Sale - CashTrack Secure</title>
</head>

<body>

    <h1>CashTrack Secure</h1>

    <h2>Record Sale</h2>

    <p>
        Logged in as:
        {{ auth()->user()->name }}
    </p>

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

    <form method="POST" action="{{ route('cashier.sales.store') }}">

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
                        -
                        RM {{ number_format($product->selling_price, 2) }}
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
                value="1"
                required
            >
        </div>

        <br>

        <button type="submit">
            Confirm Sale
        </button>

    </form>

    <br>

    <a href="{{ route('cashier.dashboard') }}">
        Back to Dashboard
    </a>

</body>
</html>