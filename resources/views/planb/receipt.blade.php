
@extends('layouts.planb')
@section('title', 'Ringkasan penerimaan')
@section('content')
<h1>Penerimaan #{{ $operation->id }}</h1><p>{{ \Carbon\CarbonImmutable::parse($operation->created_at, 'UTC')->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }} · Malaysia</p>
<div class="ct-panel ct-table"><table><thead><tr><th>Barang</th><th>Jenis</th><th>Diterima</th><th>Baki sebelum → selepas</th><th>Pengguna / catatan</th></tr></thead><tbody>
@foreach($movements as $movement)<tr><td>{{ $movement->product->name }}<br>{{ $movement->product->sku }}</td><td>{{ $movement->movement_type === 'OPENING' ? 'Stok permulaan' : 'Penerimaan biasa' }}</td><td>{{ $movement->quantity }} {{ $movement->product->unit }}</td><td>{{ $movement->quantity_before }} → {{ $movement->quantity_after }}</td><td>{{ $movement->user->name }}<br>{{ $movement->notes }}</td></tr>
@endforeach</tbody></table></div><div class="ct-actions"><button class="ct-button" onclick="window.print()">Cetak</button><a href="{{ route('owner.stock-in.create') }}">Penerimaan baharu</a></div>
@endsection
