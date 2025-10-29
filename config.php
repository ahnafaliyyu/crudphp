<?php
/*
File: config.php
Deskripsi: Konfigurasi koneksi database.
*/

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

// --- Migrasi Skema Sederhana ---
// Cek dan tambahkan kolom yang mungkin hilang dari versi sebelumnya

$columns_to_check = [
    'kategori' => 'VARCHAR(50)',
    'stok' => 'INT(11) DEFAULT 0',
    'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
];

foreach ($columns_to_check as $column => $definition) {
    $check_column_sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '{$db_name}' AND TABLE_NAME = 'products' AND COLUMN_NAME = '{$column}'";
    $result = mysqli_query($koneksi, $check_column_sql);
    if ($result && mysqli_num_rows($result) == 0) {
        $alter_sql = "ALTER TABLE products ADD COLUMN `{$column}` {$definition}";
        mysqli_query($koneksi, $alter_sql);
    }
}


// Membuat tabel jika belum ada (untuk kemudahan setup)
// Tabel untuk admin
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)";
mysqli_query($koneksi, $sql_users);

// Tabel untuk produk
$sql_products = "CREATE TABLE IF NOT EXISTS products (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10, 2) NOT NULL,
    kategori VARCHAR(50),
    stok INT(11) DEFAULT 0,
    gambar_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($koneksi, $sql_products);

// Menambahkan admin default jika belum ada (username: admin, password: password123)
$check_admin = "SELECT id FROM users WHERE username = 'admin'";
$result = mysqli_query($koneksi, $check_admin);
if ($result && mysqli_num_rows($result) == 0) {
    $hashed_password = password_hash('password123', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO users (username, password) VALUES ('admin', '$hashed_password')";
    mysqli_query($koneksi, $insert_admin);
}
?>