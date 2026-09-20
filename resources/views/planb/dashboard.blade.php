@extends('layouts.planb')
@section('title', 'Dashboard')
@section('content')
@if(auth()->user()->role?->name === 'Owner')
    @include('planb.owner-dashboard')
@else
<div class="ct-page-heading"><div><h1>Dashboard</h1><p class="ct-muted">Your sales and stock at a glance.</p></div><p class="ct-date">{{ \Carbon\CarbonImmutable::parse($day)->format('j F Y') }}</p></div>
<div class="ct-grid ct-metrics ct-cashier-metrics" aria-label="Cashier overview">
    <section class="ct-panel ct-metric" aria-labelledby="cashier-sales-heading"><div class="ct-metric-heading"><h2 id="cashier-sales-heading">Today's Sales</h2><span class="ct-metric-icon"><flux:icon.banknotes /></span></div><p class="ct-number"><span class="ct-currency">RM</span> {{ number_format((float) $total, 2) }}</p><p class="ct-muted">{{ $transactions }} {{ $transactions === 1 ? 'transaction' : 'transactions' }} · Your sales</p></section>
    <section class="ct-panel ct-metric" aria-labelledby="cashier-low-heading"><div class="ct-metric-heading"><h2 id="cashier-low-heading">Low Stock Items</h2><span class="ct-metric-icon ct-icon-warning"><flux:icon.cube /></span></div><p class="ct-number">{{ $low->count() }}</p><p class="ct-muted">Items at or below their low stock alert</p></section>
    @if(auth()->user()->role?->name === 'Owner')
        <div class="ct-panel"><h2>Stock Issues</h2><p class="ct-number">{{ $alerts }}</p><a href="{{ route('owner.alerts.index') }}">Review stock issues</a></div>
    @endif
</div>
<section class="ct-quick-actions" aria-labelledby="cashier-actions-heading"><h2 id="cashier-actions-heading">Quick actions</h2><div class="ct-actions">
    <a class="ct-button" href="{{ route('cashier.sales.create') }}"><flux:icon.plus />New Sale</a>
    @if(auth()->user()->role?->name === 'Owner')
        <a class="ct-button ct-secondary" href="{{ route('owner.stock-in.create') }}">Receive Stock</a>
        <a class="ct-button ct-secondary" href="{{ route('owner.stock-counts.create') }}">Start Stock Check</a>
    @endif
</div></section>
<section class="ct-panel ct-table-panel"><div class="ct-section-heading"><h2>Items to Restock</h2><p class="ct-muted">Let the Owner know when items are running low.</p></div>
    <div class="ct-table" role="region" aria-label="Items to restock" tabindex="0"><table><thead><tr><th scope="col">Item</th><th scope="col" class="ct-numeric">Current Stock</th><th scope="col" class="ct-numeric">Low Stock Alert</th><th scope="col">Action</th></tr></thead><tbody>
        @forelse($low as $product)
            <tr><td><span class="ct-item-name">{{ $product->name }}</span><span class="ct-item-code">{{ $product->sku }}</span></td><td class="ct-numeric"><span class="ct-badge ct-badge-warning">{{ (int) $product->balance }} @include('planb.unit', ['unit' => $product->unit])</span></td><td class="ct-numeric">{{ $product->reorder_level }}</td><td>
                @if(auth()->user()->role?->name === 'Owner')
                    <a href="{{ route('owner.stock-in.create') }}">Receive Stock</a> · <a href="{{ route('owner.stock-counts.create', ['product_id' => $product->id]) }}">Check Stock</a>
                @else
                    Let the Owner know
                @endif
            </td></tr>
        @empty
            <tr><td colspan="4"><div class="ct-empty"><strong>No items are running low.</strong><p>Items at or below their low stock alert will appear here.</p></div></td></tr>
        @endforelse
    </tbody></table></div>
</section>
@endif
@endsection
