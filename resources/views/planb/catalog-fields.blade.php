<div class="ct-grid">
<label class="ct-field">Nama barang<input name="{{ $prefix }}name{{ $suffix }}" value="{{ $values['name'] ?? '' }}" required maxlength="150"></label>
<label class="ct-field">Unit<select name="{{ $prefix }}unit{{ $suffix }}" required>
@foreach(['unit','biji','pek','kotak','potong'] as $unit)<option value="{{ $unit }}" @selected(($values['unit'] ?? 'biji') === $unit)>{{ $unit }}</option>
@endforeach</select></label>
<label class="ct-field">Harga jual seunit (RM)<input name="{{ $prefix }}selling_price{{ $suffix }}" value="{{ $values['selling_price'] ?? '' }}" type="number" step="0.01" min="0" max="999999.99" required></label>
<label class="ct-field">Ingatkan tambah stok apabila baki ≤<input name="{{ $prefix }}reorder_level{{ $suffix }}" value="{{ $values['reorder_level'] ?? 5 }}" type="number" min="0" max="1000000" required></label>
<label class="ct-field">Barcode (pilihan)<input name="{{ $prefix }}barcode{{ $suffix }}" value="{{ $values['barcode'] ?? '' }}" maxlength="100" pattern="[A-Za-z0-9-]+"></label>
<label class="ct-field">Penerangan (pilihan)<input name="{{ $prefix }}description{{ $suffix }}" value="{{ $values['description'] ?? '' }}" maxlength="1000"></label>
</div>
