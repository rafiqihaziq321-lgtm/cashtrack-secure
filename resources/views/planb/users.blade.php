
@extends('layouts.planb')
@section('title', 'Akaun pengguna')
@section('content')
<h1>Akaun pengguna</h1><a class="ct-button" href="{{ route('owner.users.create') }}">Cipta akaun Cashier</a><p>Owner pertama melalui arahan pemasangan terkawal. Akaun Owner dilindungi daripada nyahaktif, pertukaran peranan dan pemadaman.</p><div class="ct-panel ct-table"><table><thead><tr><th>Nama / email login</th><th>Peranan</th><th>Akses</th><th>Email</th><th></th></tr></thead><tbody>
@foreach($users as $account)<tr><td>{{ $account->name }}<br>{{ $account->email }}</td><td>{{ $account->role?->name ?? 'Belum ditetapkan' }}</td><td>{{ $account->is_active ? 'Aktif' : 'Dinyahaktifkan' }}</td><td>{{ $account->email_verified_at ? 'Verified' : 'Belum verified' }}</td><td>
@if($account->role?->name === 'Cashier')<a href="{{ route('owner.users.edit',$account) }}">Urus akaun</a>
@else Dilindungi
@endif</td></tr>
@endforeach</tbody></table></div>
@endsection
