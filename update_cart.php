<?php
session_start();
require_once 'config.php';

// Initialize buy_later if it doesn't exist
if (!isset($_SESSION['buy_later'])) {
    $_SESSION['buy_later'] = [];
}

// Update quantity
if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity > 0 && isset($_SESSION['cart'][$product_id])) {
        // Check against stock
        $stmt = $koneksi->prepare("SELECT stok FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            if ($quantity <= $product['stok']) {
                $_SESSION['cart'][$product_id] = $quantity;
                $_SESSION['message'] = "Kuantitas diperbarui.";
            } else {
                $_SESSION['message'] = "Stok tidak mencukupi untuk jumlah yang diminta.";
            }
        }
        $stmt->close();
    } else {
        // If quantity is 0 or less, remove the item
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['message'] = "Produk dihapus dari keranjang.";
    }
}

// Remove item from cart
if (isset($_POST['remove_product_id'])) {
    $product_id = (int)$_POST['remove_product_id'];
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['message'] = "Produk dihapus dari keranjang.";
    }
}

// Move item from cart to buy later
if (isset($_POST['move_to_later_id'])) {
    $product_id = (int)$_POST['move_to_later_id'];
    if (isset($_SESSION['cart'][$product_id])) {
        $quantity = $_SESSION['cart'][$product_id];
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['buy_later'][$product_id] = $quantity;
        $_SESSION['message'] = "Produk dipindahkan ke daftar Beli Nanti.";
    }
}

// Move item from buy later to cart
if (isset($_POST['move_to_cart_id'])) {
    $product_id = (int)$_POST['move_to_cart_id'];
    if (isset($_SESSION['buy_later'][$product_id])) {
        $quantity = $_SESSION['buy_later'][$product_id];
        unset($_SESSION['buy_later'][$product_id]);
        $_SESSION['cart'][$product_id] = $quantity;
        $_SESSION['message'] = "Produk dipindahkan ke Keranjang.";
    }
}

header('Location: cart.php');
exit();
?>