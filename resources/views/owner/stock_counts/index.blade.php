@extends('layouts.owner')

@section('title', 'Stock Count History - CashTrack Secure')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 class="fw-bold mb-1">Stock Count History</h2>

        <p class="text-muted mb-0">
            Historical expected versus physical inventory counts.
        </p>
    </div>

    <a
        href="{{ route('owner.stock-counts.create') }}"
        class="btn btn-dark"
    >
        + New Stock Count
    </a>

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
                        <th>Status</th>
                        <th>Counted By</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($stockCounts as $count)

                    <tr>

                        <td>
                            {{ $count->created_at->format('d M Y H:i') }}
                        </td>

                        <td class="fw-semibold">
                            {{ $count->product->name }}
                        </td>

                        <td>
                            {{ $count->expected_quantity }}
                        </td>

                        <td>
                            {{ $count->physical_quantity }}
                        </td>

                        <td class="fw-bold">
                            {{ $count->discrepancy_quantity > 0 ? '+' : '' }}
                            {{ $count->discrepancy_quantity }}
                        </td>

                        <td>

                            @if($count->status === 'MATCH')

                                <span class="badge bg-success">
                                    MATCH
                                </span>

                            @elseif($count->status === 'SHORTAGE')

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
                            {{ $count->user->name }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td
                            colspan="7"
                            class="text-center text-muted py-4"
                        >
                            No stock counts recorded.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>
</div>

@endsection