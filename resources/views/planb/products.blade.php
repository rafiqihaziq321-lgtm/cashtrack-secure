
@extends('layouts.planb')
@section('title', 'Barang')
@section('content')
<h1>Barang & baki rekod</h1><div class="ct-actions"><a class="ct-button" href="{{ route('owner.stock-in.create') }}">Terima stok / daftar barang baharu</a><a href="{{ route('owner.products.create') }}">Daftar katalog sahaja</a></div>
<form method="get" class="ct-panel"><label class="ct-field">Cari nama, kod barang atau barcode<input name="q" value="{{ $search }}" maxlength="150"></label><button class="ct-button">Cari</button></form>
<div class="ct-panel ct-table"><table><thead><tr><th>Barang</th><th>Harga jual</th><th>Status jualan</th><th>Baki rekod</th><th>Peringatan</th><th></th></tr></thead><tbody>
@forelse($products as $product)<tr><td>{{ $product->name }}<br><small>{{ $product->sku }} · {{ $product->barcode ?: 'Tiada barcode' }}</small></td><td>RM {{ $product->selling_price }}</td><td>{{ $product->is_active ? 'Dijual' : 'Dihentikan' }}</td><td>{{ (int)$product->balance }} {{ $product->unit }}<br><small>{{ (int)$product->balance > 0 ? 'Ada stok mengikut rekod' : 'Habis / baki tidak positif' }}</small></td><td>{{ $product->reorder_level }} {{ $product->unit }}</td><td><a href="{{ route('owner.products.edit', $product) }}">Ubah maklumat</a></td></tr>
@empty<tr><td colspan="6">Tiada barang dijumpai. Mulakan dengan penerimaan stok.</td></tr>
@endforelse</tbody></table>{{ $products->links() }}</div>
@endsection
