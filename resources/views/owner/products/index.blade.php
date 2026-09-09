@extends('layouts.owner')

@section('title', 'Products - CashTrack Secure')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 class="fw-bold mb-1">
            Product Management
        </h2>

        <p class="text-muted mb-0">
            Manage products available in the inventory system.
        </p>
    </div>

    <a
        href="{{ route('owner.products.create') }}"
        class="btn btn-dark"
    >
        + Add Product
    </a>

</div>

<div class="card content-card shadow-sm">

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product</th>
                        <th>Selling Price</th>
                        <th>Reorder Level</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($products as $product)

                    <tr>

                        <td>
                            <code>{{ $product->sku }}</code>
                        </td>

                        <td class="fw-semibold">
                            {{ $product->name }}
                        </td>

                        <td>
                            RM {{ number_format($product->selling_price, 2) }}
                        </td>

                        <td>
                            {{ $product->reorder_level }}
                        </td>

                        <td>

                            @if($product->is_active)

                                <span class="badge bg-success">
                                    ACTIVE
                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    INACTIVE
                                </span>

                            @endif

                        </td>

                        <td>

                            <div class="d-flex gap-2">

                                <a
                                    href="{{ route('owner.products.edit', $product) }}"
                                    class="btn btn-outline-dark btn-sm"
                                >
                                    Edit
                                </a>

                                @if($product->is_active)

                                    <form
                                        method="POST"
                                        action="{{ route('owner.products.deactivate', $product) }}"
                                        onsubmit="return confirm('Deactivate this product?')"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="btn btn-outline-danger btn-sm"
                                        >
                                            Deactivate
                                        </button>

                                    </form>

                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="text-center text-muted py-4"
                        >
                            No products found.
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection