@extends('layouts.owner')

@section('title', 'Stock In - CashTrack Secure')

@section('content')

<div class="mb-4">
    <h2 class="fw-bold mb-1">Stock In</h2>
    <p class="text-muted mb-0">
        Record incoming inventory into the inventory ledger.
    </p>
</div>

<div class="card content-card shadow-sm">
    <div class="card-body p-4">

        <form method="POST"
              action="{{ route('owner.stock-in.store') }}">

            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Product
                </label>

                <select name="product_id"
                        class="form-select"
                        required>

                    <option value="">
                        -- Select Product --
                    </option>

                    @foreach($products as $product)
                        <option
                            value="{{ $product->id }}"
                            @selected(old('product_id') == $product->id)
                        >
                            {{ $product->sku }} - {{ $product->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Quantity
                </label>

                <input
                    type="number"
                    name="quantity"
                    min="1"
                    value="{{ old('quantity') }}"
                    class="form-control"
                    required
                >
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Notes
                </label>

                <textarea
                    name="notes"
                    class="form-control"
                    rows="3"
                    placeholder="Example: Supplier delivery"
                >{{ old('notes') }}</textarea>
            </div>

            <button
                type="submit"
                class="btn btn-dark"
            >
                Record Stock In
            </button>

        </form>

    </div>
</div>

@endsection