@extends('layouts.owner')

@section('title', 'Discrepancy Alerts - CashTrack Secure')

@section('content')

<div class="mb-4">

    <h2 class="fw-bold mb-1">
        Discrepancy Alerts
    </h2>

    <p class="text-muted mb-0">
        Stock shortages and surpluses detected during physical stock counts.
    </p>

</div>

<div class="card content-card shadow-sm">

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Expected</th>
                        <th>Physical</th>
                        <th>Difference</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($alerts as $alert)

                    <tr>

                        <td>
                            {{ $alert->created_at->format('d M Y H:i') }}
                        </td>

                        <td class="fw-semibold">
                            {{ $alert->product->name }}
                        </td>

                        <td>
                            {{ $alert->stockCount->expected_quantity }}
                        </td>

                        <td>
                            {{ $alert->stockCount->physical_quantity }}
                        </td>

                        <td class="fw-bold">
                            {{ $alert->discrepancy_quantity > 0 ? '+' : '' }}
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
                            <span class="badge bg-secondary">
                                {{ $alert->status }}
                            </span>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td
                            colspan="7"
                            class="text-center text-muted py-4"
                        >
                            No discrepancy alerts found.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>
</div>

@endsection