
@extends('layouts.planb')
@section('title', 'Stock Issues')
@section('content')
<h1>Stock Issues</h1><p>Review differences between recorded stock and the physical count.</p>
<div class="ct-panel ct-table"><table><thead><tr><th>Item / Date</th><th>Recorded Stock at Check</th><th>Physical Count</th><th>Difference</th><th>Status / Action</th></tr></thead><tbody>
@forelse($alerts as $alert)<tr><td>{{ $alert->product->name }}<br><small>{{ $alert->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }} · {{ $alert->stockCount->user->name }}</small></td><td>{{ $alert->stockCount->expected_quantity }} @include('planb.unit', ['unit' => $alert->product->unit])</td><td>{{ $alert->stockCount->physical_quantity }}</td><td>{{ $alert->discrepancy_quantity }}</td><td>{{ ['OPEN'=>'Needs Review','REVIEWING'=>'Under Review','RESOLVED'=>'Resolved'][$alert->status] ?? $alert->status }}<br><a href="{{ route('owner.alerts.show', $alert) }}">Review Issue</a></td></tr>
@empty<tr><td colspan="5">No stock issues to review.</td></tr>
@endforelse</tbody></table>{{ $alerts->links() }}</div>
@endsection
