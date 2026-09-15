<nav aria-label="Inventory navigation" class="ct-subnav">
    <a href="{{ route('owner.products.index') }}" @if(request()->routeIs('owner.products.*')) aria-current="page" @endif>Items & Current Stock</a>
    <a href="{{ route('owner.stock-in.create') }}" @if(request()->routeIs('owner.stock-in.*', 'owner.receipts.*')) aria-current="page" @endif>Receive Stock</a>
    <a href="{{ route('owner.stock-counts.index') }}" @if(request()->routeIs('owner.stock-counts.*')) aria-current="page" @endif>Stock Check</a>
    <a href="{{ route('owner.alerts.index') }}" @if(request()->routeIs('owner.alerts.*')) aria-current="page" @endif>Stock Issues</a>
    <a href="{{ route('withdrawals.create') }}" @if(request()->routeIs('withdrawals.*')) aria-current="page" @endif>Remove Stock</a>
</nav>
