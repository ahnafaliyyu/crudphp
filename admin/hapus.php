<?php
session_start(); // Start session to use $_SESSION['message']
require_once "../config.php";

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
    $gambar_url = "";

    // 1. Get the image URL before deleting
    $sql_select = "SELECT gambar_url FROM products WHERE id = ?";
    if ($stmt_select = mysqli_prepare($koneksi, $sql_select)) {
        mysqli_stmt_bind_param($stmt_select, "i", $id);
        if (mysqli_stmt_execute($stmt_select)) {
            $result = mysqli_stmt_get_result($stmt_select);
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_array($result);
                $gambar_url = $row['gambar_url'];
            }
        }
        mysqli_stmt_close($stmt_select);
    }

    // 2. Delete the product record
    $sql_delete = "DELETE FROM products WHERE id = ?";
    if ($stmt_delete = mysqli_prepare($koneksi, $sql_delete)) {
        mysqli_stmt_bind_param($stmt_delete, "i", $id);
        if (mysqli_stmt_execute($stmt_delete)) {
            // 3. If record deletion is successful, delete the image file
            if (!empty($gambar_url) && file_exists("../" . $gambar_url)) {
                unlink("../" . $gambar_url);
            }
            $_SESSION['message'] = "Produk telah berhasil dihapus.";
        } else {
            $_SESSION['message'] = "Oops! Gagal menghapus produk.";
        }
        mysqli_stmt_close($stmt_delete);
    }
    
    mysqli_close($koneksi);
    header("location: dashboard.php");
    exit();

} else {
    header("location: dashboard.php");
    exit();
}
?>