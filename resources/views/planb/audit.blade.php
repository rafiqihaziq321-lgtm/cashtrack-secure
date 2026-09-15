
@extends('layouts.planb')
@section('title', 'Audit aktiviti')
@section('content')
<h1>Audit: siapa melakukan apa</h1><p>Audit aktiviti menerangkan tindakan pengguna. Ledger di bawah menerangkan pergerakan kuantiti barang.</p>
<section class="ct-panel ct-table"><h2>Aktiviti pengguna</h2><table><thead><tr><th>Masa Malaysia / pengguna</th><th>Tindakan / rujukan</th><th>Sebelum</th><th>Selepas</th></tr></thead><tbody>
@forelse($activities as $activity)<tr><td>{{ \Carbon\CarbonImmutable::parse($activity->created_at,'UTC')->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i:s') }}<br>{{ $activity->name ?? 'Sistem' }}</td><td>{{ str_replace(['.','_'], [' · ',' '], $activity->action) }}<br>{{ $activity->reference_type }} #{{ $activity->reference_id }}</td><td><pre class="ct-code">{{ json_encode(json_decode($activity->before_values), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></td><td><pre class="ct-code">{{ json_encode(json_decode($activity->after_values), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></td></tr>
@empty<tr><td colspan="4">Belum ada aktiviti Plan B. Aktiviti sebelum pemasangan tidak direka semula.</td></tr>
@endforelse</tbody></table>{{ $activities->links() }}</section>
<section class="ct-panel"><h2>Ledger pergerakan stok</h2>
@include('planb.ledger'){{ $movements->links() }}</section>
<details class="ct-panel"><summary>Semakan integriti teknikal — {{ $verification['status'] }}</summary><p>{{ $verification['checked'] }} rekod diperiksa mengikut ID; {{ $verification['legacy'] }} versi lama; {{ $verification['unsigned'] }} rekod lama tanpa hash. @if($verification['error'])Kegagalan pertama: #{{ $verification['error'] }}.
@endif Head tersimpan {{ $verification['head_matches'] ? 'sepadan' : 'tidak sepadan' }}.</p><p>Versi 1 melindungi medan pergerakan dan previous_hash; masa dan ID tidak dilindungi. Versi 2 turut melindungi ID dan created_at. Audit aktiviti, katalog, jualan dan kiraan fizikal tidak termasuk dalam chain ini.</p><p>Ini ialah tamper-evident, bukan immutable. Akses penuh database/aplikasi boleh membina semula chain serta head. Pemadaman hujung boleh dikesan jika head asal masih ada; pemadaman bersama pengubahan head memerlukan checkpoint bebas untuk dikesan. Hash tidak mengesahkan kebenaran input asal.</p></details>
@endsection
