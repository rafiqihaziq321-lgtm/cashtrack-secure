@extends('layouts.planb')
@section('title', 'Inventory')
@section('content')
<h1>Inventory</h1>
<p>Manage your items and current stock here. Receive Stock also lets you add a new item.</p>
<a class="ct-button" href="{{ route('owner.stock-in.create') }}">Receive Stock</a>
<form method="get" class="ct-panel">
    <label class="ct-field">Search by item name, Item Code or barcode<input name="q" value="{{ $search }}" maxlength="150"></label>
    <button class="ct-button">Search</button>
</form>
<div class="ct-panel ct-table"><table><thead><tr><th>Item / Item Code</th><th>Selling Price</th><th>Availability</th><th>Current Stock</th><th>Low Stock Alert</th><th>Action</th></tr></thead><tbody>
@forelse($products as $product)
    <tr><td>{{ $product->name }}<br><small>{{ $product->sku }} · {{ $product->barcode ?: 'No barcode' }}</small></td><td>RM {{ $product->selling_price }}</td><td>{{ $product->is_active ? 'Available' : 'Unavailable' }}</td><td>{{ (int) $product->balance }} @include('planb.unit', ['unit' => $product->unit])</td><td>{{ $product->reorder_level }}</td><td><a href="{{ route('owner.products.edit', $product) }}">Edit Item</a></td></tr>
@empty
    <tr><td colspan="6">No items found. Use Receive Stock to add an item and its starting quantity.</td></tr>
@endforelse
</tbody></table>{{ $products->links() }}</div>
@endsection
