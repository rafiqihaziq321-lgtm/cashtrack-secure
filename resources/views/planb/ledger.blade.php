<div class="ct-table"><table><thead><tr><th>Date / Time</th><th>Item</th><th>What Happened</th><th>Recorded Stock<br>Before → After</th><th>Who / Reason</th></tr></thead><tbody>
@forelse($movements as $movement)
    <tr>
        <td>{{ $movement->created_at->timezone('Asia/Kuala_Lumpur')->format('d M Y, g:i a') }}</td>
        <td>{{ $movement->product->name }}<br><small>Item Code: {{ $movement->product->sku }}</small></td>
        <td>{{ ['OPENING' => 'Opening stock received', 'STOCK_IN' => 'Stock received', 'SALE' => 'Sale recorded', 'DAMAGED' => 'Damaged stock removed', 'EXPIRED' => 'Expired stock removed', 'SAMPLE' => 'Sample stock removed', 'OWN_USE' => 'Stock removed for own use', 'ADJUSTMENT' => 'Stock adjusted'][$movement->movement_type] ?? 'Stock movement' }}<br>{{ $movement->quantity }} @include('planb.unit', ['unit' => $movement->product->unit])</td>
        <td>{{ $movement->quantity_before ?? 'Not recorded' }} → {{ $movement->quantity_after ?? 'Not recorded' }}</td>
        <td>{{ $movement->user->name }}<br>{{ $movement->notes ?: 'No reason recorded' }}</td>
    </tr>
@empty
    <tr><td colspan="5">No stock movements yet.</td></tr>
@endforelse
</tbody></table></div>
