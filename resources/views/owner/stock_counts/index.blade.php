<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Count History - CashTrack Secure</title>
</head>

<body>

    <h1>CashTrack Secure</h1>

    <h2>Stock Count History</h2>

    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <p>
        <a href="{{ route('owner.stock-counts.create') }}">
            + New Stock Count
        </a>
    </p>

    <table border="1" cellpadding="8">

        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Expected</th>
                <th>Physical</th>
                <th>Discrepancy</th>
                <th>Status</th>
                <th>Counted By</th>
            </tr>
        </thead>

        <tbody>

            @forelse($stockCounts as $count)

                <tr>

                    <td>
                        {{ $count->created_at->format('Y-m-d H:i') }}
                    </td>

                    <td>
                        {{ $count->product->name }}
                    </td>

                    <td>
                        {{ $count->expected_quantity }}
                    </td>

                    <td>
                        {{ $count->physical_quantity }}
                    </td>

                    <td>
                        {{ $count->discrepancy_quantity }}
                    </td>

                    <td>
                        {{ $count->status }}
                    </td>

                    <td>
                        {{ $count->user->name }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="7">
                        No stock counts recorded.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</body>
</html>