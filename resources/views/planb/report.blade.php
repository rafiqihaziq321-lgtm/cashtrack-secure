@extends('layouts.planb')
@section('title', 'Reports')
@section('content')
<div class="ct-page-heading"><div><h1>Sales Report</h1><p class="ct-muted">Daily sales at a glance. Open a transaction to view its receipt.</p></div></div>
<form class="ct-panel ct-report-toolbar" method="get" aria-label="Report filters">
    <label class="ct-field">Date<input type="date" name="day" value="{{ $day }}" required></label>
    <div class="ct-actions">
        <button class="ct-button">View Report</button>
        <a class="ct-button ct-secondary" href="{{ route('owner.reports.index', ['day' => $day, 'format' => 'csv']) }}"><flux:icon.arrow-down-tray />Export CSV</a>
        <button class="ct-button ct-secondary" type="button" onclick="window.print()"><flux:icon.printer />Print</button>
    </div>
</form>
<section class="ct-panel ct-report-summary" aria-label="Daily sales summary">
    <div><div class="ct-metric-heading"><h2>Sales Total</h2><span class="ct-metric-icon"><flux:icon.banknotes /></span></div><p class="ct-number"><span class="ct-currency">RM</span> {{ $total }}</p></div>
    <div class="ct-report-period"><h2>{{ \Carbon\CarbonImmutable::parse($day)->format('j F Y') }}</h2><p>{{ $sales->count() }} {{ $sales->count() === 1 ? 'transaction' : 'transactions' }}</p></div>
</section>
<section class="ct-panel ct-table-panel" aria-labelledby="transactions-heading">
    <div class="ct-section-heading"><h2 id="transactions-heading">Transactions</h2><p class="ct-muted">{{ \Carbon\CarbonImmutable::parse($day)->format('j F Y') }}</p></div>
    <div class="ct-table" role="region" aria-label="Sales transactions" tabindex="0"><table><thead><tr><th scope="col">Transaction</th><th scope="col">Time</th><th scope="col">Staff Member</th><th scope="col" class="ct-numeric">Total (RM)</th></tr></thead><tbody>
    @forelse($sales as $sale)
        <tr><td><a class="ct-text-action" href="{{ route('sales.show', $sale) }}">{{ $sale->transaction_no }}</a></td><td>{{ $sale->created_at->timezone('Asia/Kuala_Lumpur')->format('g:i a') }}</td><td>{{ $sale->cashier->name }}</td><td class="ct-numeric ct-transaction-total">{{ $sale->total_amount }}</td></tr>
    @empty
        <tr><td colspan="4"><div class="ct-empty"><span class="ct-empty-icon"><flux:icon.banknotes /></span><strong>No sales on this date.</strong><p>Choose another date to view its sales and transactions.</p></div></td></tr>
    @endforelse
    </tbody></table></div>
</section>
@endsection
