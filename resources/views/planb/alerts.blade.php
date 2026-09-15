
@extends('layouts.planb')
@section('title', 'Semakan perbezaan')
@section('content')
<h1>Semakan perbezaan stok</h1><p>Perbezaan memerlukan siasatan proses. Ia bukan bukti seseorang jujur atau menipu.</p>
<div class="ct-panel ct-table"><table><thead><tr><th>Barang / masa Malaysia</th><th>Baki rekod ketika kiraan</th><th>Kiraan fizikal</th><th>Perbezaan</th><th>Status / tindakan</th></tr></thead><tbody>
@forelse($alerts as $alert)<tr><td>{{ $alert->product->name }}<br><small>{{ $alert->created_at->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }} · {{ $alert->stockCount->user->name }}</small></td><td>{{ $alert->stockCount->expected_quantity }} {{ $alert->product->unit }}</td><td>{{ $alert->stockCount->physical_quantity }}</td><td>{{ $alert->discrepancy_quantity }}</td><td>{{ ['OPEN'=>'Belum disemak','REVIEWING'=>'Sedang disemak','RESOLVED'=>'Selesai'][$alert->status] ?? $alert->status }}<br><a href="{{ route('owner.alerts.show', $alert) }}">Buka bukti & tindakan</a></td></tr>
@empty<tr><td colspan="5">Tiada perbezaan direkodkan. Ini tidak membuktikan semua input benar.</td></tr>
@endforelse</tbody></table>{{ $alerts->links() }}</div>
@endsection
