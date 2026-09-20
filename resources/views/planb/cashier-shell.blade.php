<aside class="ct-sidebar" x-data="{ menuOpen: false }" data-open="false" x-bind:data-open="menuOpen ? 'true' : 'false'"
       x-on:keydown.escape="if (menuOpen) { menuOpen = false; $refs.menuToggle.focus() }">
    <div class="ct-sidebar-heading">
        <a class="ct-brand" href="{{ route('home') }}"><span class="ct-brand-mark" aria-hidden="true"><flux:icon.cube /></span><span>CashTrack <strong>Secure</strong></span></a>
        <button class="ct-menu-toggle" type="button" x-ref="menuToggle" x-on:click="menuOpen = !menuOpen"
                aria-controls="cashier-navigation" aria-expanded="false" x-bind:aria-expanded="menuOpen" aria-label="Toggle navigation"><flux:icon.bars-3 /></button>
    </div>
    <nav id="cashier-navigation" aria-label="Main navigation" class="ct-nav">
        <a href="{{ route('dashboard') }}" @if(request()->routeIs('cashier.dashboard')) aria-current="page" @endif><flux:icon.home /><span>Dashboard</span></a>
        <a href="{{ route('cashier.sales.create') }}" @if(request()->routeIs('cashier.sales.create')) aria-current="page" @endif><flux:icon.banknotes /><span>Sales</span></a>
        <a href="{{ route('withdrawals.create') }}" @if(request()->routeIs('withdrawals.create')) aria-current="page" @endif><flux:icon.minus-circle /><span>Remove Stock</span></a>
    </nav>
    <p class="ct-sidebar-caption">Sales & stock removal</p>
</aside>
<header class="ct-topbar">
    <span class="ct-workspace-label">Cashier <span aria-hidden="true">/</span> <strong>@yield('title', $title ?? 'Dashboard')</strong></span>
    <details class="ct-account">
        <summary><span class="ct-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span>{{ auth()->user()->name }}</span><flux:icon.chevron-down /></summary>
        <div class="ct-account-menu">
            <a href="{{ route('profile.edit') }}">Profile</a>
            <a href="{{ route('security.edit') }}">Security</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="ct-button ct-secondary">Logout</button></form>
        </div>
    </details>
</header>
