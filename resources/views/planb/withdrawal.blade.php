
@extends('layouts.planb')
@section('title', 'Remove Stock')
@section('content')
<div class="ct-page-heading"><div><h1>Remove Stock</h1><p class="ct-muted">Record damaged, expired, sample or own-use stock. These removals reduce stock without recording a sale.</p></div></div>
<form class="ct-panel ct-form-panel" method="post" action="{{ route('withdrawals.store') }}">
@csrf<input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
<section class="ct-form-section">@if(auth()->user()->role?->name === 'Owner')<h2>Stock removal details</h2>@endif
<label class="ct-field">Item<select name="product_id" required><option value="">Select an item</option>
@foreach($products as $product)<option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->sku }} — {{ $product->name }} (@include('planb.unit', ['unit' => $product->unit]))</option>
@endforeach</select></label>
<div class="ct-grid"><label class="ct-field">Quantity<input type="number" name="quantity" min="1" max="1000000" required value="{{ old('quantity', 1) }}"></label><label class="ct-field">Reason<select name="reason">
@foreach(['DAMAGED'=>'Damaged','EXPIRED'=>'Expired','SAMPLE'=>'Sample','OWN_USE'=>'Own Use'] as $key=>$label)<option value="{{ $key }}" @selected(old('reason') === $key)>{{ $label }}</option>
@endforeach</select></label></div>
<label class="ct-field">Removal Notes<textarea name="notes" minlength="3" maxlength="1000" required>{{ old('notes') }}</textarea></label></section>
<div class="ct-form-actions"><button class="ct-button {{ auth()->user()->role?->name === 'Owner' ? 'ct-danger' : '' }}">Save Stock Removal</button></div></form>
@endsection
