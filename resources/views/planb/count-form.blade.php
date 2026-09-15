
@extends('layouts.planb')
@section('title', 'Stock Check')
@section('content')
<h1>Stock Check</h1><p>Choose an item and count all stock on the shelves and in storage. Pause sales and other movements of this item while counting.</p>
<form class="ct-panel" method="get"><label class="ct-field">Item to Check<select name="product_id" required><option value="">Select an item</option>
@foreach($products as $product)<option value="{{ $product->id }}" @selected($selected?->id === $product->id)>{{ $product->sku }} — {{ $product->name }} (@include('planb.unit', ['unit' => $product->unit]))</option>
@endforeach</select></label><button class="ct-button ct-secondary">Start Count</button></form>
@if($selected)<form class="ct-panel" method="post" action="{{ route('owner.stock-counts.store') }}">
@csrf
<input type="hidden" name="submission_key" value="{{ $submissionKey }}"><input type="hidden" name="product_id" value="{{ $selected->id }}"><input type="hidden" name="count_token" value="{{ $token }}"><input type="hidden" name="recount_of" value="{{ $recountOf }}">
<h2>{{ $selected->name }} @if($recountOf)· Recheck
@endif</h2><p class="ct-muted">Complete this count within 2 hours. If stock changes before you save, start a new count.</p>
<label class="ct-field">Physical Count (@include('planb.unit', ['unit' => $selected->unit]))<input type="number" name="physical_quantity" min="0" max="2000000000" required autocomplete="off"></label><label class="ct-field">Count Notes<textarea name="notes" maxlength="1000" placeholder="For example: counted the front shelf and back storage"></textarea></label><button class="ct-button">Save Stock Check</button></form>
@endif
@endsection
