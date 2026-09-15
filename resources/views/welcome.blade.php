@extends('layouts.planb')
@section('title', 'Rekod jualan dan stok')
@section('content')
<div class="ct-panel" style="padding:clamp(24px,5vw,64px)">
    <span class="ct-badge">CashTrack Secure — projek demonstrasi</span>
    <h1 style="font-size:clamp(2rem,5vw,3.4rem);max-width:800px">Rekod jualan. Semak stok.<br>Fahami setiap perbezaan.</h1>
    <p style="max-width:720px;font-size:1.1rem">Sistem untuk membantu Owner dan Cashier merekod pergerakan barang, membuat kiraan fizikal dan menyemak sejarah tindakan dalam satu kedai.</p>
    <div class="ct-actions" style="margin-top:24px">@auth<a class="ct-button" href="{{ route('dashboard') }}">Buka ringkasan</a>@else<a class="ct-button" href="{{ route('login') }}">Log masuk</a><a href="{{ route('password.request') }}">Lupa kata laluan</a>@endauth</div>
</div>
<div class="ct-grid"><article class="ct-panel"><h2>Urus barang & stok</h2><p>Daftar barang baharu ketika menerima stok. Kod barang automatik; barcode pilihan.</p></article><article class="ct-panel"><h2>Jual & rekod pengeluaran</h2><p>Bezakan jualan daripada barang rosak, luput, sampel dan penggunaan sendiri.</p></article><article class="ct-panel"><h2>Semak dengan bukti</h2><p>Bandingkan rekod dengan kiraan manusia. Catat semakan dan pelarasan tanpa memadam sejarah asal.</p></article></div>
<p class="ct-muted">Hash bersifat tamper-evident: membantu mengesan perubahan pada medan yang dilindungi. Ia tidak membuktikan input asal benar atau mengenal pasti pihak yang bersalah. Requirement dan UAT client belum disahkan.</p>
@endsection
