<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CashTrack Secure') — Plan B</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body{margin:0;background:#f4f6f8;color:#182b3a;font-family:system-ui,sans-serif;line-height:1.6}a{color:#0d6562}header{background:#143f40;color:white}header a{color:white}.ct-wrap{max-width:1200px;margin:auto;padding:24px}.ct-nav{display:flex;flex-wrap:wrap;gap:8px 20px;align-items:center}.ct-nav a{padding:6px 0}.ct-panel{background:white;border:1px solid #dbe3e6;border-radius:14px;padding:24px;margin:20px 0;box-shadow:0 3px 10px #18343c05}.ct-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:18px}.ct-field{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}.ct-field input,.ct-field select,.ct-field textarea,.ct-input{width:100%;border:1px solid #9aaeb4;border-radius:7px;background:white;color:#182b3a;padding:9px 12px;box-sizing:border-box}.ct-button{display:inline-block;background:#146764;color:white;border:0;border-radius:7px;padding:10px 17px;text-decoration:none;cursor:pointer;font-weight:600}.ct-secondary{background:#e6efef;color:#184c4b}.ct-danger{background:#a53237;color:white}.ct-muted{color:#52666f;font-size:.93rem}.ct-alert{padding:14px 18px;border-radius:8px;background:#e8f5ef;margin:16px 0}.ct-error{background:#fff0ee;color:#84272a}.ct-table{overflow-x:auto}table{width:100%;border-collapse:collapse;text-align:left}th,td{padding:12px;border-bottom:1px solid #dee6e8;vertical-align:top}th{font-size:.88rem;background:#f1f5f6}h1{font-size:1.9rem;font-weight:750;line-height:1.25;margin:15px 0}h2{font-size:1.2rem;font-weight:700;margin:12px 0}h3{font-weight:700}p{margin:8px 0}.ct-number{font-size:2rem;font-weight:750}.ct-row{border:1px solid #d8e3e5;border-radius:9px;padding:18px;margin:14px 0}.ct-actions{display:flex;flex-wrap:wrap;gap:12px;align-items:center}.ct-badge{display:inline-block;border-radius:20px;background:#e4efed;padding:3px 10px;font-size:.82rem}.ct-code{white-space:pre-wrap;overflow-wrap:anywhere;font-size:.85rem}input:focus,select:focus,textarea:focus,button:focus,a:focus{outline:3px solid #c78b39;outline-offset:2px}[hidden]{display:none!important}@media(max-width:600px){.ct-wrap{padding:16px}.ct-panel{padding:16px}h1{font-size:1.5rem}}@media print{header,.ct-no-print,button,form{display:none!important}.ct-panel{border:0;box-shadow:none;padding:0}body{background:white}.ct-wrap{max-width:none;padding:0}table{font-size:10pt}tr{break-inside:avoid}}
    </style>
</head>
<body>
<header><div class="ct-wrap"><div class="ct-actions" style="justify-content:space-between">
    <a href="{{ route('home') }}" style="font-size:1.3rem;font-weight:750">CashTrack Secure <small>Plan B</small></a>
    @auth <span>{{ auth()->user()->name }} · {{ auth()->user()->role?->name }}</span> @endauth
</div>
@auth
<nav aria-label="Navigasi utama" class="ct-nav" style="margin-top:16px">
    <a href="{{ route('dashboard') }}">Ringkasan</a>
    @if(auth()->user()->role?->name === 'Owner')
        <a href="{{ route('owner.products.index') }}">Barang</a><a href="{{ route('owner.stock-in.create') }}">Terima stok</a>
        <a href="{{ route('owner.stock-counts.index') }}">Kiraan stok</a><a href="{{ route('owner.alerts.index') }}">Perbezaan</a>
        <a href="{{ route('owner.audit.index') }}">Audit</a><a href="{{ route('owner.reports.index') }}">Laporan</a><a href="{{ route('owner.users.index') }}">Akaun</a>
    @endif
    <a href="{{ route('cashier.sales.create') }}">Jualan</a><a href="{{ route('withdrawals.create') }}">Pengeluaran lain</a>
    <a href="{{ route('profile.edit') }}">Profil</a>
    <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="ct-button ct-secondary">Log keluar</button></form>
</nav>
@endauth</div></header>
<main class="ct-wrap">
    <p class="ct-muted ct-no-print">Demonstrasi standalone · Satu kedai · Produk siap jual</p>
    @if(session('success'))<div role="status" class="ct-alert">{{ session('success') }}</div>@endif
    @if(session('status'))<div role="status" class="ct-alert">{{ session('status') }}</div>@endif
    @if($errors->any())<div role="alert" class="ct-alert ct-error"><strong>Maklumat belum disimpan. Semak perkara berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
    <footer class="ct-muted" style="margin-top:36px;border-top:1px solid #dbe3e6;padding-top:15px">Baki sistem berdasarkan transaksi yang direkodkan. Kiraan fizikal memerlukan pemeriksaan manusia.</footer>
</main>
@stack('scripts')
</body></html>
