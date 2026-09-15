@extends('layouts.planb')
@section('title', 'Welcome')
@section('content')
<div class="ct-panel" style="padding:clamp(24px,5vw,64px)">
    <h1 style="font-size:clamp(2rem,5vw,3.4rem);max-width:800px">Your sales and stock,<br>in one place.</h1>
    <p style="max-width:720px;font-size:1.1rem">CashTrack Secure helps you record sales, receive stock and keep track of your items.</p>
    <div class="ct-actions" style="margin-top:24px">
        @auth
            <a class="ct-button" href="{{ route('dashboard') }}">Open Dashboard</a>
        @else
            <a class="ct-button" href="{{ route('login') }}">Log In</a>
            <a href="{{ route('password.request') }}">Forgot Password?</a>
        @endauth
    </div>
</div>
<div class="ct-grid">
    <article class="ct-panel"><h2>Manage Inventory</h2><p>Receive deliveries and add new items in the same step.</p></article>
    <article class="ct-panel"><h2>Record Sales</h2><p>Keep sales and other stock removals clearly recorded.</p></article>
    <article class="ct-panel"><h2>Check Stock</h2><p>Compare your records with a physical count and follow up on stock issues.</p></article>
</div>
@endsection
