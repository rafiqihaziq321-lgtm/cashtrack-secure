# CashTrack Secure — Plan B

## Semakan 15 September 2026

- Folder disahkan: `C:\CashTrack-Secure-FYP\04_Source_Code\cashtrack-secure-plan-b`. Folder ori tidak diubah.
- Asas sebenar `711ad8dc8f291d9a90d2fad54bb472ebf0b48e92`; branch baharu `plan-b/sv-review`. Working tree asal bersih.
- Perubahan Plan B lama tidak ditemukan; kerja berikut dibina semula di atas kod Day 6.
- Checkpoint ZIP kod tracked tersedia dalam workspace sesi `work/checkpoint-711ad8d.zip`, SHA-256 `E7D696521ACD0A55DEF8DA74733E358402CAD159046B83C464EE61CE27196532`.
- Checkpoint ini bukan backup database, `.env`, uploads atau dependencies.
- Laravel 13.30.1, Fortify 1.39.0, Livewire 4.4.3, PHP 8.5.10, Composer 2.10.3, Node 24.20.0, npm 11.19.0. Tiada upgrade dependency.
- MariaDB 10.4.32 tersedia. Instance baru 127.0.0.1:3307 dengan datadir workspace `work/mariadb`; database `cashtrack_plan_b` dan `cashtrack_plan_b_testing`, akaun berasingan dengan grant hanya database masing-masing.
- `.env` asal Plan B menunjuk `cashtrack_secure`; telah ditukar sebelum Artisan. Tiada config.php cache ditemukan. Environment DB luar tidak ditemukan. `.env.testing` asal tiada; kini diwujudkan dan diabaikan Git.
- PHP CLI tiada pdo_sqlite. Ujian akan menggunakan MariaDB sebenar; ia bukan bukti kelulusan MySQL Oracle.
- Laporan FYP1/panduan FYP2 tidak ditemukan dalam folder dokumentasi/rujukan yang diperiksa. Fail diperlukan untuk menyelaraskan bab dan format akademik.

## Pelan berfasa

1. Checkpoint, pengasingan database, perlindungan runner ujian.
2. Akaun, penerimaan batch, jualan, pengeluaran bukan jualan, kiraan dan alert; transaksi atomic serta kawalan serentak.
3. UI Bahasa Melayu, dashboard, audit aktiviti, ledger versi hash, laporan.
4. Ujian automatik, demo browser, dokumentasi manual dan pakej kod bersih.

## Batas status

Sasaran 90% ialah sasaran pengguna; tiada peratus kesiapan disahkan. Implementasi, ujian teknikal, pengesahan requirement client dan UAT mesti dinilai berasingan. Interview/persetujuan/UAT Hanin Bakery House belum dibuktikan. Sistem standalone, satu kedai, data contoh, produk siap jual; tiada integrasi POS atau pengurusan bahan mentah.

## Risiko asal yang telah dikenal pasti

Jualan satu item tidak menyemak baki; wang menggunakan operasi float. Penerimaan satu item. Kiraan tiada semakan borang lapuk. Alert hanya senarai. Audit hanya ledger/hash. Hash hook tidak mengunci head dan tidak melindungi timestamp. Public registration aktif (akaun tanpa role), self-delete pengguna wujud, middleware tidak memeriksa akaun tidak aktif. Dashboard menjumlahkan semua unit dan semua masa. Seeder mempunyai kata laluan demo tetap.

## Keputusan awal

Gunakan transaksi database dan satu baris kunci penulisan stok bagi satu kedai. Semua laluan penulisan mengambil kunci sebelum membaca baki/head. Ini mengutamakan ketepatan pada skala demo; bukan dakwaan kapasiti komersial. Rekod hash lama dikekalkan; format baru mesti berversi dan verification melaporkan rekod lama yang tiada hash. Pelarasan akan menolak pergerakan selepas kiraan dan meminta kiraan baharu, supaya bukti lapuk tidak digunakan.

## Rekod ujian

Semakan folder/Git/runtime: LULUS. Ujian aplikasi, concurrency, browser, email SMTP, scanner fizikal, UAT client: BELUM DIJALANKAN. Database ori: tidak diuji atau diubah.
