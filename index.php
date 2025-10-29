<?php 
require_once "config.php"; 
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// --- Pagination & Filter Logic ---
$limit = 9; // 9 products per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// --- Build WHERE clause and parameters for filtering ---
$page_title = 'Semua Produk';
$where_clause = "stok > 0";
$params = [];
$types = '';
$query_string = []; // For pagination links

// Filter by category
if (isset($_GET['kategori']) && in_array($_GET['kategori'], ['Pria', 'Wanita', 'Aksesoris'])) {
    $kategori_filter = $_GET['kategori'];
    $page_title = $kategori_filter;
    $where_clause .= " AND kategori = ?";
    $params[] = $kategori_filter;
    $types .= 's';
    $query_string['kategori'] = $kategori_filter;
}

// Filter by search
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = '%' . trim($_GET['search']) . '%';
    $page_title = 'Hasil Pencarian untuk "' . htmlspecialchars(trim($_GET['search'])) . '"';
    $where_clause .= " AND nama LIKE ?";
    $params[] = $search_term;
    $types .= 's';
    $query_string['search'] = trim($_GET['search']);
}

// --- Count total records for pagination ---
$count_sql = "SELECT COUNT(id) AS total FROM products WHERE " . $where_clause;
$count_stmt = mysqli_prepare($koneksi, $count_sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$total_rows = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];
$total_pages = ceil($total_rows / $limit);
mysqli_stmt_close($count_stmt);

// --- Fetch records for the current page ---
$sql = "SELECT * FROM products WHERE " . $where_clause . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Sepatu Vintage</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lora:ital,wght@0,400;0,700;1,400&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <header class="hero-section">
        <div class="hero-content">
            <h1>Koleksi Musim Gugur</h1>
            <p>Temukan gaya klasik yang tak lekang oleh waktu.</p>
            <a href="#produk" class="btn">Lihat Koleksi</a>
        </div>
    </header>

    <main id="produk" class="container">
        <?php if(isset($_SESSION['message'])):
        ?>
            <div class="alert-success" style="margin-bottom: 2rem; text-align: center; padding: 1rem;">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <h2 class='section-title'><?php echo htmlspecialchars($page_title); ?></h2>

        <div class="product-grid">
            <?php if (mysqli_num_rows($result) > 0):
            ?>
                <?php while ($row = mysqli_fetch_array($result)):
                ?>
                    <div class='product-card' data-id='<?php echo $row['id']; ?>' data-nama='<?php echo htmlspecialchars($row['nama']); ?>' data-harga='Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>' data-deskripsi='<?php echo htmlspecialchars($row['deskripsi']); ?>' data-gambar='<?php echo htmlspecialchars($row['gambar_url']); ?>' data-stok='<?php echo $row['stok']; ?>'>
                        <div class='product-image-container'>
                            <img src='<?php echo htmlspecialchars($row['gambar_url']); ?>' alt='<?php echo htmlspecialchars($row['nama']); ?>'>
                            <span class='stock-badge'>Stok: <?php echo $row['stok']; ?></span>
                        </div>
                        <div class='product-info'>
                            <h3 class='product-name'><?php echo htmlspecialchars($row['nama']); ?></h3>
                            <p class='product-price'>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else:
            ?>
                <p class='text-center' style='grid-column: 1 / -1;'><em>Tidak ada produk yang cocok dengan kriteria Anda.</em></p>
            <?php endif; ?>
        </div>

        <!-- Pagination Links -->
        <?php if ($total_pages > 1):
        ?>
            <div class="pagination">
                <?php
                $http_query = http_build_query($query_string);
                if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&<?php echo $http_query; ?>">« Sebelumnya</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?>&<?php echo $http_query; ?>" class="<?php if ($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages):
                ?>
                    <a href="?page=<?php echo $page + 1; ?>&<?php echo $http_query; ?>">Berikutnya »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>

    <footer class="site-footer">
        <!-- Footer -->
    </footer>

    <div id="productModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <img src="" alt="Product Image" id="modalImage" class="modal-image">
            <div class="modal-info">
                <h2 id="modalName"></h2>
                <p id="modalPrice"></p>
                <p id="modalDescription"></p>
                <p id="modalStok"></p>
                <form action="add_to_cart.php" method="post">
                    <input type="hidden" name="product_id" id="modalProductId">
                    <div class="form-group">
                        <label for="quantity">Jumlah:</label>
                        <input type="number" name="quantity" value="1" min="1" id="modalQuantity" class="quantity-input">
                    </div>
                    <button type="submit" class="btn">Tambah ke Keranjang</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('productModal');
        const modalProductId = document.getElementById('modalProductId');
        const modalQuantity = document.getElementById('modalQuantity');

        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function() {
                document.getElementById('modalImage').src = this.dataset.gambar;
                document.getElementById('modalName').textContent = this.dataset.nama;
                document.getElementById('modalPrice').textContent = this.dataset.harga;
                document.getElementById('modalDescription').textContent = this.dataset.deskripsi;
                document.getElementById('modalStok').textContent = 'Sisa Stok: ' + this.dataset.stok;
                modalProductId.value = this.dataset.id;
                modalQuantity.max = this.dataset.stok; // Set max quantity based on stock
                modal.style.display = 'flex';
            });
        });
        document.querySelector('.modal-close').addEventListener('click', () => modal.style.display = 'none');
        modal.addEventListener('click', (e) => { if (e.target === modal) { modal.style.display = 'none'; } });
    });
    </script>

</body>
</html>