@extends('layouts.owner')

@section('title', 'Dashboard - CashTrack Secure')

@section('content')

<div class="mb-4">
    <h2 class="fw-bold mb-1">Owner Dashboard</h2>

    <p class="text-muted mb-0">
        Overview of inventory, sales and stock discrepancies.
    </p>
</div>

<div class="row g-4 mb-4">

    <div class="col-md-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">

                <p class="text-muted mb-2">
                    Active Products
                </p>

                <h2 class="fw-bold mb-0">
                    {{ $productCount }}
                </h2>

            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">

                <p class="text-muted mb-2">
                    Expected Stock
                </p>

                <h2 class="fw-bold mb-0">
                    {{ $expectedStock }}
                </h2>

                <small class="text-muted">
                    units
                </small>

            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">

                <p class="text-muted mb-2">
                    Open Alerts
                </p>

                <h2 class="fw-bold mb-0">
                    {{ $openAlerts }}
                </h2>

                @if($openAlerts > 0)
                    <small class="text-danger">
                        Requires attention
                    </small>
                @else
                    <small class="text-success">
                        No active discrepancy
                    </small>
                @endif

            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">

                <p class="text-muted mb-2">
                    Recorded Sales
                </p>

                <h2 class="fw-bold mb-0">
                    RM {{ number_format($totalSales, 2) }}
                </h2>

            </div>
        </div>
    </div>

</div>

<div class="card content-card shadow-sm">

    <div class="card-header bg-white border-0 pt-4 px-4">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <h5 class="fw-bold mb-1">
                    Recent Discrepancy Alerts
                </h5>

                <p class="text-muted small mb-0">
                    Latest stock count differences detected by the system.
                </p>
            </div>

            <a
                href="{{ route('owner.alerts.index') }}"
                class="btn btn-outline-dark btn-sm"
            >
                View All
            </a>

        </div>

    </div>

    <div class="card-body px-4">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Expected</th>
                        <th>Physical</th>
                        <th>Difference</th>
                        <th>Type</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($recentAlerts as $alert)

                    <tr>

                        <td class="fw-semibold">
                            {{ $alert->product->name }}
                        </td>

                        <td>
                            {{ $alert->stockCount->expected_quantity }}
                        </td>

                        <td>
                            {{ $alert->stockCount->physical_quantity }}
                        </td>

                        <td>
                            {{ $alert->discrepancy_quantity }}
                        </td>

                        <td>

                            @if($alert->type === 'SHORTAGE')

                                <span class="badge bg-danger">
                                    SHORTAGE
                                </span>

                            @else

                                <span class="badge bg-warning text-dark">
                                    SURPLUS
                                </span>

                            @endif

                        </td>

                        <td>
                            {{ $alert->created_at->format('d M Y H:i') }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td
                            colspan="6"
                            class="text-center text-muted py-4"
                        >
                            No discrepancy alerts recorded.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>
</div>

@endsection