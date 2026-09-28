@extends('layouts.planb')
@section('title', 'Stock Issue')
@section('content')
<div class="ct-page-heading"><div><h1>Stock Issue · {{ $alert->product->name }}</h1>
<p><span class="ct-badge {{ $alert->status === 'RESOLVED' ? 'ct-badge-success' : 'ct-badge-warning' }}">{{ ['OPEN' => 'Needs Review', 'REVIEWING' => 'Under Review', 'RESOLVED' => 'Resolved'][$alert->status] ?? 'Needs Review' }}</span> · Counted by {{ $alert->stockCount->user->name }} on {{ $alert->stockCount->created_at->timezone('Asia/Kuala_Lumpur')->format('d M Y, g:i a') }}</p></div></div>
<div class="ct-grid ct-metrics">
    <div class="ct-panel"><h2>Recorded Stock at Check</h2><p class="ct-number">{{ $alert->stockCount->expected_quantity }}</p></div>
    <div class="ct-panel"><h2>Physical Count</h2><p class="ct-number">{{ $alert->stockCount->physical_quantity }}</p></div>
    <div class="ct-panel"><h2>Difference</h2><p class="ct-number">{{ $alert->discrepancy_quantity }} @include('planb.unit', ['unit' => $alert->product->unit])</p></div>
</div>
<p>Current recorded stock: <strong>{{ $balance }} @include('planb.unit', ['unit' => $alert->product->unit])</strong>. Count notes: {{ $alert->stockCount->notes ?: 'No notes' }}</p>
<a class="ct-button ct-secondary" href="{{ route('owner.stock-counts.create', ['product_id' => $alert->product_id, 'recount_of' => $alert->stock_count_id]) }}"><flux:icon.clipboard-document-check />Recheck Stock</a>
@if($alert->status !== 'RESOLVED')
    <form class="ct-panel ct-form-panel" method="post" action="{{ route('owner.alerts.review', $alert) }}">
        @csrf
        <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
        <section class="ct-form-section"><h2>Review this issue</h2><label class="ct-field">Action<select name="action">
            @foreach(['note' => 'Add a note', 'review' => 'Mark as under review', 'close' => 'Close issue without changing stock', 'adjust' => 'Adjust stock to this physical count'] as $key => $label)
                <option value="{{ $key }}" @selected(old('action') === $key)>{{ $label }}</option>
            @endforeach
        </select></label>
        <label class="ct-field">Reason / Review Notes (required)<textarea name="note" required minlength="3" maxlength="2000">{{ old('note') }}</textarea></label>
        <p class="ct-muted">Check for missing deliveries, sales or removals first. If stock has moved or been counted again, take a new count before adjusting.</p>
        </section><div class="ct-form-actions"><button class="ct-button">Save Review</button></div>
    </form>
@else
    <div class="ct-alert">Resolved: {{ $alert->resolution }} · {{ $alert->adjustment_id ? 'Stock adjusted' : 'Stock unchanged' }}</div>
    @if(!$alert->adjustment_id)<p class="ct-resolution-help">Issue closed. Recorded stock was not changed. Recheck the item to confirm the current physical quantity.</p>@endif
@endif
<section class="ct-panel"><h2>Review History</h2>
    @forelse($notes as $note)
        <p><strong>{{ $note->name }}</strong> · {{ \Carbon\CarbonImmutable::parse($note->created_at, 'UTC')->timezone('Asia/Kuala_Lumpur')->format('d M Y, g:i a') }} · {{ ['note' => 'Note added', 'review' => 'Under review', 'close' => 'Issue closed', 'adjust' => 'Stock adjusted'][$note->action] ?? 'Review recorded' }}<br>{{ $note->note }}</p>
    @empty
        <p>No review notes yet.</p>
    @endforelse
</section>
<section class="ct-panel"><h2>Recent Stock Movements</h2>@include('planb.ledger')</section>
@endsection
