<div class="ct-row" data-row>
    @if($mode === 'receipt')<label class="ct-field">Jenis baris<select data-mode name="items[{{ $index }}][mode]"><option value="existing">Barang sedia ada</option><option value="new" @selected(($item['mode'] ?? '') === 'new')>Daftar barang baharu</option></select></label>
@endif
    <div data-existing><label class="ct-field">Cari nama / kod barang / barcode<input data-search list="catalog-options" placeholder="Taip atau pilih barang" autocomplete="off" value="{{ isset($item['product_id']) ? ($products->firstWhere('id', $item['product_id'])?->sku.' — '.$products->firstWhere('id', $item['product_id'])?->name) : '' }}"></label><input type="hidden" data-product name="items[{{ $index }}][product_id]" value="{{ $item['product_id'] ?? '' }}"><p data-product-info class="ct-muted"></p></div>
    @if($mode === 'receipt')<div data-new hidden><p class="ct-muted">Kod barang automatik; maklumat katalog dan penerimaan disimpan bersama.</p>
@include('planb.catalog-fields', ['prefix'=>"items[$index][", 'suffix'=>']', 'values'=>$item])</div>
@endif
    <label class="ct-field">Kuantiti (unit penuh)<input data-quantity name="items[{{ $index }}][quantity]" type="number" min="1" max="1000000" value="{{ $item['quantity'] ?? 1 }}" required></label>
    <button type="button" data-remove class="ct-button ct-secondary">Buang baris</button>
</div>
