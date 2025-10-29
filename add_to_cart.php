<?php
session_start();
require_once 'config.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if product ID is provided
if (isset($_POST['product_id']) && is_numeric($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) && is_numeric($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Check if product exists in the database
    $stmt = $koneksi->prepare("SELECT id, stok FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Check stock
        if ($product['stok'] > 0) {
            // If cart already has the product, update quantity. Otherwise, add it.
            if (isset($_SESSION['cart'][$product_id])) {
                // Ensure we don't add more than is in stock
                $new_quantity = $_SESSION['cart'][$product_id] + $quantity;
                if ($new_quantity <= $product['stok']) {
                    $_SESSION['cart'][$product_id] = $new_quantity;
                    $_SESSION['message'] = "Jumlah produk di keranjang diperbarui.";
                } else {
                    $_SESSION['message'] = "Stok tidak mencukupi.";
                }
            } else {
                if ($quantity <= $product['stok']) {
                    $_SESSION['cart'][$product_id] = $quantity;
                    $_SESSION['message'] = "Produk ditambahkan ke keranjang.";
                } else {
                    $_SESSION['message'] = "Stok tidak mencukupi.";
                }
            }
        } else {
            $_SESSION['message'] = "Stok produk habis.";
        }
    } else {
        $_SESSION['message'] = "Produk tidak ditemukan.";
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "ID Produk tidak valid.";
}

// Redirect back to the previous page or to the cart
header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'index.php');
exit();
?>