<nav aria-label="Inventory navigation" class="ct-subnav">
    <a href="{{ route('owner.products.index') }}" @if(request()->routeIs('owner.products.*')) aria-current="page" @endif><flux:icon.cube /><span>Items & Current Stock</span></a>
    <a href="{{ route('owner.stock-in.create') }}" @if(request()->routeIs('owner.stock-in.*', 'owner.receipts.*')) aria-current="page" @endif><flux:icon.arrow-down-tray /><span>Receive Stock</span></a>
    <a href="{{ route('owner.stock-counts.index') }}" @if(request()->routeIs('owner.stock-counts.*')) aria-current="page" @endif><flux:icon.clipboard-document-check /><span>Stock Check</span></a>
    <a href="{{ route('owner.alerts.index') }}" @if(request()->routeIs('owner.alerts.*')) aria-current="page" @endif><flux:icon.exclamation-triangle /><span>Stock Issues</span></a>
    <a href="{{ route('withdrawals.create') }}" @if(request()->routeIs('withdrawals.*')) aria-current="page" @endif><flux:icon.minus-circle /><span>Remove Stock</span></a>
</nav>
