<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - CashTrack Secure</title>
</head>

<body>

    <h1>CashTrack Secure</h1>

    <h2>Product Management</h2>

    <p>
        <a href="{{ route('owner.dashboard') }}">
            Back to Dashboard
        </a>
    </p>

    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <p>
        <a href="{{ route('owner.products.create') }}">
            + Add Product
        </a>
    </p>

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Selling Price</th>
                <th>Reorder Level</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

            @forelse($products as $product)

                <tr>
                    <td>{{ $product->sku }}</td>

                    <td>{{ $product->name }}</td>

                    <td>
                        RM {{ number_format($product->selling_price, 2) }}
                    </td>

                    <td>{{ $product->reorder_level }}</td>

                    <td>
                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                    </td>

                    <td>

                        <a href="{{ route('owner.products.edit', $product) }}">
                            Edit
                        </a>

                        @if($product->is_active)

                            <form
                                method="POST"
                                action="{{ route('owner.products.deactivate', $product) }}"
                                style="display:inline"
                            >
                                @csrf
                                @method('PATCH')

                                <button type="submit">
                                    Deactivate
                                </button>
                            </form>

                        @endif

                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="6">
                        No products found.
                    </td>
                </tr>

            @endforelse

        </tbody>
    </table>

</body>
</html>