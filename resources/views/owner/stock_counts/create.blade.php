@extends('layouts.owner')

@section('title', 'New Stock Count - CashTrack Secure')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 class="fw-bold mb-1">New Stock Count</h2>

        <p class="text-muted mb-0">
            Compare physical inventory with expected ledger stock.
        </p>
    </div>

    <a
        href="{{ route('owner.stock-counts.index') }}"
        class="btn btn-outline-secondary"
    >
        Stock Count History
    </a>

</div>

<div class="card content-card shadow-sm">

    <div class="card-body p-4">

        <form
            method="POST"
            action="{{ route('owner.stock-counts.store') }}"
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
                            {{ $product->sku }} - {{ $product->name }}
                        </option>

                    @endforeach

                </select>

            </div>

            <div class="mb-3">

                <label class="form-label fw-semibold">
                    Physical Quantity
                </label>

                <input
                    type="number"
                    name="physical_quantity"
                    min="0"
                    value="{{ old('physical_quantity') }}"
                    class="form-control"
                    required
                >

                <div class="form-text">
                    Enter the quantity physically counted.
                </div>

            </div>

            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Notes
                </label>

                <textarea
                    name="notes"
                    class="form-control"
                    rows="3"
                >{{ old('notes') }}</textarea>

            </div>

            <button
                type="submit"
                class="btn btn-dark"
            >
                Record Stock Count
            </button>

        </form>

    </div>
</div>

@endsection