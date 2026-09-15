
@extends('layouts.planb')
@section('title', 'Pengeluaran bukan jualan')
@section('content')
<h1>Pengeluaran bukan jualan</h1><p>Gunakan untuk rosak, luput, sampel atau penggunaan sendiri. Kuantiti mengurangkan stok dan tidak menambah hasil jualan.</p>
<form class="ct-panel" method="post" action="{{ route('withdrawals.store') }}">
@csrf<input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
<label class="ct-field">Barang<select name="product_id" required><option value="">Pilih barang</option>
@foreach($products as $product)<option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->sku }} — {{ $product->name }} ({{ $product->unit }})</option>
@endforeach</select></label>
<div class="ct-grid"><label class="ct-field">Kuantiti<input type="number" name="quantity" min="1" max="1000000" required value="{{ old('quantity', 1) }}"></label><label class="ct-field">Sebab<select name="reason">
@foreach(['DAMAGED'=>'Rosak','EXPIRED'=>'Luput','SAMPLE'=>'Sampel','OWN_USE'=>'Penggunaan sendiri'] as $key=>$label)<option value="{{ $key }}" @selected(old('reason') === $key)>{{ $label }}</option>
@endforeach</select></label></div>
<label class="ct-field">Catatan pemeriksaan<textarea name="notes" minlength="3" maxlength="1000" required>{{ old('notes') }}</textarea></label><button class="ct-button">Simpan pengeluaran</button></form>
@endsection
