# Operasional dan deployment

## Konfigurasi

Salin nilai dari `production.env.example` ke konfigurasi deployment dan ganti semua placeholder. Jangan menimpa `.env` lokal untuk sekadar mencoba antarmuka. Simpan rahasia di konfigurasi server, bukan Git.

- Document root wajib `public/`. Folder app, vendor, writable, dump SQL, `.env`, dan `.git` tidak boleh dilayani web server.
- PHP 8.2+ dengan dukungan Argon2id, MySQL 8, dan ekstensi yang diminta Composer. Jalankan `composer check-platform-reqs` pada server tujuan.
- `CI_ENVIRONMENT=production`: debug dan error detail dimatikan; cookie secure serta pengalihan HTTPS aktif. Pastikan koneksi proxy/HTTPS dikenali dengan benar sebelum membuka trafik. Konfigurasikan trusted proxy hanya untuk alamat proxy yang benar-benar dikelola.
- Gunakan user MySQL aplikasi dengan akses hanya ke database BanuaTour; jangan gunakan root/kata sandi kosong. Migrasi membutuhkan akses DDL, yang dapat menggunakan kredensial deployment terpisah.
- Beri PHP akses tulis hanya ke writable dan folder unggahan yang diperlukan. Backup dan log harus privat. Simpan salinan backup terenkripsi di lokasi terpisah, batasi retensi, dan uji pemulihannya.
- Rate limit memakai cache persisten. Folder cache wajib writable dan tidak memakai dummy handler. Untuk beberapa instance server gunakan cache bersama (misalnya Redis) serta pembatasan login pada reverse proxy. Batas per IP harus mempertimbangkan pengguna yang berbagi jaringan.
- Jangan aktifkan full-page cache pada halaman dengan token CSRF, session, akun, dan tiket. Konfigurasi aplikasi ini tidak mengaktifkan cache halaman.

## Upgrade dan pemulihan

Hentikan sementara penerimaan transaksi saat upgrade. Jalankan `php spark app:upgrade` dengan `MYSQLDUMP_PATH` yang menunjuk binary mysqldump kompatibel server. Perintah menggunakan file konfigurasi privat sementara untuk kredensial, menghapusnya setelah selesai, dan menyimpan dump di `writable/backups/`. Backup gagal menghentikan migrasi.

Migrasi memakai lock dan dapat dilanjutkan setelah kendala diperbaiki. MySQL DDL tidak sepenuhnya atomik: jika migrasi gagal, periksa log dan skema sebelum menjalankan ulang. Jangan menggunakan `migrate:rollback` untuk menghapus data pembayaran; migrasi hardening menolak rollback destruktif.

Untuk pemulihan, pilih backup yang sudah diuji, hentikan penulisan aplikasi, pulihkan ke database terpisah, dan verifikasi jumlah data serta transaksi sebelum mengalihkan koneksi. Dump tidak mencakup foto. Backup `public/uploads` secara terpisah. Perubahan kode dan database harus dipulihkan sebagai versi yang cocok.

## Unggahan

Foto baru di-decode dan di-encode ulang sebagai JPEG dengan nama acak, maksimal 2 MB/16 megapiksel, dan diperkecil hingga sisi terpanjang 1.920 px. Tidak menyimpan ekstensi/nama file dari klien. Impor berita hanya XLSX maksimal 5 MB, 20 MB setelah dibuka, 2.000 baris; formula ditolak, semua baris divalidasi, hasil masuk sebagai draf.

Apache: gunakan `.htaccess` di public dan public/uploads dengan `AllowOverride` yang sesuai. Nginx: `.htaccess` tidak dibaca; terapkan blok untuk eksekusi unggahan sebelum lokasi PHP umum, misalnya:

```nginx
location ~* ^/uploads/.*\.(?:php[0-9]?|phtml|phar|cgi|pl|py|sh|html?|svg)(?:\.|$) {
    deny all;
}
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Nonaktifkan autoindex dan pastikan PHP-FPM hanya menjalankan file PHP yang ada di document root. Periksa unggahan lama secara terpisah; normalisasi di aplikasi berlaku untuk unggahan baru.

## Batas implementasi

Konfirmasi pembayaran masih manual. Gateway, webhook bertanda tangan, rekonsiliasi otomatis, refund, pembatasan kapasitas destinasi, serta pemulihan kata sandi lewat email belum diimplementasikan. Definisikan proses pengelola sebelum menerima pengguna umum.

Bootstrap, ikon, QR, dan sebagian asset admin masih memakai CDN; peta/video dan sebagian gambar berita berasal dari pihak ketiga. Akses internet diperlukan untuk asset tersebut. CSP ketat dan pemindahan asset ke server sendiri memerlukan penyesuaian script inline serta pengujian tambahan; jangan memasang CSP yang memblokir alur aplikasi secara langsung.

Tes otomatis memakai SQLite, sedangkan deployment memakai MySQL. Migrasi sudah dijalankan pada MySQL lokal dengan backup. Tetap lakukan smoke test staging untuk login, pemesanan, konfirmasi pembayaran, tiket, kunjungan, arsip, dan unggahan menggunakan data uji sebelum deployment publik. Hasil pengujian bukan jaminan bebas seluruh kerentanan.
