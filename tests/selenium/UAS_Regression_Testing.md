# UAS Web Programming II — Automation & Regression Testing

**Nama Aplikasi  :** BanuaTour (CodeIgniter 4 — Tourism Booking Web App)
**Lokasi Proyek  :** `C:\laragon\www\uas-web-ii`
**Base URL Lokal :** `http://localhost/uas-web-ii/public` *(sesuaikan jika menggunakan Virtual Host Laragon, mis. `http://uas-web-ii.test`)*

---

## 1. Regression Testing — Penjelasan, Kelebihan, dan Kekurangan

### 1.1 Definisi

**Regression Testing** (Pengujian Regresi) adalah jenis pengujian perangkat lunak yang dilakukan untuk memastikan bahwa perubahan pada kode — baik berupa penambahan fitur baru, perbaikan bug, *patch*, maupun *refactoring* — **tidak merusak fungsi-fungsi yang sebelumnya sudah berjalan dengan benar**. Tujuannya adalah menjamin bahwa modifikasi tidak menimbulkan *side-effect* atau "kemunduran" (regression) pada bagian sistem lain.

Menurut **Glenford J. Myers, Corey Sandler, dan Tom Badgett** dalam buku **"The Art of Software Testing, 3rd Edition" (John Wiley & Sons, 2011)**, regression testing didefinisikan sebagai *"the process of retesting a system after it has been modified to ensure that no previously working functionality has been broken by the change."* Kegiatan ini biasanya dilakukan dengan menjalankan ulang sekumpulan *test case* yang sudah ada (sering disebut *regression test suite*) setiap kali ada *build* atau rilis baru.

**Buku referensi utama:**
- **Judul :** *The Art of Software Testing, 3rd Edition*
- **Penulis :** Glenford J. Myers, Corey Sandler, Tom Badgett
- **Penerbit :** John Wiley & Sons, Inc. (2011), ISBN 978-1-118-03196-4
- **Link    :** https://www.wiley.com/en-us/The+Art+of+Software+Testing%2C+3rd+Edition-p-9781118031964

**Referensi pendukung:**
- Ron Patton, *Software Testing, 2nd Edition*, Sams Publishing, 2005 — https://www.oreilly.com/library/view/software-testing-second/0672327988/
- ISTQB® Foundation Level Syllabus 2018 — https://www.istqb.org/certifications/certified-tester-foundation-level

### 1.2 Kelebihan (Advantages)

| No | Kelebihan | Penjelasan |
|----|-----------|------------|
| 1 | **Menjaga stabilitas sistem** | Memastikan fitur lama tetap berfungsi setelah perubahan kode. |
| 2 | **Mendeteksi bug lebih cepat** | Bug akibat *side-effect* langsung tertangkap pada *build* berikutnya. |
| 3 | **Cocok untuk otomatisasi** | Test case bersifat berulang (repetitive), sangat efisien jika di-otomasi dengan tool seperti Selenium. |
| 4 | **Meningkatkan kepercayaan rilis** | Tim dan stakeholder lebih percaya diri merilis fitur baru karena regresi sudah diuji. |
| 5 | **Mendukung Continuous Integration** | Regression suite dapat dijalankan otomatis pada pipeline CI/CD setiap *commit*. |
| 6 | **Dokumentasi perilaku sistem** | Test case berfungsi sebagai *living documentation* mengenai perilaku yang diharapkan. |

### 1.3 Kekurangan (Disadvantages)

| No | Kekurangan | Penjelasan |
|----|------------|------------|
| 1 | **Memakan waktu** | Pada aplikasi besar, eksekusi seluruh regression suite bisa berjam-jam. |
| 2 | **Biaya pemeliharaan tinggi** | Setiap perubahan UI/fitur menuntut pembaruan script test, terutama untuk pengujian otomatis. |
| 3 | **Membutuhkan investasi awal** | Perlu *tooling*, infrastruktur, dan keahlian (Selenium, framework testing, CI). |
| 4 | **Bisa menimbulkan *false positive/negative*** | Test yang rapuh (*flaky test*) dapat gagal padahal aplikasi benar, atau lulus padahal ada bug. |
| 5 | **Tidak mendeteksi bug baru di luar cakupan** | Hanya menemukan regresi pada fungsi yang sudah ada *test case*-nya. |
| 6 | **Sulit memilih cakupan optimum** | Menentukan *which test to re-run* (full vs selective regression) memerlukan analisis dampak yang matang. |

---

## 2. Aplikasi Target Automation Testing

**Nama Aplikasi :** BanuaTour — Aplikasi Pemesanan Wisata Kalimantan Selatan
**Framework    :** CodeIgniter 4 (PHP)
**Database     :** MySQL (`banuatour`)
**Fitur Utama  :**

1. **Auth** — Register, Login, Logout
2. **Destinasi** — Listing, Detail, Pencarian
3. **Wishlist** — Tambah / Hapus
4. **Booking** — Pembelian tiket wisata
5. **Riwayat** — Daftar booking, cancel, lihat tiket
6. **Profile** — Update data, ganti password
7. **Admin Panel** — CRUD users, wisata, berita, review, booking

**Alasan dipilih:** Aplikasi sudah memiliki form HTML standar (input text, password, select, submit) dan navigasi berbasis URL — sangat cocok untuk *automation testing* dengan **Selenium WebDriver**.

---

## 3. Test Case — 20 Positif & 20 Negatif

> Penomoran: `TC-P-xx` = positive scenario, `TC-N-xx` = negative scenario.

### 3.1 Skenario POSITIF (20)

| ID | Skenario | Langkah | Expected Result |
|----|----------|---------|-----------------|
| TC-P-01 | Buka halaman home | Kunjungi `/` | Halaman home tampil, judul memuat "BanuaTour" |
| TC-P-02 | Buka halaman login | Klik link Login | Form login tampil dengan field email & password |
| TC-P-03 | Buka halaman registrasi | Klik link "Daftar di sini" | Form registrasi muncul dengan 8 field |
| TC-P-04 | Registrasi user valid | Isi seluruh field valid, submit | Redirect ke login + flash "Registrasi berhasil" |
| TC-P-05 | Login user valid | Email + password benar | Redirect ke `/user_home` |
| TC-P-06 | Login admin valid | Username admin + password admin | Redirect ke `/admin/dashboard` |
| TC-P-07 | Login dengan username | Pakai username (bukan email) | Login berhasil, masuk user_home |
| TC-P-08 | Akses halaman destinasi | Buka `/destinasi` | List destinasi tampil |
| TC-P-09 | Search destinasi valid | Ketik "banjarmasin", submit | Halaman search menampilkan hasil terkait |
| TC-P-10 | Detail destinasi | Klik detail salah satu destinasi | Halaman detail dengan info wisata tampil |
| TC-P-11 | Tambah wishlist (login) | Login → klik tombol wishlist | Item masuk ke `/wishlist` |
| TC-P-12 | Akses wishlist | Buka `/wishlist` setelah login | List wishlist user tampil |
| TC-P-13 | Akses booking | Buka `/booking` setelah login | Halaman booking dapat diakses |
| TC-P-14 | Buka form pembelian | Klik "Pesan" pada destinasi | Form pembelian tampil |
| TC-P-15 | Akses riwayat | Buka `/riwayat` setelah login | Halaman riwayat tampil |
| TC-P-16 | Akses profil | Buka `/profile` | Form profile dengan data user pre-filled |
| TC-P-17 | Update profil valid | Ubah nama, submit | Profil tersimpan, muncul flashdata sukses |
| TC-P-18 | Ganti password valid | Isi password lama+baru valid | Pesan sukses muncul |
| TC-P-19 | Logout | Klik logout | Redirect ke `/auth/login` |
| TC-P-20 | Login ulang setelah logout | Login lagi | Berhasil masuk kembali ke `/user_home` |

### 3.2 Skenario NEGATIF (20)

| ID | Skenario | Langkah | Expected Result |
|----|----------|---------|-----------------|
| TC-N-01 | Login email salah | Email tidak terdaftar | Flash "Email/Username atau password salah" |
| TC-N-02 | Login password salah | Email benar, password salah | Flash "Email/Username atau password salah" |
| TC-N-03 | Login field kosong | Submit tanpa isi apapun | Form tidak ter-submit (HTML5 required) |
| TC-N-04 | Login hanya email | Password kosong | Form tidak ter-submit |
| TC-N-05 | Login hanya password | Email kosong | Form tidak ter-submit |
| TC-N-06 | Register password < 6 karakter | Isi password "abc" | Muncul list error validasi `min_length` |
| TC-N-07 | Register konfirmasi password beda | Konfirmasi tidak sama | Error `matches[password]` |
| TC-N-08 | Register email tidak valid | "abc@" | Browser/validator menolak (`valid_email`) |
| TC-N-09 | Register email sudah terdaftar | Email duplikat | Error `is_unique` |
| TC-N-10 | Register username sudah terdaftar | Username duplikat | Error `is_unique` |
| TC-N-11 | Register nama < 3 karakter | Nama "Aa" | Error `min_length[3]` |
| TC-N-12 | Register umur 0 | Umur = 0 | Error `greater_than[0]` |
| TC-N-13 | Register jenis kelamin kosong | Tidak memilih | Form tidak submit (HTML5 required) |
| TC-N-14 | Register daerah kosong | Tidak memilih | Form tidak submit |
| TC-N-15 | Akses `/user_home` tanpa login | Direct URL | Redirect ke `/auth/login` |
| TC-N-16 | Akses `/wishlist` tanpa login | Direct URL | Redirect ke `/auth/login` |
| TC-N-17 | Akses `/booking` tanpa login | Direct URL | Redirect ke `/auth/login` |
| TC-N-18 | Akses `/riwayat` tanpa login | Direct URL | Redirect ke `/auth/login` |
| TC-N-19 | Akses `/admin/dashboard` sebagai user biasa | Login non-admin | Redirect / forbidden |
| TC-N-20 | Search destinasi karakter aneh | Ketik "!@#$%^" | Halaman search tetap tampil, hasil kosong/normal (tanpa error 500) |

---

## 4. Automation Test (Selenium + Python)

Lihat file [test_banuatour.py](test_banuatour.py) di folder yang sama. Setiap skenario diimplementasikan sebagai **satu function `test_*` terpisah** dalam kelas `unittest.TestCase` agar mudah dijalankan via `unittest` atau `pytest`.

### 4.1 Prasyarat Instalasi

```bash
pip install selenium webdriver-manager pytest
```

### 4.2 Konfigurasi

Edit konstanta di bagian atas `test_banuatour.py`:

```python
BASE_URL = "http://localhost/uas-web-ii/public"   # atau http://uas-web-ii.test
VALID_USER_EMAIL    = "user@test.com"
VALID_USER_PASSWORD = "user12345"
VALID_ADMIN_USERNAME = "admin"
VALID_ADMIN_PASSWORD = "admin12345"
EXISTING_EMAIL    = "user@test.com"   # untuk skenario duplikat
EXISTING_USERNAME = "user"            # untuk skenario duplikat
```

> Pastikan akun di atas sudah ada di tabel `users` database `banuatour`. Jika belum ada, jalankan dulu skenario `TC-P-04` (register) untuk membuat user baru.

### 4.3 Menjalankan Test

```bash
# Jalankan semua test
python -m pytest tests/selenium/test_banuatour.py -v

# Jalankan satu skenario
python -m pytest tests/selenium/test_banuatour.py::BanuaTourTest::test_p05_login_user_valid -v

# Mode unittest klasik
python -m unittest tests.selenium.test_banuatour -v
```

### 4.4 Mapping Skenario → Function

| Skenario | Function | Skenario | Function |
|----------|----------|----------|----------|
| TC-P-01 | `test_p01_open_home` | TC-N-01 | `test_n01_login_wrong_email` |
| TC-P-02 | `test_p02_open_login_page` | TC-N-02 | `test_n02_login_wrong_password` |
| TC-P-03 | `test_p03_open_register_page` | TC-N-03 | `test_n03_login_empty_all` |
| TC-P-04 | `test_p04_register_valid_user` | TC-N-04 | `test_n04_login_only_email` |
| TC-P-05 | `test_p05_login_user_valid` | TC-N-05 | `test_n05_login_only_password` |
| TC-P-06 | `test_p06_login_admin_valid` | TC-N-06 | `test_n06_register_password_too_short` |
| TC-P-07 | `test_p07_login_with_username` | TC-N-07 | `test_n07_register_password_mismatch` |
| TC-P-08 | `test_p08_open_destinasi` | TC-N-08 | `test_n08_register_invalid_email` |
| TC-P-09 | `test_p09_search_destinasi_valid` | TC-N-09 | `test_n09_register_duplicate_email` |
| TC-P-10 | `test_p10_open_destinasi_detail` | TC-N-10 | `test_n10_register_duplicate_username` |
| TC-P-11 | `test_p11_add_wishlist` | TC-N-11 | `test_n11_register_short_name` |
| TC-P-12 | `test_p12_open_wishlist_page` | TC-N-12 | `test_n12_register_age_zero` |
| TC-P-13 | `test_p13_open_booking_page` | TC-N-13 | `test_n13_register_gender_empty` |
| TC-P-14 | `test_p14_open_pembelian_form` | TC-N-14 | `test_n14_register_daerah_empty` |
| TC-P-15 | `test_p15_open_riwayat_page` | TC-N-15 | `test_n15_access_user_home_without_login` |
| TC-P-16 | `test_p16_open_profile_page` | TC-N-16 | `test_n16_access_wishlist_without_login` |
| TC-P-17 | `test_p17_update_profile_valid` | TC-N-17 | `test_n17_access_booking_without_login` |
| TC-P-18 | `test_p18_change_password_valid` | TC-N-18 | `test_n18_access_riwayat_without_login` |
| TC-P-19 | `test_p19_logout` | TC-N-19 | `test_n19_user_access_admin_dashboard` |
| TC-P-20 | `test_p20_relogin_after_logout` | TC-N-20 | `test_n20_search_special_chars` |
