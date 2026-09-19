<aside class="ct-sidebar" x-data="{ menuOpen: false }" data-open="false" x-bind:data-open="menuOpen ? 'true' : 'false'"
       x-on:keydown.escape="if (menuOpen) { menuOpen = false; $refs.menuToggle.focus() }">
    <div class="ct-sidebar-heading">
        <a class="ct-brand" href="{{ route('home') }}"><span class="ct-brand-mark" aria-hidden="true"><flux:icon.cube /></span><span>CashTrack <strong>Secure</strong></span></a>
        <button class="ct-menu-toggle" type="button" x-ref="menuToggle" x-on:click="menuOpen = !menuOpen"
                aria-controls="owner-navigation" aria-expanded="false" x-bind:aria-expanded="menuOpen" aria-label="Toggle navigation"><flux:icon.bars-3 /></button>
    </div>
    <nav id="owner-navigation" aria-label="Main navigation" class="ct-nav">
        <a href="{{ route('dashboard') }}" @if(request()->routeIs('*.dashboard', 'dashboard')) aria-current="page" @endif><flux:icon.home /><span>Dashboard</span></a>
        <a href="{{ route('owner.products.index') }}" @if(request()->routeIs('owner.products.*', 'owner.stock-in.*', 'owner.receipts.*', 'owner.stock-counts.*', 'owner.alerts.*', 'withdrawals.*')) aria-current="page" @endif><flux:icon.cube /><span>Inventory</span></a>
        <a href="{{ route('cashier.sales.create') }}" @if(request()->routeIs('cashier.sales.*', 'sales.*')) aria-current="page" @endif><flux:icon.banknotes /><span>Sales</span></a>
        <a href="{{ route('owner.reports.index') }}" @if(request()->routeIs('owner.reports.*')) aria-current="page" @endif><flux:icon.chart-bar /><span>Reports</span></a>
        <a href="{{ route('owner.users.index') }}" @if(request()->routeIs('owner.users.*')) aria-current="page" @endif><flux:icon.users /><span>Staff</span></a>
        <a href="{{ route('owner.audit.index') }}" @if(request()->routeIs('owner.audit.*')) aria-current="page" @endif><flux:icon.clock /><span>Activity</span></a>
    </nav>
    <p class="ct-sidebar-caption">Sales & inventory</p>
</aside>
<header class="ct-topbar">
    <span class="ct-workspace-label">Workspace <span aria-hidden="true">/</span> <strong>@yield('title', $title ?? 'Dashboard')</strong></span>
    <details class="ct-account">
        <summary><span class="ct-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span>{{ auth()->user()->name }}</span><flux:icon.chevron-down /></summary>
        <div class="ct-account-menu">
            <a href="{{ route('profile.edit') }}">Profile</a>
            <a href="{{ route('security.edit') }}">Security</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="ct-button ct-secondary">Logout</button></form>
        </div>
    </details>
</header>
