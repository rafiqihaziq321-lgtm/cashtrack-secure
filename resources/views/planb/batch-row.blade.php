<div class="ct-row" data-row>
    @if($mode === 'receipt')<label class="ct-field">Item<select data-mode name="items[{{ $index }}][mode]"><option value="existing">Existing Item</option><option value="new" @selected(($item['mode'] ?? '') === 'new')>New Item</option></select></label>
@endif
    <div data-existing><label class="ct-field">Search by item name, Item Code or barcode<input data-search list="catalog-options" placeholder="Type or select an item" autocomplete="off" value="{{ isset($item['product_id']) ? ($products->firstWhere('id', $item['product_id'])?->sku.' — '.$products->firstWhere('id', $item['product_id'])?->name) : '' }}"></label><input type="hidden" data-product name="items[{{ $index }}][product_id]" value="{{ $item['product_id'] ?? '' }}"><p data-product-info class="ct-muted"></p></div>
    @if($mode === 'receipt')<div data-new hidden><p class="ct-muted">Your Item Code is created automatically. Enter the item details and quantity below.</p>
@include('planb.catalog-fields', ['prefix'=>"items[$index][", 'suffix'=>']', 'values'=>$item])</div>
@endif
    <label class="ct-field">Quantity (whole units)<input data-quantity name="items[{{ $index }}][quantity]" type="number" min="1" max="1000000" value="{{ $item['quantity'] ?? 1 }}" required></label>
    <button type="button" data-remove class="ct-button ct-secondary">Remove Row</button>
</div>
