@extends('layouts.owner')

@section('title', 'Edit Product - CashTrack Secure')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 class="fw-bold mb-1">Edit Product</h2>

        <p class="text-muted mb-0">
            Update product information.
        </p>
    </div>

    <a
        href="{{ route('owner.products.index') }}"
        class="btn btn-outline-secondary"
    >
        Back to Products
    </a>

</div>

<div class="card content-card shadow-sm">

    <div class="card-body p-4">

        <form
            method="POST"
            action="{{ route('owner.products.update', $product) }}"
        >

            @csrf
            @method('PUT')

            <div class="row g-3">

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        SKU
                    </label>

                    <input
                        type="text"
                        name="sku"
                        value="{{ old('sku', $product->sku) }}"
                        class="form-control"
                        required
                    >

                </div>

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Product Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $product->name) }}"
                        class="form-control"
                        required
                    >

                </div>

                <div class="col-12">

                    <label class="form-label fw-semibold">
                        Description
                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="3"
                    >{{ old('description', $product->description) }}</textarea>

                </div>

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Selling Price (RM)
                    </label>

                    <input
                        type="number"
                        name="selling_price"
                        step="0.01"
                        min="0"
                        value="{{ old('selling_price', $product->selling_price) }}"
                        class="form-control"
                        required
                    >

                </div>

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Reorder Level
                    </label>

                    <input
                        type="number"
                        name="reorder_level"
                        min="0"
                        value="{{ old('reorder_level', $product->reorder_level) }}"
                        class="form-control"
                        required
                    >

                </div>

            </div>

            <div class="d-flex gap-2 mt-4">

                <button
                    type="submit"
                    class="btn btn-dark"
                >
                    Update Product
                </button>

                <a
                    href="{{ route('owner.products.index') }}"
                    class="btn btn-outline-secondary"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

@endsection