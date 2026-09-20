@extends('layouts.planb')

@section('content')
    <div class="{{ request()->routeIs('profile.edit', 'security.edit') ? 'ct-account-page' : 'ct-panel' }}">{{ $slot }}</div>
@endsection
