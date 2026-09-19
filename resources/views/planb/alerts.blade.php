@extends('layouts.planb')
@section('title', 'Stock Issues')
@section('content')
<div class="ct-page-heading"><div><h1>Stock Issues</h1><p class="ct-muted">Review differences between recorded stock and the physical count.</p></div></div>
<section class="ct-panel ct-table-panel">
    <div class="ct-section-heading"><h2>Stock reviews</h2></div>
    <div class="ct-table" role="region" aria-label="Stock issues" tabindex="0"><table>
        <thead><tr><th scope="col">Item / Date</th><th scope="col" class="ct-numeric">Recorded Stock at Check</th><th scope="col" class="ct-numeric">Physical Count</th><th scope="col" class="ct-numeric">Difference</th><th scope="col">Status / Action</th></tr></thead>
        <tbody>
        @forelse($alerts as $alert)
            <tr>
                <th scope="row"><span class="ct-item-name">{{ $alert->product->name }}</span><span class="ct-item-meta">{{ $alert->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }} · {{ $alert->stockCount->user->name }}</span></th>
                <td class="ct-numeric">{{ $alert->stockCount->expected_quantity }}<span class="ct-item-meta">@include('planb.unit', ['unit' => $alert->product->unit])</span></td>
                <td class="ct-numeric"><strong>{{ $alert->stockCount->physical_quantity }}</strong></td>
                <td class="ct-numeric">{{ $alert->discrepancy_quantity }}</td>
                <td><span class="ct-badge {{ $alert->status === 'RESOLVED' ? 'ct-badge-success' : 'ct-badge-warning' }}">{{ ['OPEN'=>'Needs Review','REVIEWING'=>'Under Review','RESOLVED'=>'Resolved'][$alert->status] ?? $alert->status }}</span><div class="ct-table-actions" style="margin-top:8px"><a href="{{ route('owner.alerts.show', $alert) }}">Review Issue</a></div></td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="ct-empty"><strong>No stock issues to review.</strong><p>Differences found during a stock check will appear here.</p></div></td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($alerts->hasPages())<div class="ct-pagination">{{ $alerts->links() }}</div>@endif
</section>
@endsection
