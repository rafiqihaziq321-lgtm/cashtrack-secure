@extends('layouts.planb')
@section('title', 'Stock Check')
@section('content')
<div class="ct-page-heading">
    <div><h1>Stock Checks</h1><p class="ct-muted">Review past counts or start a new stock check.</p></div>
    <div class="ct-actions"><a class="ct-button ct-secondary" href="{{ route('owner.alerts.index') }}">Review Stock Issues</a><a class="ct-button" href="{{ route('owner.stock-counts.create') }}"><flux:icon.clipboard-document-check />Start Stock Check</a></div>
</div>
<section class="ct-panel ct-table-panel">
    <div class="ct-section-heading"><h2>Count history</h2></div>
    <div class="ct-table" role="region" aria-label="Stock checks" tabindex="0"><table>
        <thead><tr><th scope="col">Date / Time</th><th scope="col">Item / Counted By</th><th scope="col" class="ct-numeric">Recorded Stock</th><th scope="col" class="ct-numeric">Physical Count</th><th scope="col" class="ct-numeric">Difference</th><th scope="col">Action</th></tr></thead>
        <tbody>
        @forelse($counts as $count)
            <tr>
                <td>{{ $count->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }}@if($count->recount_of)<br><span class="ct-badge ct-badge-neutral">Recheck</span>@endif</td>
                <th scope="row"><span class="ct-item-name">{{ $count->product->name }}</span><span class="ct-item-meta">{{ $count->user->name }} · @include('planb.unit', ['unit' => $count->product->unit])</span>@if($count->notes)<span class="ct-item-meta">{{ $count->notes }}</span>@endif</th>
                <td class="ct-numeric">{{ $count->expected_quantity }}</td>
                <td class="ct-numeric"><strong>{{ $count->physical_quantity }}</strong></td>
                <td class="ct-numeric">{{ $count->discrepancy_quantity > 0 ? '+' : '' }}{{ $count->discrepancy_quantity }}<br><span class="ct-badge {{ $count->status === 'MATCH' ? 'ct-badge-success' : 'ct-badge-warning' }}">{{ ['MATCH'=>'Matches','SHORTAGE'=>'Shortage','SURPLUS'=>'Surplus'][$count->status] ?? $count->status }}</span></td>
                <td><a class="ct-button ct-secondary" href="{{ route('owner.stock-counts.create', ['product_id'=>$count->product_id,'recount_of'=>$count->id]) }}">Count Again</a></td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="ct-empty"><strong>No stock checks yet.</strong><p>Start a stock check to compare your records with a physical count.</p></div></td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($counts->hasPages())<div class="ct-pagination">{{ $counts->links() }}</div>@endif
</section>
@endsection
