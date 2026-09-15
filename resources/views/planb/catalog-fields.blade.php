<div class="ct-grid">
<label class="ct-field">Item Name<input name="{{ $prefix }}name{{ $suffix }}" value="{{ $values['name'] ?? '' }}" required maxlength="150"></label>
<label class="ct-field">Unit<select name="{{ $prefix }}unit{{ $suffix }}" required>
@foreach(['unit' => 'Unit', 'biji' => 'Piece', 'pek' => 'Pack', 'kotak' => 'Box', 'potong' => 'Slice'] as $unit => $unitLabel)<option value="{{ $unit }}" @selected(($values['unit'] ?? 'biji') === $unit)>{{ $unitLabel }}</option>
@endforeach</select></label>
<label class="ct-field">Selling Price per Unit (RM)<input name="{{ $prefix }}selling_price{{ $suffix }}" value="{{ $values['selling_price'] ?? '' }}" type="number" step="0.01" min="0" max="999999.99" required></label>
<label class="ct-field">Low Stock Alert (when stock reaches this quantity)<input name="{{ $prefix }}reorder_level{{ $suffix }}" value="{{ $values['reorder_level'] ?? 5 }}" type="number" min="0" max="1000000" required></label>
<label class="ct-field">Barcode (optional)<input name="{{ $prefix }}barcode{{ $suffix }}" value="{{ $values['barcode'] ?? '' }}" maxlength="100" pattern="[A-Za-z0-9-]+"></label>
<label class="ct-field">Description (optional)<input name="{{ $prefix }}description{{ $suffix }}" value="{{ $values['description'] ?? '' }}" maxlength="1000"></label>
</div>
