<?php
// Memulai session
session_start();

// Memasukkan file konfigurasi database
require_once "../config.php";

// Inisialisasi variabel
$username = $password = "";
$username_err = $password_err = "";

// Memproses data form ketika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Cek apakah username kosong
    if (empty(trim($_POST["username"]))) {
        $username_err = "Silakan masukkan username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Cek apakah password kosong
    if (empty(trim($_POST["password"]))) {
        $password_err = "Silakan masukkan password Anda.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validasi kredensial
    if (empty($username_err) && empty($password_err)) {
        // Siapkan statement select
        $sql = "SELECT id, username, password FROM users WHERE username = ?";

        if ($stmt = mysqli_prepare($koneksi, $sql)) {
            // Bind variabel ke statement yang sudah disiapkan sebagai parameter
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameter
            $param_username = $username;

            // Mencoba mengeksekusi statement yang sudah disiapkan
            if (mysqli_stmt_execute($stmt)) {
                // Simpan hasil
                mysqli_stmt_store_result($stmt);

                // Cek jika username ada, lalu verifikasi password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind hasil variabel
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Password benar, mulai session baru
                            session_start();

                            // Simpan data di variabel session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            // Redirect user ke halaman dashboard
                            header("location: dashboard.php");
                        } else {
                            // Tampilkan pesan error jika password tidak valid
                            $password_err = "Password yang Anda masukkan tidak valid.";
                            echo $password_err;
                        }
                    }
                } else {
                    // Tampilkan pesan error jika username tidak ditemukan
                    $username_err = "Username tidak ditemukan.";
                    echo $username_err;
                }
            } else {
                echo "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
            }

            // Tutup statement
            mysqli_stmt_close($stmt);
        }
    }

    // Tutup koneksi
    mysqli_close($koneksi);
}
?>
