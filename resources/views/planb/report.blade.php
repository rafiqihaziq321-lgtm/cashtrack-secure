@extends('layouts.planb')
@section('title', 'Reports')
@section('content')
<h1>Sales Report</h1>
<form class="ct-panel" method="get">
    <label class="ct-field">Date<input type="date" name="day" value="{{ $day }}" required></label>
    <div class="ct-actions">
        <button class="ct-button">View Report</button>
        <a class="ct-button ct-secondary" href="{{ route('owner.reports.index', ['day' => $day, 'format' => 'csv']) }}">Export CSV</a>
        <button class="ct-button ct-secondary" type="button" onclick="window.print()">Print</button>
    </div>
</form>
<div class="ct-panel">
    <h2>{{ \Carbon\CarbonImmutable::parse($day)->format('j F Y') }}</h2>
    <p>Sales Total</p><p class="ct-number">RM {{ $total }}</p><p>{{ $sales->count() }} transactions</p>
    <div class="ct-table"><table><thead><tr><th>Transaction</th><th>Time</th><th>Staff Member</th><th>Total (RM)</th></tr></thead><tbody>
    @forelse($sales as $sale)
        <tr><td><a href="{{ route('sales.show', $sale) }}">{{ $sale->transaction_no }}</a></td><td>{{ $sale->created_at->timezone('Asia/Kuala_Lumpur')->format('g:i a') }}</td><td>{{ $sale->cashier->name }}</td><td>{{ $sale->total_amount }}</td></tr>
    @empty
        <tr><td colspan="4">No sales on this date.</td></tr>
    @endforelse
    </tbody></table></div>
</div>
@endsection
