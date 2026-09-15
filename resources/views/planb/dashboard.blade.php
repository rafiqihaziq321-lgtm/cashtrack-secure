
@extends('layouts.planb')
@section('title', 'Ringkasan')
@section('content')
<h1>Ringkasan kedai</h1><p>{{ $day }} · Asia/Kuala_Lumpur · Jualan berstatus completed @if(auth()->user()->role?->name === 'Cashier')· Transaksi anda sahaja
@endif</p>
<div class="ct-grid"><div class="ct-panel"><h2>Jualan hari ini</h2><p class="ct-number">RM {{ number_format((float)$total, 2) }}</p><p>{{ $transactions }} transaksi</p></div><div class="ct-panel"><h2>Barang perlu ditambah</h2><p class="ct-number">{{ $low->count() }}</p><p>Bilangan jenis barang, bukan jumlah unit stok</p></div>
@if(auth()->user()->role?->name === 'Owner')<div class="ct-panel"><h2>Perbezaan belum selesai</h2><p class="ct-number">{{ $alerts }}</p><a href="{{ route('owner.alerts.index') }}">Buka semakan perbezaan →</a></div>
@endif</div>
<div class="ct-actions"><a class="ct-button" href="{{ route('cashier.sales.create') }}">Rekod jualan</a>
@if(auth()->user()->role?->name === 'Owner')<a class="ct-button ct-secondary" href="{{ route('owner.stock-in.create') }}">Terima stok</a><a class="ct-button ct-secondary" href="{{ route('owner.stock-counts.create') }}">Mula kiraan fizikal</a>
@endif</div>
<section class="ct-panel"><h2>Peringatan tambah stok</h2><p class="ct-muted">Dipaparkan apabila baki rekod sama atau kurang daripada paras peringatan. Semak barang fizikal sebelum membuat pesanan.</p><div class="ct-table"><table><thead><tr><th>Barang</th><th>Baki rekod</th><th>Paras peringatan</th><th>Tindakan</th></tr></thead><tbody>
@forelse($low as $product)<tr><td>{{ $product->name }}<br><small>{{ $product->sku }}</small></td><td>{{ (int)$product->balance }} {{ $product->unit }}</td><td>{{ $product->reorder_level }} {{ $product->unit }}</td><td>
@if(auth()->user()->role?->name === 'Owner')<a href="{{ route('owner.stock-in.create') }}">Terima stok</a> · <a href="{{ route('owner.stock-counts.create', ['product_id'=>$product->id]) }}">Kira semula</a>
@else Maklumkan kepada Owner
@endif</td></tr>
@empty<tr><td colspan="4">Tiada barang memerlukan penambahan mengikut rekod semasa.</td></tr>
@endforelse</tbody></table></div></section>
@endsection
