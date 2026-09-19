@extends('layouts.planb')
@section('title', 'Inventory')
@section('content')
<div class="ct-page-heading">
    <div><h1>Inventory</h1><p class="ct-muted">Your items, stock levels and receiving in one place.</p></div>
    <a class="ct-button" href="{{ route('owner.stock-in.create') }}"><flux:icon.arrow-down-tray />Receive Stock</a>
</div>

<section class="ct-panel ct-table-panel" aria-labelledby="inventory-heading">
    <div class="ct-section-heading"><h2 id="inventory-heading">Items & Current Stock</h2></div>
    <form method="get" action="{{ route('owner.products.index') }}" class="ct-search" role="search" aria-label="Search inventory">
        <label class="ct-field" for="inventory-search">Search items
            <input id="inventory-search" type="search" name="q" value="{{ $search }}" maxlength="150" placeholder="Item name, Item Code or barcode">
        </label>
        <button class="ct-button ct-secondary" type="submit"><flux:icon.magnifying-glass />Search</button>
        @if($search !== '')<a class="ct-text-action" href="{{ route('owner.products.index') }}">Clear search</a>@endif
    </form>
    <div class="ct-table" role="region" aria-label="Inventory items" tabindex="0">
        <table>
            <thead><tr><th scope="col">Item / Item Code</th><th scope="col" class="ct-numeric">Selling Price</th><th scope="col">Availability</th><th scope="col" class="ct-numeric">Current Stock</th><th scope="col" class="ct-numeric">Low Stock Alert</th><th scope="col">Action</th></tr></thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <th scope="row"><span class="ct-item-name">{{ $product->name }}</span><span class="ct-item-code">{{ $product->sku }}</span><span class="ct-item-meta">{{ $product->barcode ? 'Barcode: '.$product->barcode : 'No barcode' }}</span></th>
                        <td class="ct-numeric ct-price">RM {{ $product->selling_price }}</td>
                        <td><span class="ct-badge {{ $product->is_active ? 'ct-badge-success' : 'ct-badge-neutral' }}">{{ $product->is_active ? 'Available' : 'Unavailable' }}</span></td>
                        <td class="ct-numeric"><strong class="ct-stock">{{ (int) $product->balance }}</strong><span class="ct-item-meta">@include('planb.unit', ['unit' => $product->unit])</span>@if($product->is_active && (int) $product->balance <= $product->reorder_level)<span class="ct-stock-state"><span class="ct-badge ct-badge-warning">Low stock</span></span>@endif</td>
                        <td class="ct-numeric">{{ $product->reorder_level }}</td>
                        <td><a class="ct-button ct-secondary" href="{{ route('owner.products.edit', $product) }}" aria-label="Edit {{ $product->name }}">Edit Item</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="ct-empty">
                        @if($search !== '')
                            <strong>No items match “{{ $search }}”.</strong><p>Try another name, Item Code or barcode.</p>
                        @else
                            <strong>Your inventory is ready for its first item.</strong><p>Use Receive Stock to add item details and the received quantity together.</p>
                            <a class="ct-button ct-secondary" href="{{ route('owner.stock-in.create') }}">Receive Stock</a>
                        @endif
                    </div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())<div class="ct-pagination">{{ $products->links() }}</div>@endif
</section>
@endsection
