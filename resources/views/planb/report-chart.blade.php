@php
    $chartMaximum = max(100, (int) ceil(max($dailyTotals) / 100) * 100);
    $columnWidth = 640 / count($dailyTotals);
@endphp
<section class="ct-panel ct-sales-chart" aria-labelledby="sales-chart-heading">
    <div class="ct-section-heading"><h2 id="sales-chart-heading">Daily Sales</h2><p class="ct-muted">{{ $periodLabel }} · Sales in RM</p></div>
    <div class="ct-chart-scroll" role="region" aria-label="Monthly daily sales chart" tabindex="0">
        <svg viewBox="0 0 760 260" role="img" aria-labelledby="sales-chart-title sales-chart-description">
            <title id="sales-chart-title">Daily sales for {{ $periodLabel }}</title>
            <desc id="sales-chart-description">Bar chart of completed sales in RM for each day. Days without sales show zero. Exact amounts are available in Daily sales values below.</desc>
            @foreach([0, 0.5, 1] as $fraction)
                <line class="ct-chart-grid" x1="90" x2="730" y1="{{ 210 - 170 * $fraction }}" y2="{{ 210 - 170 * $fraction }}" />
                <text x="80" y="{{ 214 - 170 * $fraction }}" text-anchor="end">{{ number_format($chartMaximum * $fraction / 100, 2) }}</text>
            @endforeach
            <text x="90" y="20">Sales (RM)</text>
            @foreach($dailyTotals as $date => $cents)
                @php
                    $x = 90 + $loop->index * $columnWidth;
                    $height = $cents / $chartMaximum * 170;
                @endphp
                <rect class="ct-chart-bar" x="{{ $x + 3 }}" y="{{ 210 - $height }}" width="{{ $columnWidth - 6 }}" height="{{ $height }}" rx="2">
                    <title>{{ \Carbon\CarbonImmutable::parse($date)->format('j F') }}: RM {{ \App\Services\InventoryService::money($cents) }}</title>
                </rect>
                <text x="{{ $x + $columnWidth / 2 }}" y="230" text-anchor="middle">{{ $loop->iteration }}</text>
            @endforeach
            <text x="410" y="253" text-anchor="middle">Day of month</text>
        </svg>
    </div>
    <details class="ct-chart-values">
        <summary>Daily sales values</summary>
        <div class="ct-table" role="region" aria-label="Daily sales values" tabindex="0"><table><thead><tr><th scope="col">Date</th><th scope="col" class="ct-numeric">Sales (RM)</th></tr></thead><tbody>
        @foreach($dailyTotals as $date => $cents)
            <tr><th scope="row">{{ \Carbon\CarbonImmutable::parse($date)->format('j F Y') }}</th><td class="ct-numeric">{{ \App\Services\InventoryService::money($cents) }}</td></tr>
        @endforeach
        </tbody></table></div>
    </details>
</section>
