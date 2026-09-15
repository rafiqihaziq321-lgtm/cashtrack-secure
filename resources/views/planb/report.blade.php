
@extends('layouts.planb')
@section('title', 'Laporan harian')
@section('content')
<h1>Laporan jualan harian</h1><p>{{ $day }} · Asia/Kuala_Lumpur · 00:00 termasuk hingga 00:00 hari berikutnya tidak termasuk. Jumlah berdasarkan jualan completed; pengeluaran rosak/luput tidak termasuk.</p>
<form class="ct-panel" method="get"><label class="ct-field">Tarikh Malaysia<input type="date" name="day" value="{{ $day }}" required></label><div class="ct-actions"><button class="ct-button">Papar laporan</button><a class="ct-button ct-secondary" href="{{ route('owner.reports.index', ['day'=>$day,'format'=>'csv']) }}">Eksport CSV</a><button class="ct-button ct-secondary" type="button" onclick="window.print()">Cetak</button></div></form>
<div class="ct-panel"><h2>Jumlah jualan</h2><p class="ct-number">RM {{ $total }}</p><p>{{ $sales->count() }} transaksi</p><div class="ct-table"><table><thead><tr><th>No transaksi</th><th>Masa Malaysia</th><th>Cashier</th><th>Jumlah RM</th></tr></thead><tbody>
@forelse($sales as $sale)<tr><td><a href="{{ route('sales.show',$sale) }}">{{ $sale->transaction_no }}</a></td><td>{{ $sale->created_at->timezone('Asia/Kuala_Lumpur')->format('H:i:s') }}</td><td>{{ $sale->cashier->name }}</td><td>{{ $sale->total_amount }}</td></tr>
@empty<tr><td colspan="4">Tiada jualan untuk tarikh ini.</td></tr>
@endforelse</tbody></table></div></div>
@endsection
