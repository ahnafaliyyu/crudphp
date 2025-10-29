# Toko Online Sederhana (PHP Native)

Aplikasi e-commerce sederhana yang dibangun dengan PHP native dan database MySQL. Aplikasi ini memungkinkan admin untuk mengelola produk dan pelanggan untuk melihat, menambahkan ke keranjang, serta membeli produk.

## Fitur yang Tersedia

### Panel Admin
- **Login & Logout**: Sistem otentikasi untuk admin.
- **Dashboard**: Menampilkan semua produk dalam bentuk tabel.
- **CRUD Produk**: Tambah, lihat, edit, dan hapus data produk.
- **Pencarian**: Mencari produk berdasarkan nama.
- **Paginasi**: Pembagian halaman untuk daftar produk.
- **Upload Gambar**: Mengunggah gambar untuk setiap produk.

### Halaman Pelanggan
- **Galeri Produk**: Menampilkan semua produk yang tersedia.
- **Keranjang Belanja**: Menambahkan produk ke keranjang, melihat isi keranjang, dan memperbarui jumlah item.
- **Proses Beli**: Melakukan "pembelian" sederhana dari item di keranjang.

## Kebutuhan Sistem
- Web Server (direkomendasikan XAMPP)
  - Apache
  - PHP 
  - MySQL/MariaDB
- Web Browser (Chrome, Firefox, dll.)

## Cara Instalasi dan Konfigurasi
1.  **Clone atau Download**: Unduh atau clone repositori ini ke dalam direktori `htdocs` pada instalasi XAMPP Anda (contoh: `c:\xampp\htdocs\web php`).
2.  **Jalankan XAMPP**: Buka XAMPP Control Panel dan jalankan service **Apache** dan **MySQL**.
3.  **Buat Database**: Buka phpMyAdmin (`http://localhost/phpmyadmin`) dan buat database baru dengan nama `db_sepatu_vintage`.
4.  **Setup Otomatis**: Aplikasi ini memiliki skrip setup otomatis di dalam `config.php`. Cukup akses halaman utama (`http://localhost/web php/`) dan tabel `users` serta `products` akan dibuat secara otomatis.
5.  **Login Admin**:
    - Akses halaman login admin di `http://localhost/web php/admin/`.
    - Gunakan kredensial default:
      - **Username**: `admin`
      - **Password**: `password123`
6.  **Selesai**: Aplikasi siap digunakan.

## Contoh Environment Config
Konfigurasi koneksi database terletak di `config.php`. Pastikan pengaturannya sesuai dengan environment Anda.

```php
<?php
// Pengaturan Database
$db_host = 'localhost'; // Biasanya 'localhost'
$db_user = 'root';      // User database Anda
$db_pass = '';          // Password database Anda
$db_name = 'db_sepatu_vintage'; // Nama database Anda

// Membuat koneksi
$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
```

## Struktur Folder
```
c:\xampp\htdocs\web php\
├───add_to_cart.php
├───beli.php
├───cart.php
├───config.php
├───index.php
├───navbar.php
├───style.css
├───update_cart.php
├───admin\
│   ├───dashboard.php
│   ├───edit.php
│   ├───hapus.php
│   ├───index.php
│   ├───login_process.php
│   ├───logout.php
│   └───tambah.php
└───uploads\
    └───... (berisi gambar produk)
```

## Screenshot Aplikasi

![Tampilan Halaman Utama](uploads/6900915814dec_Screenshot%202025-10-28%20154005.png)

```
