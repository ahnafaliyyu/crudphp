<?php
session_start();
require_once "../config.php";
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

// Pagination and Search logic
$limit = 5; // 5 data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Count total records for pagination ---
$count_sql = "SELECT COUNT(id) AS total FROM products";
$where_clause = [];
$params = [];
$types = '';

if (!empty($search_keyword)) {
    $where_clause[] = "nama LIKE ?";
    $params[] = "%" . $search_keyword . "%";
    $types .= 's';
}

if (!empty($where_clause)) {
    $count_sql .= " WHERE " . implode(' AND ', $where_clause);
}

$count_stmt = mysqli_prepare($koneksi, $count_sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$total_result = mysqli_stmt_get_result($count_stmt);
$total_rows = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rows / $limit);
mysqli_stmt_close($count_stmt);


// --- Fetch records for the current page ---
$sql = "SELECT * FROM products";
if (!empty($where_clause)) {
    $sql .= " WHERE " . implode(' AND ', $where_clause);
}
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

// Re-add limit and offset to params for the main query
$main_params = $params;
$main_params[] = $limit;
$main_params[] = $offset;
$types .= 'ii';

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$main_params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="admin-page">
    <header class="admin-header">
        <h1>Dashboard Admin</h1>
        <nav>
            <a href="dashboard.php">Lihat Produk</a>
            <a href="tambah.php">Tambah Produk</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>
    <main class="container admin-container">
        <?php if(isset($_SESSION['message'])):
        ?>
            <div class="alert-success" style="margin-bottom: 1rem;">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <h2>Manajemen Produk</h2>

        <!-- Search Form -->
        <div class="admin-controls">
            <a href="tambah.php" class="btn btn-primary">+ Tambah Produk Baru</a>
            <form action="dashboard.php" method="get" class="admin-search-form">
                <input type="text" name="search" placeholder="Cari nama produk..." value="<?php echo htmlspecialchars($search_keyword); ?>">
                <button type="submit">Cari</button>
            </form>
        </div>

        <table class="product-table">
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Stok</th>
                    <th>Harga</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0):
                ?>
                    <?php while ($row = mysqli_fetch_array($result)):
                    ?>
                        <tr>
                            <td><img src="../<?php echo htmlspecialchars($row['gambar_url']); ?>" alt="<?php echo htmlspecialchars($row['nama']); ?>"></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><?php echo $row['stok']; ?></td>
                            <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                            <td class="actions">
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                                <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn-hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else:
                ?>
                    <tr><td colspan="6" style="text-align:center;"><em>Tidak ada produk yang cocok.</em></td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination Links -->
        <?php if ($total_pages > 1):
        ?>
            <div class="pagination">
                <?php if ($page > 1):
                ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_keyword); ?>">« Sebelumnya</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++):
                ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_keyword); ?>" class="<?php if ($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages):
                ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_keyword); ?>">Berikutnya »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>