@extends('layouts.planb')
@section('title', 'Reports')
@section('content')
@php
    $isMonthly = $reportView === 'monthly';
    $periodLabel = \Carbon\CarbonImmutable::parse($isMonthly ? $month.'-01' : $day)->format($isMonthly ? 'F Y' : 'j F Y');
    $exportParameters = $isMonthly ? ['view' => 'monthly', 'month' => $month, 'format' => 'csv'] : ['day' => $day, 'format' => 'csv'];
@endphp
<div class="ct-page-heading"><div><h1>Sales Report</h1><p class="ct-muted">Sales at a glance. Open a transaction to view its receipt.</p></div>
    <nav class="ct-report-switch ct-no-print" aria-label="Report period">
        <a class="ct-button {{ $isMonthly ? 'ct-secondary' : '' }}" href="{{ route('owner.reports.index', ['day' => $day]) }}" @if(!$isMonthly) aria-current="page" @endif>Daily</a>
        <a class="ct-button {{ $isMonthly ? '' : 'ct-secondary' }}" href="{{ route('owner.reports.index', ['view' => 'monthly', 'month' => $month]) }}" @if($isMonthly) aria-current="page" @endif>Monthly</a>
    </nav>
</div>
<form class="ct-panel ct-report-toolbar" method="get" aria-label="Report filters">
    @if($isMonthly)
    <input type="hidden" name="view" value="monthly">
    <label class="ct-field">Month<input type="month" name="month" value="{{ $month }}" required></label>
    @else
    <label class="ct-field">Date<input type="date" name="day" value="{{ $day }}" required></label>
    @endif
    <div class="ct-actions">
        <button class="ct-button">View Report</button>
        <a class="ct-button ct-secondary" href="{{ route('owner.reports.index', $exportParameters) }}"><flux:icon.arrow-down-tray />Export CSV</a>
        <button class="ct-button ct-secondary" type="button" onclick="window.print()"><flux:icon.printer />Print</button>
    </div>
</form>
<section class="ct-panel ct-report-summary" aria-label="{{ $isMonthly ? 'Monthly' : 'Daily' }} sales summary">
    <div><div class="ct-metric-heading"><h2>Sales Total</h2><span class="ct-metric-icon"><flux:icon.banknotes /></span></div><p class="ct-number"><span class="ct-currency">RM</span> {{ $total }}</p></div>
    @if($isMonthly)
    <div><h2>Average Sale Value</h2><p class="ct-number"><span class="ct-currency">RM</span> {{ $average }}</p></div>
    @endif
    <div class="ct-report-period"><h2>{{ $periodLabel }}</h2><p>{{ $sales->count() }} {{ $sales->count() === 1 ? 'transaction' : 'transactions' }}</p></div>
</section>
@if($isMonthly)
    @include('planb.report-chart')
@endif
<section class="ct-panel ct-table-panel" aria-labelledby="transactions-heading">
    <div class="ct-section-heading"><h2 id="transactions-heading">Transactions</h2><p class="ct-muted">{{ $periodLabel }}</p></div>
    <div class="ct-table" role="region" aria-label="Sales transactions" tabindex="0"><table><thead><tr><th scope="col">Transaction</th><th scope="col">{{ $isMonthly ? 'Date / Time' : 'Time' }}</th><th scope="col">{{ $isMonthly ? 'Recorded By' : 'Staff Member' }}</th><th scope="col" class="ct-numeric">Total (RM)</th></tr></thead><tbody>
    @forelse($sales as $sale)
        <tr><td><a class="ct-text-action" href="{{ route('sales.show', $sale) }}">{{ $sale->transaction_no }}</a></td><td>{{ $sale->created_at->timezone('Asia/Kuala_Lumpur')->format($isMonthly ? 'j M Y, g:i a' : 'g:i a') }}</td><td>{{ $sale->cashier?->name ?? 'Staff member' }}</td><td class="ct-numeric ct-transaction-total">{{ $sale->total_amount }}</td></tr>
    @empty
        <tr><td colspan="4"><div class="ct-empty"><span class="ct-empty-icon"><flux:icon.banknotes /></span><strong>{{ $isMonthly ? 'No sales in this month.' : 'No sales on this date.' }}</strong><p>{{ $isMonthly ? 'Choose another month to view its sales and transactions.' : 'Choose another date to view its sales and transactions.' }}</p></div></td></tr>
    @endforelse
    </tbody></table></div>
</section>
@endsection
