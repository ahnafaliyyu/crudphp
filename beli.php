<?php
session_start();
require_once 'config.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

$cart = $_SESSION['cart'];
$error_message = "";
$success = true;

// Start a transaction
mysqli_begin_transaction($koneksi);

try {
    $sql_update = "UPDATE products SET stok = stok - ? WHERE id = ? AND stok >= ?";
    $stmt = mysqli_prepare($koneksi, $sql_update);

    foreach ($cart as $product_id => $quantity) {
        mysqli_stmt_bind_param($stmt, "iii", $quantity, $product_id, $quantity);
        mysqli_stmt_execute($stmt);
        
        // Check if the update was successful (affected rows > 0)
        if (mysqli_stmt_affected_rows($stmt) == 0) {
            // This means stock was insufficient or product ID was invalid
            throw new Exception("Stok untuk produk ID #{$product_id} tidak mencukupi atau produk tidak ditemukan.");
        }
    }

    // If all updates were successful, commit the transaction
    mysqli_commit($koneksi);
    
    // Clear the cart
    unset($_SESSION['cart']);

} catch (Exception $e) {
    // If any error occurred, roll back the transaction
    mysqli_rollback($koneksi);
    $success = false;
    $error_message = $e->getMessage();
    // Set message to be displayed on the cart page
    $_SESSION['message'] = "Terjadi kesalahan saat memproses pembelian: " . $error_message;
}

mysqli_close($koneksi);

// If purchase failed, redirect back to cart to show the error
if (!$success) {
    header('Location: cart.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Status Pembelian</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container" style="text-align: center; padding-top: 4rem;">
        <div class="hero-content">
            <h1>Terima Kasih!</h1>
            <p>Pembelian Anda telah berhasil diproses.</p>
            <a href="index.php" class="btn">Lanjutkan Belanja</a>
        </div>
    </main>
</body>
</html>