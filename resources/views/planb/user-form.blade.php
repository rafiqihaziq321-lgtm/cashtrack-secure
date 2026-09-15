
@extends('layouts.planb')
@section('title', 'Urus Cashier')
@section('content')
<h1>{{ $account ? 'Urus akaun Cashier' : 'Cipta akaun Cashier' }}</h1><form class="ct-panel" method="post" action="{{ $account ? route('owner.users.update',$account) : route('owner.users.store') }}">
@csrf @if($account)
@method('PUT')
@endif
<label class="ct-field">Nama<input name="name" required maxlength="255" value="{{ old('name',$account?->name) }}"></label><label class="ct-field">Email untuk login<input name="email" type="email" required maxlength="255" value="{{ old('email',$account?->email) }}"></label><p class="ct-muted">Email sebagai ID login tidak bermakna alamat tersebut sudah disahkan.</p>
<div class="ct-grid"><label class="ct-field">Kata laluan {{ $account ? '(kosongkan untuk kekalkan)' : '' }}<input name="password" type="password" minlength="12" maxlength="200" autocomplete="new-password" @required(!$account)></label><label class="ct-field">Ulang kata laluan<input name="password_confirmation" type="password" autocomplete="new-password" @required(!$account)></label></div><label class="ct-field">Status akses<select name="is_active"><option value="1" @selected(old('is_active',$account?->is_active ?? true))>Aktif</option><option value="0" @selected(!old('is_active',$account?->is_active ?? true))>Dinyahaktifkan</option></select></label><p>Selepas nyahaktif, request seterusnya melalui sesi lama akan ditolak. Sejarah transaksi kekal.</p><button class="ct-button">Simpan akaun Cashier</button></form>
@endsection
