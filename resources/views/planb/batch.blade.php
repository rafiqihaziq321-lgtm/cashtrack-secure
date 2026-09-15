
@extends('layouts.planb')
@section('title', $mode === 'sale' ? 'Jualan baharu' : 'Penerimaan stok')
@section('content')
<h1>{{ $mode === 'sale' ? 'Rekod jualan' : 'Terima stok & daftar barang' }}</h1>
<p>{{ $mode === 'sale' ? 'Tambah semua barang dalam satu jualan. Harga katalog digunakan oleh server; baki disemak sebelum jualan disimpan.' : 'Cari barang sedia ada atau daftar barang baharu pada skrin yang sama. Jika satu baris gagal, seluruh penerimaan dibatalkan.' }}</p>
<div class="ct-panel"><label class="ct-field">Input barcode / kod barang<input id="scanner" placeholder="Imbas atau taip, kemudian tekan Enter" autocomplete="off"></label><p id="scanner-status" role="status" class="ct-muted">Input scanner seperti papan kekunci. Demonstrasi menaip barcode tidak membuktikan scanner fizikal telah diuji.</p></div>
<datalist id="catalog-options">
@foreach($products as $product)<option value="{{ $product->sku }} — {{ $product->name }}" data-id="{{ $product->id }}" data-code="{{ $product->sku }}" data-barcode="{{ $product->barcode }}" data-name="{{ $product->name }}" data-price="{{ $product->selling_price }}" data-unit="{{ $product->unit }}">{{ $product->barcode }} · RM {{ $product->selling_price }} / {{ $product->unit }}</option>
@endforeach</datalist>
<form method="post" action="{{ $mode === 'sale' ? route('cashier.sales.store') : route('owner.stock-in.store') }}" id="batch-form" class="ct-panel">
@csrf
<input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
@if($mode === 'receipt')<label class="ct-field">Jenis penerimaan<select name="purpose"><option value="STOCK_IN" @selected(old('purpose') === 'STOCK_IN')>Penerimaan stok biasa</option><option value="OPENING" @selected(old('purpose') === 'OPENING')>Stok permulaan — barang belum mempunyai sejarah</option></select></label><label class="ct-field">Catatan / rujukan penerimaan<textarea name="notes" maxlength="1000">{{ old('notes') }}</textarea></label>
@endif
<div id="batch-rows">
@foreach(old('items', [['quantity'=>1]]) as $index => $item)
@include('planb.batch-row', compact('index','item'))
@endforeach</div>
<div class="ct-actions"><button class="ct-button ct-secondary" type="button" id="add-row">Tambah baris</button><button class="ct-button" type="submit">{{ $mode === 'sale' ? 'Simpan jualan & buka resit' : 'Simpan semua penerimaan' }}</button></div>
<p id="estimated-total" class="ct-muted"></p></form>
<template id="batch-template">
@include('planb.batch-row', ['index'=>'__INDEX__', 'item'=>[]])</template>
@endsection
@push('scripts')<script>
(() => {
    const rows=document.getElementById('batch-rows'), options=[...document.querySelectorAll('#catalog-options option')];
    let next=rows.children.length;
    const total=()=>{let cents=0;rows.querySelectorAll('[data-row]').forEach(row=>{const p=options.find(o=>o.dataset.id===row.querySelector('[data-product]').value);if(p){cents+=Math.round(Number(p.dataset.price)*100)*Number(row.querySelector('[data-quantity]').value||0)}});document.getElementById('estimated-total').textContent={{ Illuminate\Support\Js::from($mode === 'sale') }}?'Anggaran jumlah: RM '+(cents/100).toFixed(2)+'. Jumlah akhir disahkan oleh server.':'';};
    const select=(row,p)=>{row.querySelector('[data-product]').value=p?.dataset.id||'';if(p)row.querySelector('[data-search]').value=p.value;row.querySelector('[data-product-info]').textContent=p?'RM '+p.dataset.price+' / '+p.dataset.unit:'';total();};
    const bind=row=>{const mode=row.querySelector('[data-mode]');const toggle=()=>{const isNew=mode?.value==='new';row.querySelector('[data-existing]').hidden=isNew;row.querySelectorAll('[data-existing] input').forEach(i=>i.disabled=isNew);const box=row.querySelector('[data-new]');if(box){box.hidden=!isNew;box.querySelectorAll('input,select').forEach(i=>i.disabled=!isNew)}total();};mode?.addEventListener('change',toggle);toggle();row.querySelector('[data-search]').addEventListener('input',e=>{const value=e.target.value.trim();const p=options.find(o=>o.value===value||o.dataset.code===value||(o.dataset.barcode&&o.dataset.barcode===value));select(row,p)});row.querySelector('[data-quantity]').addEventListener('input',total);row.querySelector('[data-remove]').addEventListener('click',()=>{row.remove();total()});select(row,options.find(o=>o.dataset.id===row.querySelector('[data-product]').value));};
    const add=()=>{const html=document.getElementById('batch-template').innerHTML.replaceAll('__INDEX__',String(next++));const template=document.createElement('template');template.innerHTML=html;const row=template.content.firstElementChild;rows.appendChild(row);bind(row);return row;};
    rows.querySelectorAll('[data-row]').forEach(bind);document.getElementById('add-row').addEventListener('click',add);
    document.getElementById('scanner').addEventListener('keydown',e=>{if(e.key!=='Enter')return;e.preventDefault();const value=e.target.value.trim();const p=options.find(o=>o.dataset.code===value||(o.dataset.barcode&&o.dataset.barcode===value));if(!p){document.getElementById('scanner-status').textContent='Barang tidak ditemui. Cari nama atau daftar barang baharu.';return}let row=[...rows.children].find(r=>!r.querySelector('[data-product]').value&&r.querySelector('[data-mode]')?.value!=='new')||add();select(row,p);e.target.value='';document.getElementById('scanner-status').textContent='Ditambah: '+p.dataset.name;});
    document.getElementById('batch-form').addEventListener('submit',e=>{const missing=[...rows.children].some(r=>r.querySelector('[data-mode]')?.value!=='new'&&!r.querySelector('[data-product]').value);if(!rows.children.length||missing){e.preventDefault();document.getElementById('scanner-status').textContent='Pilih barang yang sah untuk setiap baris sebelum menyimpan.';document.getElementById('scanner').focus();}});
})();
</script>
@endpush
