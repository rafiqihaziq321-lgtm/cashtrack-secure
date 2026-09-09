@extends('layouts.cashier')

@section('title', 'Cashier Dashboard - CashTrack Secure')

@section('content')

<div class="mb-4">
    <h2 class="fw-bold mb-1">
        Cashier Dashboard
    </h2>

    <p class="text-muted mb-0">
        Record customer sales securely through CashTrack Secure.
    </p>
</div>

<div class="row g-4">

    <div class="col-md-6">

        <div class="card content-card shadow-sm h-100">

            <div class="card-body p-4">

                <h5 class="fw-bold">
                    New Sale
                </h5>

                <p class="text-muted">
                    Record a product sale and automatically update the inventory ledger.
                </p>

                <a
                    href="{{ route('cashier.sales.create') }}"
                    class="btn btn-dark"
                >
                    Record Sale
                </a>

            </div>

        </div>

    </div>

    <div class="col-md-6">

        <div class="card content-card shadow-sm h-100">

            <div class="card-body p-4">

                <h5 class="fw-bold">
                    Secure Transaction Recording
                </h5>

                <p class="text-muted mb-0">
                    Each completed sale generates an inventory movement that becomes part of the system audit trail.
                </p>

            </div>

        </div>

    </div>

</div>

@endsection