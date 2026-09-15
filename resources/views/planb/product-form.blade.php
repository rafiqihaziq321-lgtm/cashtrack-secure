
@extends('layouts.planb')
@section('title', 'Maklumat barang')
@section('content')
<h1>{{ $product ? 'Ubah maklumat barang' : 'Daftar katalog barang' }}</h1><p>Kod barang {{ $product?->sku ?? 'dijana secara automatik' }}. Menyimpan katalog tidak menambah stok. Unit dikunci selepas pergerakan pertama.</p>
<form class="ct-panel" method="post" action="{{ $product ? route('owner.products.update', $product) : route('owner.products.store') }}">
@csrf @if($product)
@method('PUT')
@endif<input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
@include('planb.catalog-fields', ['prefix'=>'', 'suffix'=>'', 'values'=>old() ?: ($product?->toArray() ?? [])])
@if($product)<label class="ct-field">Status jualan<select name="is_active"><option value="1" @selected(old('is_active', $product->is_active))>Dijual</option><option value="0" @selected(!old('is_active', $product->is_active))>Dihentikan</option></select></label>
@endif
<button class="ct-button">Simpan maklumat barang</button></form>
@endsection
