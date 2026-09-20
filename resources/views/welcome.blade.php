@extends('layouts.planb')
@section('title', 'Welcome')
@section('content')
<section class="ct-panel ct-welcome @guest ct-welcome-public @endguest">
    <div class="ct-welcome-copy">
    <span class="ct-badge ct-badge-success">Sales & inventory</span>
    <h1>Your sales and stock,<br>in one place.</h1>
    <p>CashTrack Secure helps you record sales, receive stock and keep track of your items.</p>
    <div class="ct-actions">
        @auth
            <a class="ct-button" href="{{ route('dashboard') }}">Open Dashboard</a>
        @else
            <a class="ct-button" href="{{ route('login') }}">Log In</a>
            <a class="ct-text-action" href="{{ route('password.request') }}">Forgot Password?</a>
        @endauth
    </div>
    </div>
    @guest
        <figure class="ct-welcome-flow" aria-label="Workflow illustration: Receive Stock, then Record Sale, then Check Stock">
            <figcaption><strong>A simple daily workflow</strong></figcaption>
            <ol>
                <li><span class="ct-metric-icon"><flux:icon.arrow-down-tray /></span><div><strong>Receive Stock</strong><p>Add items and record deliveries.</p></div><span class="ct-flow-step" aria-hidden="true">01</span></li>
                <li><span class="ct-metric-icon"><flux:icon.banknotes /></span><div><strong>Record Sale</strong><p>Select items and record the sale.</p></div><span class="ct-flow-step" aria-hidden="true">02</span></li>
                <li><span class="ct-metric-icon"><flux:icon.clipboard-document-check /></span><div><strong>Check Stock</strong><p>Compare counts and review stock issues.</p></div><span class="ct-flow-step" aria-hidden="true">03</span></li>
            </ol>
        </figure>
    @endguest
</section>
<div class="ct-grid ct-welcome-features">
    <article class="ct-panel"><span class="ct-metric-icon"><flux:icon.cube /></span><h2>Manage Inventory</h2><p>Receive deliveries and add new items in the same step.</p></article>
    <article class="ct-panel"><span class="ct-metric-icon"><flux:icon.banknotes /></span><h2>Record Sales</h2><p>Keep sales and other stock removals clearly recorded.</p></article>
    <article class="ct-panel"><span class="ct-metric-icon"><flux:icon.clipboard-document-check /></span><h2>Check Stock</h2><p>Compare your records with a physical count and follow up on stock issues.</p></article>
</div>
@endsection
