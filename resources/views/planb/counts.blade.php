
@extends('layouts.planb')
@section('title', 'Stock Check')
@section('content')
<h1>Stock Checks</h1><div class="ct-actions"><a class="ct-button" href="{{ route('owner.stock-counts.create') }}">Start Stock Check</a><a href="{{ route('owner.alerts.index') }}">Review Stock Issues</a></div><p>Review past counts or start a new stock check.</p>
<div class="ct-panel ct-table"><table><thead><tr><th>Date / Time</th><th>Item / Counted By</th><th>Recorded Stock</th><th>Physical Count</th><th>Difference</th><th></th></tr></thead><tbody>
@forelse($counts as $count)<tr><td>{{ $count->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }}
@if($count->recount_of)<br>Recheck
@endif</td><td>{{ $count->product->name }} (@include('planb.unit', ['unit' => $count->product->unit]))<br><small>{{ $count->user->name }}</small></td><td>{{ $count->expected_quantity }}</td><td>{{ $count->physical_quantity }}</td><td>{{ $count->discrepancy_quantity > 0 ? '+' : '' }}{{ $count->discrepancy_quantity }}<br>{{ ['MATCH'=>'Matches','SHORTAGE'=>'Shortage','SURPLUS'=>'Surplus'][$count->status] ?? $count->status }}</td><td><a href="{{ route('owner.stock-counts.create', ['product_id'=>$count->product_id,'recount_of'=>$count->id]) }}">Count Again</a><br><small>{{ $count->notes }}</small></td></tr>
@empty<tr><td colspan="6">No stock checks yet.</td></tr>
@endforelse</tbody></table>{{ $counts->links() }}</div>
@endsection
