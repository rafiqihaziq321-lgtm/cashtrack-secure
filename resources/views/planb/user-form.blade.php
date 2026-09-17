
@extends('layouts.planb')
@section('title', 'Staff')
@section('content')
<h1>{{ $account ? 'Edit Staff Member' : 'Add Staff Member' }}</h1><form class="ct-panel" method="post" action="{{ $account ? route('owner.users.update',$account) : route('owner.users.store') }}">
@csrf @if($account)
@method('PUT')
@endif
<label class="ct-field">Name<input name="name" required maxlength="255" value="{{ old('name',$account?->name) }}"></label><label class="ct-field">Email<input name="email" type="email" required maxlength="255" value="{{ old('email',$account?->email) }}"></label><p class="ct-muted">Use this email address to sign in.</p>
<div class="ct-grid"><label class="ct-field">Password {{ $account ? '(leave blank to keep current password)' : '' }}<input name="password" type="password" minlength="12" maxlength="200" autocomplete="new-password" @required(!$account)></label><label class="ct-field">Confirm Password<input name="password_confirmation" type="password" autocomplete="new-password" @required(!$account)></label></div><label class="ct-field">Access<select name="is_active"><option value="1" @selected(old('is_active',$account?->is_active ?? true))>Enabled</option><option value="0" @selected(!old('is_active',$account?->is_active ?? true))>Disabled</option></select></label><p>Set access to Disabled to stop this staff member signing in. Their past transactions remain in Activity History.</p><button class="ct-button">Save Staff Member</button></form>
@endsection
