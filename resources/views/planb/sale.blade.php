
@extends('layouts.planb')
@section('title', 'Sales Receipt')
@section('content')
@if(session('success') === 'Jualan disimpan. Harga dan jumlah disahkan pada server.')
    <p class="ct-sale-confirmation"><flux:icon.check /><strong>Sale completed successfully.</strong></p>
@endif
<h1>Sales Receipt</h1><p>{{ $sale->transaction_no }} · {{ $sale->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }} </p><p>Recorded by {{ $sale->cashier->name }} · {{ $sale->status === 'completed' ? 'Completed' : ucfirst($sale->status) }}</p>
<div class="ct-panel ct-table"><table><thead><tr><th>Item</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr></thead><tbody>
@foreach($sale->items as $item)<tr><td>{{ $item->product->name }}<br><small>{{ $item->product->sku }}</small></td><td>{{ $item->quantity }} @include('planb.unit', ['unit' => $item->product->unit])</td><td>RM {{ $item->unit_price }}</td><td>RM {{ $item->subtotal }}</td></tr>
@endforeach</tbody><tfoot><tr><th colspan="3">Sales Total</th><th>RM {{ $sale->total_amount }}</th></tr></tfoot></table></div>
<p class="ct-muted">Thank you for your purchase.</p><div class="ct-actions"><button class="ct-button" onclick="window.print()">Print Receipt</button><a href="{{ route('cashier.sales.create') }}">New Sale</a></div>
@endsection
