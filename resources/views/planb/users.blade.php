@extends('layouts.planb')
@section('title', 'Staff')
@section('content')
<h1>Staff</h1>
<p>Manage who can use CashTrack Secure.</p>
<a class="ct-button" href="{{ route('owner.users.create') }}">Add Staff Member</a>
<div class="ct-panel ct-table"><table><thead><tr><th>Name / Email</th><th>Role</th><th>Access</th><th>Action</th></tr></thead><tbody>
@foreach($users as $account)
    <tr><td>{{ $account->name }}<br>{{ $account->email }}</td><td>{{ $account->role?->name ?? 'Not assigned' }}</td><td>{{ $account->is_active ? 'Available' : 'Unavailable' }}</td><td>
        @if($account->role?->name === 'Cashier')
            <a href="{{ route('owner.users.edit', $account) }}">Edit Staff Member</a>
        @else
            <span class="ct-muted">Owner-managed account</span>
        @endif
    </td></tr>
@endforeach
</tbody></table></div>
@endsection
