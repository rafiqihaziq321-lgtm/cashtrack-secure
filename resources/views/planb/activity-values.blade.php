@php
    $isStaff = ($activity->reference_type ?? null) === 'user';
    $fieldLabels = [
        'name' => 'Name', 'email' => 'Email', 'sku' => 'Item Code', 'barcode' => 'Barcode', 'unit' => 'Unit',
        'selling_price' => 'Selling Price (RM)', 'reorder_level' => 'Low Stock Alert', 'description' => 'Description',
        'is_active' => $isStaff ? 'Access' : 'Availability', 'password_changed' => 'Password Changed', 'email_verified' => 'Email Verified',
        'expected' => 'Recorded Stock', 'physical' => 'Physical Count', 'difference' => 'Difference',
        'quantity' => 'Quantity', 'total_amount' => 'Sales Total (RM)', 'transaction_no' => 'Transaction',
        'purpose' => 'Receiving Type', 'items' => 'Item Types', 'status' => 'Status',
    ];
    $visibleValues = array_intersect_key($values, $fieldLabels);
@endphp
@if($visibleValues)
    <dl class="ct-values">
        @foreach($visibleValues as $field => $value)
            <dt>{{ $fieldLabels[$field] }}</dt>
            <dd>
                @if($field === 'is_active')
                    {{ $isStaff ? ($value ? 'Enabled' : 'Disabled') : ($value ? 'Available' : 'Unavailable') }}
                @elseif(in_array($field, ['password_changed', 'email_verified'], true))
                    {{ $value ? 'Yes' : 'No' }}
                @elseif($field === 'unit')
                    @include('planb.unit', ['unit' => $value])
                @elseif($field === 'purpose')
                    {{ ['OPENING' => 'Opening Stock', 'STOCK_IN' => 'Supplier Delivery'][$value] ?? 'Receiving' }}
                @elseif($field === 'status')
                    {{ ['OPEN' => 'Needs Review', 'REVIEWING' => 'Under Review', 'RESOLVED' => 'Resolved', 'MATCH' => 'Matches', 'SHORTAGE' => 'Shortage', 'SURPLUS' => 'Surplus', 'completed' => 'Completed'][$value] ?? 'Recorded' }}
                @elseif($field === 'name')
                    @include('planb.message', ['message' => $value ?? 'Not set'])
                @else
                    {{ is_scalar($value) && $value !== '' ? $value : 'Not set' }}
                @endif
            </dd>
        @endforeach
    </dl>
@else
    <span class="ct-muted">—</span>
@endif
