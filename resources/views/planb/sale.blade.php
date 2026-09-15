
@extends('layouts.planb')
@section('title', 'Resit jualan')
@section('content')
<h1>Ringkasan jualan</h1><p>{{ $sale->transaction_no }} · {{ $sale->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }} Malaysia</p><p>Direkod oleh {{ $sale->cashier->name }} · Status {{ $sale->status }}</p>
<div class="ct-panel ct-table"><table><thead><tr><th>Barang</th><th>Kuantiti</th><th>Harga seunit</th><th>Jumlah</th></tr></thead><tbody>
@foreach($sale->items as $item)<tr><td>{{ $item->product->name }}<br><small>{{ $item->product->sku }}</small></td><td>{{ $item->quantity }} {{ $item->product->unit }}</td><td>RM {{ $item->unit_price }}</td><td>RM {{ $item->subtotal }}</td></tr>
@endforeach</tbody><tfoot><tr><th colspan="3">Jumlah jualan</th><th>RM {{ $sale->total_amount }}</th></tr></tfoot></table></div>
<p class="ct-muted">Harga transaksi disimpan semasa jualan. Nama barang ialah maklumat katalog semasa. Resit demonstrasi; pembayaran tidak diproses oleh sistem ini.</p><div class="ct-actions"><button class="ct-button" onclick="window.print()">Cetak resit</button><a href="{{ route('cashier.sales.create') }}">Jualan baharu</a></div>
@endsection
