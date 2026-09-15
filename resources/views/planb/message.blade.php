@php
    $messages = [
        'Maklumat barang disimpan. Katalog tidak menambah stok; gunakan penerimaan stok.' => 'Item details saved. Use Receive Stock to add stock.',
        'Semua item penerimaan disimpan bersama.' => 'Stock received successfully.',
        'Jualan disimpan. Harga dan jumlah disahkan pada server.' => 'Sale saved.',
        'Pengeluaran bukan jualan disimpan; tiada hasil jualan ditambah.' => 'Stock removal saved.',
        'Kiraan fizikal disimpan sebagai bukti baharu. Semak perbezaan jika ada.' => 'Stock check saved. Review any stock issues.',
        'Tindakan semakan disimpan.' => 'Review saved.',
        'Akaun Cashier disimpan. Email tidak ditandakan verified secara automatik.' => 'Staff member saved.',
        'Borang ini sudah digunakan dengan maklumat lain. Buka borang baharu.' => 'This form has already been saved with different details. Open a new form.',
        'Harga mesti nombor positif dengan maksimum dua tempat perpuluhan.' => 'Enter a price with no more than two decimal places.',
        'Unit tidak boleh diubah selepas pergerakan stok. Daftar barang lain untuk unit baharu.' => 'The unit cannot change after stock has been recorded. Add a new item for a different unit.',
        'Stok tidak mencukupi atau melebihi had: ' => 'Not enough stock, or the quantity exceeds the limit: ',
        'Gabungkan kuantiti barang yang sama dalam satu baris.' => 'Combine quantities for the same item into one row.',
        'Stok permulaan hanya untuk barang tanpa sejarah pergerakan. Gunakan penerimaan biasa.' => 'This item already has stock history. Choose Delivery instead of Opening Stock.',
        'Barang dihentikan tidak boleh dijual: ' => 'This item is unavailable for sale: ',
        'Jumlah jualan melebihi had RM99,999,999.99.' => 'The sale total exceeds RM99,999,999.99.',
        'Borang kiraan tidak sah. Buka semula borang.' => 'Please start a new stock check.',
        'Borang lapuk atau stok telah berubah. Buka semula borang dan kira semula barang.' => 'Stock has changed or this count has expired. Start a new count.',
        'Kiraan semula mesti melibatkan barang yang sama.' => 'A recheck must be for the same item.',
        'Perbezaan ini sudah selesai. Bukti dan catatan asal dikekalkan.' => 'This stock issue has already been resolved. Its history has been kept.',
        'Bukti lapuk: ada pergerakan atau kiraan baharu. Lakukan kiraan semula; tiada pelarasan disimpan.' => 'Stock has moved or been counted again. Start a new count before adjusting.',
        'Akaun Owner dilindungi daripada penukaran peranan atau nyahaktif.' => 'The Owner role and access cannot be changed.',
        'Akaun operasi dikekalkan untuk sejarah audit.' => 'This account is kept to preserve Activity History.',
        'Akaun dikekalkan untuk sejarah audit. Owner boleh menyahaktif akaun Cashier.' => 'Accounts are kept for Activity History. The Owner can make staff access unavailable.',
        'Akaun ini dinyahaktifkan. Hubungi Owner.' => 'Your access is unavailable. Please contact the Owner.',
    ];
    $displayMessage = $message;
    foreach ($messages as $original => $english) {
        if (str_starts_with($message, $original)) {
            $displayMessage = $english.substr($message, strlen($original));
            break;
        }
    }
@endphp
{{ $displayMessage }}
