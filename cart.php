<?php
session_start();
require_once 'config.php';

// Function to fetch product details from an array of IDs
function get_products_by_ids($koneksi, $ids) {
    if (empty($ids)) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT id, nama, harga, gambar_url, stok FROM products WHERE id IN ($placeholders)";
    $stmt = $koneksi->prepare($sql);
    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }
    $stmt->close();
    return $products;
}

// --- Cart Items ---
$cart_items = [];
$total_price = 0;
if (!empty($_SESSION['cart'])) {
    $cart_product_ids = array_keys($_SESSION['cart']);
    $cart_products = get_products_by_ids($koneksi, $cart_product_ids);
    
    foreach ($cart_products as $product) {
        $product_id = $product['id'];
        $quantity = $_SESSION['cart'][$product_id];
        $subtotal = $product['harga'] * $quantity;
        $total_price += $subtotal;
        
        $cart_items[] = [
            'id' => $product_id,
            'nama' => $product['nama'],
            'harga' => $product['harga'],
            'gambar_url' => $product['gambar_url'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}

// --- Buy Later Items ---
$buy_later_items = [];
if (!empty($_SESSION['buy_later'])) {
    $buy_later_product_ids = array_keys($_SESSION['buy_later']);
    $buy_later_products = get_products_by_ids($koneksi, $buy_later_product_ids);

    foreach ($buy_later_products as $product) {
        $buy_later_items[] = $product;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container">
        <h1>Keranjang Belanja Anda</h1>

        <?php 
        if (isset($_SESSION['message'])) {
            echo '<p class="alert-success">' . $_SESSION['message'] . '</p>';
            unset($_SESSION['message']);
        }
        ?>

        <?php if (!empty($cart_items)):
        ?>
            <div class="cart-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th colspan="2">Produk</th>
                            <th>Harga</th>
                            <th>Kuantitas</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($item['gambar_url']); ?>" alt="<?php echo htmlspecialchars($item['nama']); ?>" width="100"></td>
                                <td><?php echo htmlspecialchars($item['nama']); ?></td>
                                <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <form action="update_cart.php" method="post" class="update-form">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input">
                                        <button type="submit" class="btn-update">Update</button>
                                    </form>
                                </td>
                                <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                <td class="cart-actions">
                                    <form action="update_cart.php" method="post">
                                        <input type="hidden" name="remove_product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn-remove">Hapus</button>
                                    </form>
                                    <form action="update_cart.php" method="post">
                                        <input type="hidden" name="move_to_later_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn-secondary">Simpan Nanti</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-summary">
                    <h3>Total: Rp <?php echo number_format($total_price, 0, ',', '.'); ?></h3>
                    <a href="beli.php" class="btn btn-primary">Lanjutkan ke Pembelian</a>
                </div>
            </div>
        <?php else: ?>
            <p>Keranjang belanja Anda kosong. <a href="index.php">Mulai belanja</a>.</p>
        <?php endif; ?>

        <?php if (!empty($buy_later_items)):
        ?>
            <div class="buy-later-wrapper">
                <h2>Disimpan untuk Nanti</h2>
                <div class="product-grid">
                    <?php foreach ($buy_later_items as $item): ?>
                        <div class="product-card">
                            <div class="product-image-container">
                                <img src="<?php echo htmlspecialchars($item['gambar_url']); ?>" alt="<?php echo htmlspecialchars($item['nama']); ?>">
                                <span class="stock-badge">Stok: <?php echo $item['stok']; ?></span>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($item['nama']); ?></h3>
                                <p class="product-price">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></p>
                                <form action="update_cart.php" method="post">
                                    <input type="hidden" name="move_to_cart_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn">Pindah ke Keranjang</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>