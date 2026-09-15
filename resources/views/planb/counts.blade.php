
@extends('layouts.planb')
@section('title', 'Kiraan stok')
@section('content')
<h1>Sejarah kiraan fizikal</h1><div class="ct-actions"><a class="ct-button" href="{{ route('owner.stock-counts.create') }}">Mula kiraan baharu</a><a href="{{ route('owner.alerts.index') }}">Semak perbezaan</a></div><p>Kiraan ialah pemeriksaan manusia pada masa tertentu. Kiraan semula menghasilkan rekod baharu.</p>
<div class="ct-panel ct-table"><table><thead><tr><th>Rekod / masa Malaysia</th><th>Barang / pemeriksa</th><th>Baki rekod</th><th>Kiraan fizikal</th><th>Perbezaan</th><th></th></tr></thead><tbody>
@forelse($counts as $count)<tr><td>#{{ $count->id }}<br>{{ $count->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }}
@if($count->recount_of)<br>Semula bagi #{{ $count->recount_of }}
@endif</td><td>{{ $count->product->name }} ({{ $count->product->unit }})<br><small>{{ $count->user->name }}</small></td><td>{{ $count->expected_quantity }}</td><td>{{ $count->physical_quantity }}</td><td>{{ $count->discrepancy_quantity > 0 ? '+' : '' }}{{ $count->discrepancy_quantity }}<br>{{ ['MATCH'=>'Sepadan','SHORTAGE'=>'Kurang','SURPLUS'=>'Lebih'][$count->status] ?? $count->status }}</td><td><a href="{{ route('owner.stock-counts.create', ['product_id'=>$count->product_id,'recount_of'=>$count->id]) }}">Kira semula</a><br><small>{{ $count->notes }}</small></td></tr>
@empty<tr><td colspan="6">Belum ada kiraan fizikal.</td></tr>
@endforelse</tbody></table>{{ $counts->links() }}</div>
@endsection
