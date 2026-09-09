@extends('layouts.cashier')

@section('title', 'New Sale - CashTrack Secure')

@section('content')

<div class="mb-4">
    <h2 class="fw-bold mb-1">
        Record Sale
    </h2>

    <p class="text-muted mb-0">
        Record a completed product sale.
    </p>
</div>

<div class="card content-card shadow-sm">

    <div class="card-body p-4">

        <form
            method="POST"
            action="{{ route('cashier.sales.store') }}"
        >

            @csrf

            <div class="mb-3">

                <label class="form-label fw-semibold">
                    Product
                </label>

                <select
                    name="product_id"
                    class="form-select"
                    required
                >

                    <option value="">
                        -- Select Product --
                    </option>

                    @foreach($products as $product)

                        <option
                            value="{{ $product->id }}"
                            @selected(old('product_id') == $product->id)
                        >
                            {{ $product->sku }}
                            -
                            {{ $product->name }}
                            -
                            RM {{ number_format($product->selling_price, 2) }}
                        </option>

                    @endforeach

                </select>

            </div>

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Quantity
                </label>

                <input
                    type="number"
                    name="quantity"
                    min="1"
                    value="{{ old('quantity', 1) }}"
                    class="form-control"
                    required
                >

            </div>

            <button
                type="submit"
                class="btn btn-dark"
            >
                Complete Sale
            </button>

        </form>

    </div>
</div>

@endsection