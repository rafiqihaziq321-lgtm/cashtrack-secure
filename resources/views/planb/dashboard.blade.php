@extends('layouts.planb')
@section('title', 'Dashboard')
@section('content')
@if(auth()->user()->role?->name === 'Owner')
    @include('planb.owner-dashboard')
@else
<h1>Dashboard</h1>
<p class="ct-muted">{{ \Carbon\CarbonImmutable::parse($day)->format('j F Y') }} @if(auth()->user()->role?->name === 'Cashier') · Your sales @endif</p>
<div class="ct-grid">
    <div class="ct-panel"><h2>Today's Sales</h2><p class="ct-number">RM {{ number_format((float) $total, 2) }}</p><p>{{ $transactions }} transactions</p></div>
    <div class="ct-panel"><h2>Low Stock Items</h2><p class="ct-number">{{ $low->count() }}</p><p>Items at or below their low stock alert</p></div>
    @if(auth()->user()->role?->name === 'Owner')
        <div class="ct-panel"><h2>Stock Issues</h2><p class="ct-number">{{ $alerts }}</p><a href="{{ route('owner.alerts.index') }}">Review stock issues</a></div>
    @endif
</div>
<div class="ct-actions">
    <a class="ct-button" href="{{ route('cashier.sales.create') }}">New Sale</a>
    @if(auth()->user()->role?->name === 'Owner')
        <a class="ct-button ct-secondary" href="{{ route('owner.stock-in.create') }}">Receive Stock</a>
        <a class="ct-button ct-secondary" href="{{ route('owner.stock-counts.create') }}">Start Stock Check</a>
    @endif
</div>
<section class="ct-panel"><h2>Items to Restock</h2>
    <div class="ct-table"><table><thead><tr><th>Item</th><th>Current Stock</th><th>Low Stock Alert</th><th>Action</th></tr></thead><tbody>
        @forelse($low as $product)
            <tr><td>{{ $product->name }}<br><small>{{ $product->sku }}</small></td><td>{{ (int) $product->balance }} @include('planb.unit', ['unit' => $product->unit])</td><td>{{ $product->reorder_level }}</td><td>
                @if(auth()->user()->role?->name === 'Owner')
                    <a href="{{ route('owner.stock-in.create') }}">Receive Stock</a> · <a href="{{ route('owner.stock-counts.create', ['product_id' => $product->id]) }}">Check Stock</a>
                @else
                    Let the Owner know
                @endif
            </td></tr>
        @empty
            <tr><td colspan="4">No items are running low.</td></tr>
        @endforelse
    </tbody></table></div>
</section>
@endif
@endsection
