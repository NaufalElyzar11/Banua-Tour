# BanuaTour

Aplikasi platform wisata Kalimantan Selatan dengan katalog publik, favorit (wishlist), pemesanan tiket kunjungan, ulasan terverifikasi, dan panel manajemen admin. Dibangun menggunakan CodeIgniter 4.7, PHP 8.2+, dan MySQL 8.

---

## 📌 Tentang Proyek & Atribusi (Project Lineage)

Repositori ini merupakan **versi pembaruan mandiri (revamp, refactor, & security hardening)** dari proyek tugas kuliah:

* **Versi Awal (Proyek Kelompok UAS):**  
  Aplikasi ini bermula dari tugas kelompok Ujian Akhir Semester (UAS) mata kuliah **Pemrograman Web II** yang dikerjakan secara kolaboratif bersama tim. Seluruh riwayat orisinal dan kontribusi tim tetap tersimpan dan diarsipkan di:  
  👉 **[uas-web-ii (Original UAS Project)](https://github.com/NaufalElyzar11/uas-web-ii)**

* **Versi Saat Ini (Pengembangan Mandiri):**  
  Pada versi ini, proyek dikembangkan lebih lanjut secara independen untuk meningkatkan kualitas, keandalan, dan kesiapan produksi:
  * **Keamanan & Autentikasi:** Verifikasi role pengguna langsung dari database, penguatan proteksi CSRF, sanitasi input, rate limiting, serta hashing kata sandi aman (Bcrypt).
  * **Integritas Transaksi & Tiket:** Perhitungan total harga dari server (bukan dari browser), audit log konfirmasi tiket acak, pencegahan konfirmasi ganda, serta validasi hak akses tiket kunjungan.
  * **Peningkatan Panel Admin:** Manajemen lengkap untuk katalog destinasi, kategori, berita/artikel wisata, verifikasi pemesanan, serta manajemen pengguna.
  * **Pengujian Otomatis (Automated Testing):** Penerapan *test suite* keamanan & regresi berbasis PHPUnit (SQLite in-memory).

---

## Menjalankan aplikasi

1. Aktifkan MySQL dan siapkan `.env` dengan koneksi database serta `app.baseURL` lokal.
2. Jalankan `composer install`.
3. Untuk database yang sudah berisi data BanuaTour, jalankan `php spark app:upgrade`. Perintah membuat backup sebelum menjalankan migrasi. Jika backup gagal, migrasi dibatalkan.
4. Jalankan `php spark serve --host 127.0.0.1 --port 8080`, lalu buka `http://localhost:8080`.

Gunakan PHP dengan ekstensi intl, mbstring, mysqli, GD, ZIP, DOM/XML, dan fileinfo. Untuk pengujian, aktifkan SQLite3.

Untuk instalasi baru, buat database kosong lalu impor `database/schema.sql`. File ini hanya berisi struktur tabel, tanpa akun, transaksi, atau data pribadi. Jalankan `php spark app:upgrade` setelah impor. Daftarkan akun melalui aplikasi, lalu administrator database dapat memberi role admin kepada akun yang dipilih. Tidak ada kredensial admin bawaan. Kategori awal dapat ditambahkan melalui SQL atau dataset yang sudah diperiksa; destinasi ditambahkan melalui panel admin.

`app/Database/LegacyMigrations` menyimpan prototipe lama yang tidak cocok dengan skema aktual. Folder ini sengaja tidak dijalankan oleh migrator. Jangan memindahkannya kembali ke folder migrasi aktif.

## Alur transaksi

- Pesanan baru berstatus `upcoming` dan `unpaid`. Harga dihitung dari database, bukan dari total yang dikirim browser.
- Admin memeriksa pembayaran yang benar-benar diterima, lalu memasukkan referensi pada panel Pesanan. Konfirmasi mencatat admin, waktu, referensi, dan kode tiket acak.
- Tiket hanya dapat dibaca pemilik setelah pembayaran terverifikasi. Membuka tiket tidak mengubah status pesanan.
- Admin menandai kunjungan selesai setelah tanggal kunjungan tercapai dan tiket diperiksa. Perubahan status serta audit dilakukan dalam satu transaksi database. Konfirmasi ganda ditolak.
- Pengguna hanya dapat membatalkan pesanan yang belum dibayar. Permintaan refund untuk pesanan lunas masih ditangani pengelola; belum ada refund otomatis.
- Arsip menyembunyikan pesanan dari daftar dan dapat dipulihkan. Data transaksi tidak dihapus.
- Ulasan baru memerlukan kunjungan selesai dengan pembayaran terverifikasi.

**Belum ada integrasi payment gateway.** Jangan menggunakan konfirmasi admin sebagai pengganti verifikasi pembayaran. Isi kontak pengelola, ketentuan tiket, jam buka, fasilitas, dan akses destinasi melalui panel admin sebelum menerima pemesanan publik. Nilai yang belum tersedia ditampilkan sebagai belum diinformasikan, tanpa data buatan.

Pesanan lama mendapat status pembayaran `unpaid`, termasuk yang sebelumnya bertanda selesai. Status lama tidak dianggap bukti pembayaran. Rekonsiliasi catatan lama terhadap bukti pembayaran sebelum mengaktifkan tiketnya; jangan menandai seluruh data lama sebagai lunas secara massal.

## Pemeriksaan

```sh
composer validate --strict
composer audit
php vendor/bin/phpunit --no-coverage
```

Tes keamanan menggunakan SQLite dalam memori dan menolak koneksi selain database pengujian tersebut. Cakupan: CSRF, role terbaru dari database, kepemilikan tiket, transaksi dan rollback, replay pesanan/pembayaran, tanggal dan jumlah, harga dari server, escaping pencarian, pagination, registrasi, hashing, rate limit, profil, ulasan, serta normalisasi gambar dan impor XLSX. Tes Selenium lama bersifat terpisah dan tidak otomatis dijalankan terhadap database lokal.

## Produksi

Lihat `docs/DEPLOYMENT.md` dan `production.env.example`. Arahkan document root ke `public/`, gunakan HTTPS dan pengguna database khusus. Jangan menggunakan `spark serve`, akun database root, atau environment development untuk layanan publik.