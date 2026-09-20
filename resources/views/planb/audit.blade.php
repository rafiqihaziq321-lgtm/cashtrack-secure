@extends('layouts.planb')
@section('title', 'Activity History')
@section('content')
<div class="ct-page-heading"><div><h1>Activity History</h1>
<p class="ct-muted">See what changed, who recorded it and when.</p></div></div>
@if($verification['status'] !== 'LULUS')
    <div class="ct-alert ct-error" role="alert">Some stock history could not be verified. Review Technical details before relying on these records.</div>
@endif
<section class="ct-panel ct-table-panel ct-table ct-activity-table" aria-label="Recent activity" tabindex="0"><h2 class="ct-section-heading">Recent Activity</h2>
<table><thead><tr><th scope="col">What Happened / Item</th><th scope="col">Who / Date</th><th scope="col">Previous Value</th><th scope="col"><span aria-hidden="true">→ </span>New Value</th><th scope="col">Reason</th></tr></thead><tbody>
@forelse($activities as $activity)
    @php
        $before = json_decode($activity->before_values ?? '[]', true) ?? [];
        $after = json_decode($activity->after_values ?? '[]', true) ?? [];
        $actionLabels = [
            'owner.dipasang' => 'Owner account created', 'akaun.log_masuk' => 'Signed in', 'akaun.log_keluar' => 'Signed out',
            'akaun.dicipta' => 'Staff member added', 'akaun.dikemas_kini' => 'Staff details updated', 'profil.dikemas_kini' => 'Profile updated',
            'barang.didaftar' => 'Item registered', 'barang.dikemas_kini' => 'Item details updated', 'stok.diterima' => 'Stock received',
            'jualan.direkod' => 'Sale recorded', 'stok.dikeluarkan' => 'Stock removed', 'stok.dikira' => 'Stock checked',
            'perbezaan.note' => 'Stock issue note added', 'perbezaan.review' => 'Stock issue under review',
            'perbezaan.close' => 'Stock issue closed', 'perbezaan.adjust' => 'Stock adjusted after review',
        ];
        $itemName = $activityItems[$activity->reference_type][$activity->reference_id] ?? ($after['name'] ?? $before['name'] ?? null);
        $reason = $after['note'] ?? $after['notes'] ?? null;
    @endphp
    <tr>
        <td><div class="ct-event"><span class="ct-event-icon"><flux:icon.clock /></span><div><strong class="ct-item-name">{{ $actionLabels[$activity->action] ?? 'Activity recorded' }}</strong><span class="ct-item-meta">@include('planb.message', ['message' => $itemName ?: 'Account activity'])</span></div></div></td>
        <td><span class="ct-activity-person">{{ $activity->name ?? 'System' }}</span><span class="ct-item-meta">{{ \Carbon\CarbonImmutable::parse($activity->created_at, 'UTC')->timezone('Asia/Kuala_Lumpur')->format('d M Y, g:i a') }}</span></td>
        <td class="ct-previous-value">@include('planb.activity-values', ['values' => $before])</td>
        <td class="ct-new-value">@include('planb.activity-values', ['values' => $after])</td>
        <td class="ct-activity-reason">
            @if(isset($after['reason']))
                {{ ['DAMAGED' => 'Damaged', 'EXPIRED' => 'Expired', 'SAMPLE' => 'Sample', 'OWN_USE' => 'Own Use'][$after['reason']] ?? 'Stock removal' }}<br>
            @endif
            @include('planb.message', ['message' => $reason ?: 'No reason recorded'])
        </td>
    </tr>
@empty
    <tr><td colspan="5"><div class="ct-empty"><span class="ct-empty-icon"><flux:icon.clock /></span><strong>No activity recorded yet.</strong><p>Item, stock and account changes will appear here.</p></div></td></tr>
@endforelse
</tbody></table><div class="ct-pagination">{{ $activities->links() }}</div></section>
<section class="ct-panel ct-table-panel ct-movement-history"><div class="ct-section-heading"><h2>Stock Movement History</h2><p class="ct-muted">Recorded stock before and after each movement.</p></div>
@include('planb.ledger')
<div class="ct-pagination">{{ $movements->links() }}</div>
</section>
<details class="ct-panel ct-technical-details">
    <summary>Technical details</summary>
    <h2>Stock history verification</h2>
    <p>{{ ['LULUS' => 'Passed', 'GAGAL' => 'Failed', 'TIDAK LENGKAP' => 'Incomplete'][$verification['status']] ?? 'Unknown' }} · {{ $verification['checked'] }} records checked · {{ $verification['legacy'] }} older records · {{ $verification['unsigned'] }} unsigned records.</p>
    <p>Saved chain head: {{ $verification['head_matches'] ? 'matches' : 'does not match' }}. @if($verification['error'])First affected record: {{ $verification['error'] }}.@endif</p>
    <p>Hash checks can reveal changes to protected stock records. They do not verify the original input, and someone with full database access could rebuild the chain. Older records do not protect their ID or timestamp.</p>
    @foreach($activities as $activity)
        <details><summary>Record {{ $activity->id }} · {{ $activity->action }}</summary>
            <p>{{ $activity->reference_type }} #{{ $activity->reference_id }}</p>
            <pre class="ct-code">{{ json_encode(['previous' => json_decode($activity->before_values), 'new' => json_decode($activity->after_values)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </details>
    @endforeach
</details>
@endsection
