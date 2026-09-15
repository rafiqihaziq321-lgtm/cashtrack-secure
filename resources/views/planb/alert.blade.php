
@extends('layouts.planb')
@section('title', 'Bukti perbezaan')
@section('content')
<h1>Perbezaan #{{ $alert->id }} · {{ $alert->product->name }}</h1><p>{{ ['OPEN'=>'Belum disemak','REVIEWING'=>'Sedang disemak','RESOLVED'=>'Selesai'][$alert->status] ?? $alert->status }} · Kiraan #{{ $alert->stock_count_id }} oleh {{ $alert->stockCount->user->name }} pada {{ $alert->stockCount->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }} Malaysia</p>
<div class="ct-grid"><div class="ct-panel"><h2>Baki rekod ketika kiraan</h2><p class="ct-number">{{ $alert->stockCount->expected_quantity }}</p></div><div class="ct-panel"><h2>Kiraan fizikal</h2><p class="ct-number">{{ $alert->stockCount->physical_quantity }}</p></div><div class="ct-panel"><h2>Perbezaan</h2><p class="ct-number">{{ $alert->discrepancy_quantity }} {{ $alert->product->unit }}</p></div></div>
<p>Baki rekod semasa: <strong>{{ $balance }} {{ $alert->product->unit }}</strong>. Catatan kiraan: {{ $alert->stockCount->notes ?: 'Tiada' }}</p><a class="ct-button ct-secondary" href="{{ route('owner.stock-counts.create', ['product_id'=>$alert->product_id,'recount_of'=>$alert->stock_count_id]) }}">Buat kiraan semula</a>
@if($alert->status !== 'RESOLVED')<form class="ct-panel" method="post" action="{{ route('owner.alerts.review', $alert) }}">
@csrf<input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}"><label class="ct-field">Tindakan<select name="action">
@foreach(['note'=>'Tambah catatan sahaja','review'=>'Tandakan sedang disemak','close'=>'Tutup dengan sebab — tanpa ubah stok','adjust'=>'Laras stok mengikut bukti kiraan ini'] as $key=>$label)<option value="{{ $key }}" @selected(old('action') === $key)>{{ $label }}</option>
@endforeach</select></label><label class="ct-field">Hasil semakan / sebab (wajib)<textarea name="note" required minlength="3" maxlength="2000">{{ old('note') }}</textarea></label><p class="ct-muted">Pelarasan ditolak jika terdapat pergerakan atau kiraan lebih baharu. Tidak semua perbezaan perlu pelarasan. Semak juga penerimaan, jualan dan pengeluaran yang belum direkod.</p><button class="ct-button">Simpan tindakan</button></form>
@else<div class="ct-alert">Selesai: {{ $alert->resolution }} @if($alert->adjustment_id)· Pelarasan ledger #{{ $alert->adjustment_id }}
@else· Tiada pelarasan stok
@endif</div>
@endif
<section class="ct-panel"><h2>Sejarah tindakan</h2>
@forelse($notes as $note)<p><strong>{{ $note->name }}</strong> · {{ \Carbon\CarbonImmutable::parse($note->created_at, 'UTC')->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }} · {{ $note->action }}<br>{{ $note->note }}</p>
@empty<p>Belum ada catatan semakan.</p>
@endforelse</section>
<section class="ct-panel"><h2>50 pergerakan terkini barang ini</h2>
@include('planb.ledger', ['movements'=>$movements])</section>
@endsection
