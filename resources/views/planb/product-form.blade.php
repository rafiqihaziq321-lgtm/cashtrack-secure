@extends('layouts.planb')
@section('title', 'Item Details')
@section('content')
@if($product)
    <div class="ct-page-heading"><div><h1>Edit Item</h1>
    <p class="ct-muted">Item Code: {{ $product->sku }}. Use Receive Stock to add stock. The unit cannot change after stock has been recorded.</p></div></div>
    <form class="ct-panel ct-form-panel" method="post" action="{{ route('owner.products.update', $product) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
        <section class="ct-form-section"><h2>Item details</h2>
        @include('planb.catalog-fields', ['prefix' => '', 'suffix' => '', 'values' => old() ?: $product->toArray()])
        <label class="ct-field">Availability<select name="is_active">
            <option value="1" @selected(old('is_active', $product->is_active))>Available</option>
            <option value="0" @selected(!old('is_active', $product->is_active))>Unavailable</option>
        </select></label></section>
        <div class="ct-form-actions"><button class="ct-button">Save Item</button></div>
    </form>
@else
    <h1>Add an Item with Receive Stock</h1>
    <p>Add the item details and received quantity together.</p>
    <a class="ct-button" href="{{ route('owner.stock-in.create') }}">Receive Stock</a>
@endif
@endsection
