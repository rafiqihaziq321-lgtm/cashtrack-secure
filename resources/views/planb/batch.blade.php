
@extends('layouts.planb')
@section('title', $mode === 'sale' ? 'New Sale' : 'Receive Stock')
@section('content')
<h1>{{ $mode === 'sale' ? 'New Sale' : 'Receive Stock' }}</h1>
<p>{{ $mode === 'sale' ? 'Add the items your customer is buying.' : 'Search or scan an item, then enter the received quantity. If it is new, choose New Item and add its details here.' }}</p>
<div class="ct-panel"><label class="ct-field">Scan or enter Item Code / barcode<input id="scanner" placeholder="Scan or type, then press Enter" autocomplete="off"></label><p id="scanner-status" role="status" class="ct-muted">You can also search by item name in each row below.</p></div>
<datalist id="catalog-options">
@foreach($products as $product)<option value="{{ $product->sku }} — {{ $product->name }}" data-id="{{ $product->id }}" data-code="{{ $product->sku }}" data-barcode="{{ $product->barcode }}" data-name="{{ $product->name }}" data-price="{{ $product->selling_price }}" data-unit="@include('planb.unit', ['unit' => $product->unit])">{{ $product->barcode }} · RM {{ $product->selling_price }} / @include('planb.unit', ['unit' => $product->unit])</option>
@endforeach</datalist>
<form method="post" action="{{ $mode === 'sale' ? route('cashier.sales.store') : route('owner.stock-in.store') }}" id="batch-form" class="ct-panel">
@csrf
<input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
@if($mode === 'receipt')<label class="ct-field">Receiving Type<select name="purpose"><option value="STOCK_IN" @selected(old('purpose') === 'STOCK_IN')>Delivery</option><option value="OPENING" @selected(old('purpose') === 'OPENING')>Opening Stock — first quantity for this item</option></select></label><label class="ct-field">Delivery Notes / Reference<textarea name="notes" maxlength="1000">{{ old('notes') }}</textarea></label>
@endif
<div id="batch-rows">
@foreach(old('items', [['quantity'=>1]]) as $index => $item)
@include('planb.batch-row', compact('index','item'))
@endforeach</div>
<div class="ct-actions"><button class="ct-button ct-secondary" type="button" id="add-row">Add Another Item</button><button class="ct-button" type="submit">{{ $mode === 'sale' ? 'Save Sale & View Receipt' : 'Save Received Stock' }}</button></div>
<p id="estimated-total" class="ct-muted"></p></form>
<template id="batch-template">
@include('planb.batch-row', ['index'=>'__INDEX__', 'item'=>[]])</template>
@endsection
@push('scripts')<script>
(() => {
    const rows=document.getElementById('batch-rows'), options=[...document.querySelectorAll('#catalog-options option')];
    let next=rows.children.length;
    const total=()=>{let cents=0;rows.querySelectorAll('[data-row]').forEach(row=>{const p=options.find(o=>o.dataset.id===row.querySelector('[data-product]').value);if(p){cents+=Math.round(Number(p.dataset.price)*100)*Number(row.querySelector('[data-quantity]').value||0)}});document.getElementById('estimated-total').textContent={{ Illuminate\Support\Js::from($mode === 'sale') }}?'Estimated Total: RM '+(cents/100).toFixed(2)+'.':'';};
    const select=(row,p)=>{row.querySelector('[data-product]').value=p?.dataset.id||'';if(p)row.querySelector('[data-search]').value=p.value;row.querySelector('[data-product-info]').textContent=p?'RM '+p.dataset.price+' / '+p.dataset.unit:'';total();};
    const bind=row=>{const mode=row.querySelector('[data-mode]');const toggle=()=>{const isNew=mode?.value==='new';row.querySelector('[data-existing]').hidden=isNew;row.querySelectorAll('[data-existing] input').forEach(i=>i.disabled=isNew);const box=row.querySelector('[data-new]');if(box){box.hidden=!isNew;box.querySelectorAll('input,select').forEach(i=>i.disabled=!isNew)}total();};mode?.addEventListener('change',toggle);toggle();row.querySelector('[data-search]').addEventListener('input',e=>{const value=e.target.value.trim();const p=options.find(o=>o.value===value||o.dataset.code===value||(o.dataset.barcode&&o.dataset.barcode===value));select(row,p)});row.querySelector('[data-quantity]').addEventListener('input',total);row.querySelector('[data-remove]').addEventListener('click',()=>{row.remove();total()});select(row,options.find(o=>o.dataset.id===row.querySelector('[data-product]').value));};
    const add=()=>{const html=document.getElementById('batch-template').innerHTML.replaceAll('__INDEX__',String(next++));const template=document.createElement('template');template.innerHTML=html;const row=template.content.firstElementChild;rows.appendChild(row);bind(row);return row;};
    rows.querySelectorAll('[data-row]').forEach(bind);document.getElementById('add-row').addEventListener('click',add);
    document.getElementById('scanner').addEventListener('keydown',e=>{if(e.key!=='Enter')return;e.preventDefault();const value=e.target.value.trim();const p=options.find(o=>o.dataset.code===value||(o.dataset.barcode&&o.dataset.barcode===value));if(!p){document.getElementById('scanner-status').textContent='Item not found. Search by name; when receiving, choose New Item to add it.';return}let row=[...rows.children].find(r=>!r.querySelector('[data-product]').value&&r.querySelector('[data-mode]')?.value!=='new')||add();select(row,p);e.target.value='';document.getElementById('scanner-status').textContent='Added: '+p.dataset.name;});
    document.getElementById('batch-form').addEventListener('submit',e=>{const missing=[...rows.children].some(r=>r.querySelector('[data-mode]')?.value!=='new'&&!r.querySelector('[data-product]').value);if(!rows.children.length||missing){e.preventDefault();document.getElementById('scanner-status').textContent='Please select an item in each row before saving.';document.getElementById('scanner').focus();}});
})();
</script>
@endpush
