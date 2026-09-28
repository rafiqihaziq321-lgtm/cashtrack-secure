
@extends('layouts.planb')
@section('title', 'Stock Received')
@section('content')
<h1>Stock Received</h1><p>{{ \Carbon\CarbonImmutable::parse($operation->created_at, 'UTC')->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }}</p>
<div class="ct-panel ct-table"><table><thead><tr><th>Item</th><th>Type</th><th>Received</th><th>Stock Before → After</th><th>Received By / Notes</th></tr></thead><tbody>
@foreach($movements as $movement)<tr><td>{{ $movement->product->name }}<br>{{ $movement->product->sku }}</td><td>{{ $movement->movement_type === 'OPENING' ? 'Opening Stock' : 'Supplier Delivery' }}</td><td>{{ $movement->quantity }} @include('planb.unit', ['unit' => $movement->product->unit])</td><td>{{ $movement->quantity_before }} → {{ $movement->quantity_after }}</td><td>{{ $movement->user->name }}<br>@include('planb.message', ['message' => $movement->notes ?? ''])</td></tr>
@endforeach</tbody></table></div><div class="ct-actions"><button class="ct-button" onclick="window.print()">Print</button><a href="{{ route('owner.stock-in.create') }}">Receive More Stock</a></div>
@endsection
