<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $title ?? 'Dashboard') — CashTrack Secure</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body{margin:0;background:#f4f6f8;color:#182b3a;font-family:system-ui,sans-serif;line-height:1.6}a{color:#0d6562}header{background:#143f40;color:white}header a{color:white}.ct-wrap{max-width:1200px;margin:auto;padding:24px}.ct-nav{display:flex;flex-wrap:wrap;gap:8px 20px;align-items:center}.ct-nav a{padding:6px 0}.ct-panel{background:white;border:1px solid #dbe3e6;border-radius:14px;padding:24px;margin:20px 0;box-shadow:0 3px 10px #18343c05}.ct-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:18px}.ct-field{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}.ct-field input,.ct-field select,.ct-field textarea,.ct-input{width:100%;border:1px solid #9aaeb4;border-radius:7px;background:white;color:#182b3a;padding:9px 12px;box-sizing:border-box}.ct-button{display:inline-block;background:#146764;color:white;border:0;border-radius:7px;padding:10px 17px;text-decoration:none;cursor:pointer;font-weight:600}.ct-secondary{background:#e6efef;color:#184c4b}.ct-danger{background:#a53237;color:white}.ct-muted{color:#52666f;font-size:.93rem}.ct-alert{padding:14px 18px;border-radius:8px;background:#e8f5ef;margin:16px 0}.ct-error{background:#fff0ee;color:#84272a}.ct-table{overflow-x:auto}table{width:100%;border-collapse:collapse;text-align:left}th,td{padding:12px;border-bottom:1px solid #dee6e8;vertical-align:top}th{font-size:.88rem;background:#f1f5f6}h1{font-size:1.9rem;font-weight:750;line-height:1.25;margin:15px 0}h2{font-size:1.2rem;font-weight:700;margin:12px 0}h3{font-weight:700}p{margin:8px 0}.ct-number{font-size:2rem;font-weight:750}.ct-row{border:1px solid #d8e3e5;border-radius:9px;padding:18px;margin:14px 0}.ct-actions{display:flex;flex-wrap:wrap;gap:12px;align-items:center}.ct-badge{display:inline-block;border-radius:20px;background:#e4efed;padding:3px 10px;font-size:.82rem}.ct-code{white-space:pre-wrap;overflow-wrap:anywhere;font-size:.85rem}input:focus,select:focus,textarea:focus,button:focus,a:focus{outline:3px solid #c78b39;outline-offset:2px}[hidden]{display:none!important}@media(max-width:600px){.ct-wrap{padding:16px}.ct-panel{padding:16px}h1{font-size:1.5rem}}@media print{header,.ct-no-print,button,form{display:none!important}.ct-panel{border:0;box-shadow:none;padding:0}body{background:white}.ct-wrap{max-width:none;padding:0}table{font-size:10pt}tr{break-inside:avoid}}
        .ct-nav a[aria-current="page"],.ct-subnav a[aria-current="page"]{font-weight:750;text-decoration:underline;text-underline-offset:8px}.ct-account{position:relative}.ct-account summary{cursor:pointer;padding:8px}.ct-account-menu{position:absolute;right:0;z-index:20;min-width:210px;padding:16px;background:white;color:#182b3a;border:1px solid #dbe3e6;border-radius:10px;box-shadow:0 8px 24px #182b3a22}.ct-account-menu a{display:block;color:#0d6562;padding:8px 0}.ct-subnav{display:flex;flex-wrap:wrap;gap:12px 24px;border-bottom:1px solid #dbe3e6;padding:0 0 18px;margin-bottom:24px}.ct-subnav a{padding:6px 0}.ct-values dt{font-weight:600}.ct-values dd{margin:0 0 8px;overflow-wrap:anywhere}.ct-panel summary{cursor:pointer;font-weight:600}.ct-table td{overflow-wrap:anywhere}body{--color-accent:#146764;--color-accent-content:#146764;--color-accent-foreground:white}@media print{.ct-subnav,details{display:none!important}}
    </style>
    @livewireStyles
</head>
<body @if(auth()->user()?->role?->name === 'Owner') class="ct-owner{{ request()->routeIs('owner.dashboard', 'owner.products.*', 'owner.stock-in.*', 'owner.receipts.*', 'owner.stock-counts.*', 'owner.alerts.*', 'withdrawals.*', 'cashier.sales.create', 'owner.reports.*', 'owner.audit.*') ? ' ct-refresh-page' : '' }}" @endif>
<a class="ct-skip-link" href="#main-content">Skip to content</a>
@if(auth()->user()?->role?->name === 'Owner')
    @include('planb.owner-shell')
@else
<header><div class="ct-wrap"><div class="ct-actions ct-header-top" style="justify-content:space-between">
    <a class="ct-brand" href="{{ route('home') }}" style="font-size:1.3rem;font-weight:750">CashTrack Secure</a>
    @auth
        <details class="ct-account">
            <summary>{{ auth()->user()->name }} · Account</summary>
            <div class="ct-account-menu">
                <a href="{{ route('profile.edit') }}">Profile</a>
                <a href="{{ route('security.edit') }}">Security</a>
                <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="ct-button ct-secondary">Logout</button></form>
            </div>
        </details>
    @endauth
</div>
@auth
<nav aria-label="Main navigation" class="ct-nav" style="margin-top:16px">
    <a href="{{ route('dashboard') }}" @if(request()->routeIs('*.dashboard', 'dashboard')) aria-current="page" @endif>Dashboard</a>
    @if(auth()->user()->role?->name === 'Owner')
        <a href="{{ route('owner.products.index') }}" @if(request()->routeIs('owner.products.*', 'owner.stock-in.*', 'owner.receipts.*', 'owner.stock-counts.*', 'owner.alerts.*', 'withdrawals.*')) aria-current="page" @endif>Inventory</a>
    @endif
    <a href="{{ route('cashier.sales.create') }}" @if(request()->routeIs('cashier.sales.*', 'sales.*')) aria-current="page" @endif>Sales</a>
    @if(auth()->user()->role?->name === 'Owner')
        <a href="{{ route('owner.reports.index') }}" @if(request()->routeIs('owner.reports.*')) aria-current="page" @endif>Reports</a>
        <a href="{{ route('owner.users.index') }}" @if(request()->routeIs('owner.users.*')) aria-current="page" @endif>Staff</a>
        <a href="{{ route('owner.audit.index') }}" @if(request()->routeIs('owner.audit.*')) aria-current="page" @endif>Activity</a>
    @else
        <a href="{{ route('withdrawals.create') }}">Remove Stock</a>
    @endif
</nav>
@endauth</div></header>
@endif
<main id="main-content" class="ct-wrap" tabindex="-1">
    @if(session('success'))<div role="status" class="ct-alert">@include('planb.message', ['message' => session('success')])</div>@endif
    @if(session('status'))<div role="status" class="ct-alert">@include('planb.message', ['message' => session('status')])</div>@endif
    @if($errors->any())<div role="alert" class="ct-alert ct-error"><strong>Please check the following before saving:</strong><ul>@foreach($errors->all() as $error)<li>@include('planb.message', ['message' => $error])</li>@endforeach</ul></div>@endif
    @auth
        @if(auth()->user()->role?->name === 'Owner' && request()->routeIs('owner.products.*', 'owner.stock-in.*', 'owner.receipts.*', 'owner.stock-counts.*', 'owner.alerts.*', 'withdrawals.*'))
            @include('planb.inventory-nav')
        @endif
    @endauth
    @yield('content')
    <footer class="ct-muted ct-footer" style="margin-top:36px;border-top:1px solid #dbe3e6;padding-top:15px">CashTrack Secure</footer>
</main>
@stack('scripts')
@persist('toast')
    <flux:toast.group><flux:toast /></flux:toast.group>
@endpersist
@fluxScripts
</body></html>
