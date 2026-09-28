<div class="ct-page-heading">
    <div><h1>Dashboard</h1><p class="ct-muted">Your store at a glance.</p></div>
    <p class="ct-date">{{ \Carbon\CarbonImmutable::parse($day)->format('j F Y') }}</p>
</div>

<div class="ct-grid ct-metrics" aria-label="Store overview">
    <section class="ct-panel ct-metric" aria-labelledby="sales-heading">
        <div class="ct-metric-heading"><h2 id="sales-heading">Today's Sales</h2><span class="ct-metric-icon"><flux:icon.banknotes /></span></div>
        <p class="ct-number"><span class="ct-currency">RM</span> {{ number_format((float) $total, 2) }}</p>
        <p class="ct-muted">{{ $transactions }} {{ $transactions === 1 ? 'transaction' : 'transactions' }} today</p>
        @if($transactions === 0)<p class="ct-muted ct-metric-help">No sales recorded yet today.</p>@endif
    </section>
    <section class="ct-panel ct-metric" aria-labelledby="low-stock-heading">
        <div class="ct-metric-heading"><h2 id="low-stock-heading">Low Stock Items</h2><span class="ct-metric-icon ct-icon-warning"><flux:icon.cube /></span></div>
        <p class="ct-number">{{ $low->count() }}</p>
        <p class="ct-muted">At or below their low stock alert</p>
    </section>
    <section class="ct-panel ct-metric" aria-labelledby="issues-heading">
        <div class="ct-metric-heading"><h2 id="issues-heading">Stock Issues</h2><span class="ct-metric-icon ct-icon-warning"><flux:icon.exclamation-triangle /></span></div>
        <p class="ct-number">{{ $alerts }}</p>
        @if($alerts === 0)<p class="ct-muted ct-metric-help">No stock issues need review.</p>@endif
        <a class="ct-text-action" href="{{ route('owner.alerts.index') }}">Review stock issues <span aria-hidden="true">→</span></a>
    </section>
</div>

<section class="ct-quick-actions" aria-labelledby="quick-actions-heading">
    <h2 id="quick-actions-heading">Quick actions</h2>
    <div class="ct-actions">
        <a class="ct-button" href="{{ route('cashier.sales.create') }}"><flux:icon.plus />New Sale</a>
        <a class="ct-button ct-secondary" href="{{ route('owner.stock-in.create') }}"><flux:icon.arrow-down-tray />Receive Stock</a>
        <a class="ct-button ct-secondary" href="{{ route('owner.stock-counts.create') }}"><flux:icon.clipboard-document-check />Start Stock Check</a>
    </div>
</section>

<section class="ct-panel ct-table-panel" aria-labelledby="restock-heading">
    <div class="ct-section-heading"><h2 id="restock-heading">Items to Restock</h2><p class="ct-muted">Review current stock before your next delivery.</p></div>
    <div class="ct-table" role="region" aria-label="Items to restock" tabindex="0">
        <table>
            <thead><tr><th scope="col">Item</th><th scope="col" class="ct-numeric">Current Stock</th><th scope="col" class="ct-numeric">Low Stock Alert</th><th scope="col">Actions</th></tr></thead>
            <tbody>
                @forelse($low as $product)
                    <tr>
                        <th scope="row"><span class="ct-item-name">{{ $product->name }}</span><span class="ct-item-code">{{ $product->sku }}</span></th>
                        <td class="ct-numeric"><span class="ct-badge ct-badge-warning">{{ (int) $product->balance }} @include('planb.unit', ['unit' => $product->unit])</span></td>
                        <td class="ct-numeric">{{ $product->reorder_level }}</td>
                        <td><div class="ct-actions ct-table-actions"><a href="{{ route('owner.stock-in.create') }}">Receive Stock</a><a href="{{ route('owner.stock-counts.create', ['product_id' => $product->id]) }}">Check Stock</a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="ct-empty"><strong>No items are running low.</strong><p>Items at or below their low stock alert will appear here.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
