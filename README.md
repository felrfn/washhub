# WashHub Car Wash Admin & Booking System

## Deskripsi

WashHub adalah sistem manajemen antrian, riwayat, dan pemesanan untuk layanan cuci mobil/motor. Sistem ini berbasis PHP, MySQL, dan berjalan di lingkungan XAMPP (Windows). Admin dapat mengelola antrian, riwayat pesanan, dan pelanggan dapat melakukan booking secara online.

---

## Prasyarat

1. **XAMPP** (PHP, Apache, MySQL)
   - [Download XAMPP](https://www.apachefriends.org/download.html)
2. **phpMyAdmin** (sudah termasuk di XAMPP)
3. **Web Browser** (Chrome, Edge, Firefox, dsb)

---

## Instalasi & Setup

### 1. Clone/Salin Project

- Salin folder `washhub` ke dalam `C:/xampp/htdocs/`

### 2. Setup Database

- Jalankan XAMPP Control Panel, aktifkan **Apache** & **MySQL**
- Buka [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
- **Import database**:
  1.  Buat database baru, misal: `washhub`
  2.  Import file SQL (jika tersedia, misal `washhub.sql` di folder project)
      - Jika file SQL belum ada, buat struktur tabel sesuai kebutuhan aplikasi (lihat file PHP untuk referensi struktur tabel)

### 3. Konfigurasi Koneksi Database

- Buka file konfigurasi koneksi database (biasanya `config.php` atau di bagian atas file PHP utama)
- Pastikan pengaturan berikut sesuai dengan XAMPP default:
  ```php
  $db_host = 'localhost';
  $db_user = 'root';
  $db_pass = '';
  $db_name = 'washhub';
  ```

### 4. Jalankan Aplikasi

- Buka browser, akses: [http://localhost/washhub/](http://localhost/washhub/)
- Untuk admin: [http://localhost/washhub/admin/](http://localhost/washhub/admin/)

---

## Catatan Penting

- **Tidak ada dependensi npm atau composer**: Semua kode JS dan CSS sudah include/manual, tidak perlu `npm install` atau `composer install`.
- **Pastikan port Apache & MySQL tidak bentrok** (default: 80 & 3306)
- **Jika ada error**: Cek konfigurasi database, struktur tabel, dan permission folder.
- **File SQL**: Jika belum ada, minta ke pengembang atau buat manual sesuai kebutuhan aplikasi.

---

## Struktur Folder

- `admin/` : Halaman admin (antrian, riwayat, dsb)
- `booking.php` : Form booking pelanggan
- `eg/` : (Opsional, contoh atau file tambahan)
- `color.txt` : (Opsional, referensi warna)

---

## Pengembang

- Project oleh felrfn
- Untuk pertanyaan, hubungi pengembang atau cek dokumentasi di setiap file PHP

---

## Troubleshooting

- **Blank page/error**: Aktifkan error reporting di PHP (`error_reporting(E_ALL); ini_set('display_errors', 1);`)
- **Database tidak konek**: Pastikan MySQL aktif & konfigurasi benar
- **Akses ditolak**: Cek permission folder/file di Windows

---

## Lisensi

Project ini untuk keperluan internal. Silakan modifikasi sesuai kebutuhan.
