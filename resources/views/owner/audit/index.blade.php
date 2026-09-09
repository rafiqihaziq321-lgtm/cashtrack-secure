@extends('layouts.owner')

@section('title', 'Audit Trail - CashTrack Secure')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 class="fw-bold mb-1">
            Inventory Audit Trail
        </h2>

        <p class="text-muted mb-0">
            Tamper-evident inventory movement ledger.
        </p>
    </div>

    @if($chainValid)

        <span class="badge bg-success fs-6 px-3 py-2">
            ✓ INTEGRITY VALID
        </span>

    @else

        <span class="badge bg-danger fs-6 px-3 py-2">
            ⚠ TAMPERING DETECTED
        </span>

    @endif

</div>

<div class="card content-card shadow-sm">

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Movement</th>
                        <th>Quantity</th>
                        <th>User</th>
                        <th>Reference</th>
                        <th>Hash</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($movements as $movement)

                    <tr>

                        <td>
                            #{{ $movement->id }}
                        </td>

                        <td>
                            {{ $movement->created_at->format('d M Y H:i') }}
                        </td>

                        <td>
                            {{ $movement->product->name }}
                        </td>

                        <td>

                            @if($movement->movement_type === 'SALE')

                                <span class="badge bg-danger">
                                    SALE
                                </span>

                            @elseif($movement->movement_type === 'STOCK_IN')

                                <span class="badge bg-success">
                                    STOCK IN
                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    {{ $movement->movement_type }}
                                </span>

                            @endif

                        </td>

                        <td class="fw-bold">

                            @if($movement->quantity > 0)
                                +{{ $movement->quantity }}
                            @else
                                {{ $movement->quantity }}
                            @endif

                        </td>

                        <td>
                            {{ $movement->user->name }}
                        </td>

                        <td>

                            @if($movement->reference_type)

                                {{ strtoupper($movement->reference_type) }}

                                @if($movement->reference_id)
                                    #{{ $movement->reference_id }}
                                @endif

                            @else
                                -
                            @endif

                        </td>

                        <td>

                            <code
                                title="{{ $movement->record_hash }}"
                            >
                                {{ substr($movement->record_hash, 0, 12) }}...
                            </code>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td
                            colspan="8"
                            class="text-center text-muted py-4"
                        >
                            No inventory movements recorded.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection