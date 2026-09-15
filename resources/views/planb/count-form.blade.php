
@extends('layouts.planb')
@section('title', 'Kiraan bebas')
@section('content')
<h1>Kiraan fizikal oleh Owner</h1><p>Pilih barang dahulu, hentikan pergerakan barang berkenaan seketika, kemudian periksa dan kira barang sebenar. Baki jangkaan disembunyikan pada borang ini untuk mengurangkan kecenderungan menyalin angka; ini tidak menjamin kejujuran.</p>
<form class="ct-panel" method="get"><label class="ct-field">Pilih barang untuk dikira<select name="product_id" required><option value="">Pilih barang</option>
@foreach($products as $product)<option value="{{ $product->id }}" @selected($selected?->id === $product->id)>{{ $product->sku }} — {{ $product->name }} ({{ $product->unit }})</option>
@endforeach</select></label><button class="ct-button ct-secondary">Buka borang kiraan baharu</button></form>
@if($selected)<form class="ct-panel" method="post" action="{{ route('owner.stock-counts.store') }}">
@csrf
<input type="hidden" name="submission_key" value="{{ $submissionKey }}"><input type="hidden" name="product_id" value="{{ $selected->id }}"><input type="hidden" name="count_token" value="{{ $token }}"><input type="hidden" name="recount_of" value="{{ $recountOf }}">
<h2>{{ $selected->name }} @if($recountOf)· Kiraan semula bagi #{{ $recountOf }}
@endif</h2><p class="ct-muted">Borang luput selepas 2 jam atau apabila pergerakan stok berubah. Jika ditolak, buka semula dan kira semula barang; angka lama tidak diisi automatik.</p>
<label class="ct-field">Bilangan sebenar ({{ $selected->unit }})<input type="number" name="physical_quantity" min="0" max="2000000000" required autocomplete="off"></label><label class="ct-field">Catatan pemeriksaan<textarea name="notes" maxlength="1000" placeholder="Contoh: dikira di rak depan dan simpanan belakang"></textarea></label><button class="ct-button">Simpan bukti kiraan</button></form>
@endif
@endsection
