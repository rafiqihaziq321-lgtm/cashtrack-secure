@extends('layouts.planb')
@section('title', 'Activity History')
@section('content')
<h1>Activity History</h1>
<p>See what changed, who recorded it and when.</p>
@if($verification['status'] !== 'LULUS')
    <div class="ct-alert ct-error" role="alert">Some stock history could not be verified. Review Technical details before relying on these records.</div>
@endif
<section class="ct-panel ct-table"><h2>Recent Activity</h2>
<table><thead><tr><th>What Happened / Item</th><th>Who / Date</th><th>Previous Value</th><th>New Value</th><th>Reason</th></tr></thead><tbody>
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
        <td><strong>{{ $actionLabels[$activity->action] ?? 'Activity recorded' }}</strong><br>{{ $itemName ?: 'Account activity' }}</td>
        <td>{{ $activity->name ?? 'System' }}<br><small>{{ \Carbon\CarbonImmutable::parse($activity->created_at, 'UTC')->timezone('Asia/Kuala_Lumpur')->format('d M Y, g:i a') }}</small></td>
        <td>@include('planb.activity-values', ['values' => $before])</td>
        <td>@include('planb.activity-values', ['values' => $after])</td>
        <td>
            @if(isset($after['reason']))
                {{ ['DAMAGED' => 'Damaged', 'EXPIRED' => 'Expired', 'SAMPLE' => 'Sample', 'OWN_USE' => 'Own Use'][$after['reason']] ?? 'Stock removal' }}<br>
            @endif
            {{ $reason ?: 'No reason recorded' }}
        </td>
    </tr>
@empty
    <tr><td colspan="5">No activity recorded yet.</td></tr>
@endforelse
</tbody></table>{{ $activities->links() }}</section>
<section class="ct-panel"><h2>Stock Movement History</h2>
@include('planb.ledger')
{{ $movements->links() }}
</section>
<details class="ct-panel">
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
