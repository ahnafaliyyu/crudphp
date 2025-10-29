<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { header("location: index.php"); exit; }
if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) { header("location: dashboard.php"); exit(); }

$id = trim($_GET["id"]);
$nama = $deskripsi = $harga = $kategori = $stok = $current_gambar = "";
$nama_err = $deskripsi_err = $harga_err = $kategori_err = $stok_err = $gambar_err = "";

// Ambil data produk dari DB
$sql_fetch = "SELECT * FROM products WHERE id = ?";
if ($stmt_fetch = mysqli_prepare($koneksi, $sql_fetch)) {
    mysqli_stmt_bind_param($stmt_fetch, "i", $id);
    if (mysqli_stmt_execute($stmt_fetch)) {
        $result = mysqli_stmt_get_result($stmt_fetch);
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $nama = $row["nama"];
            $deskripsi = $row["deskripsi"];
            $harga = $row["harga"];
            $kategori = $row["kategori"];
            $stok = $row["stok"];
            $current_gambar = $row["gambar_url"];
        } else { $_SESSION['message'] = "Produk tidak ditemukan."; header("location: dashboard.php"); exit(); }
    } else { echo "Oops! Terjadi kesalahan."; }
    mysqli_stmt_close($stmt_fetch);
}

// Proses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    if(empty(trim($_POST["nama"]))){ $nama_err = "Nama kosong."; } else { $nama = trim($_POST["nama"]); }
    if(empty(trim($_POST["deskripsi"]))){ $deskripsi_err = "Deskripsi kosong."; } else { $deskripsi = trim($_POST["deskripsi"]); }
    if(empty(trim($_POST["harga"]))){ $harga_err = "Harga kosong."; } else { $harga = trim($_POST["harga"]); }
    if(empty(trim($_POST["kategori"]))){ $kategori_err = "Kategori kosong."; } else { $kategori = trim($_POST["kategori"]); }
    if(!isset($_POST["stok"]) || !is_numeric($_POST["stok"]) || $_POST["stok"] < 0){ $stok_err = "Stok tidak valid."; } else { $stok = trim($_POST["stok"]); }

    $gambar_path = $current_gambar;
    if (isset($_FILES["gambar"]) && $_FILES["gambar"]["error"] == UPLOAD_ERR_OK) {
        // Logika upload file
        $target_dir = "../uploads/";
        $file_name = basename($_FILES["gambar"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $unique_file_name = uniqid() . '_' . $file_name;
        $target_file = $target_dir . $unique_file_name;
        if (in_array($file_type, ["jpg", "jpeg", "png", "gif"]) && $_FILES["gambar"]["size"] <= 5000000) {
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                if (!empty($current_gambar) && file_exists("../" . $current_gambar)) { unlink("../" . $current_gambar); }
                $gambar_path = "uploads/" . $unique_file_name;
            } else { $gambar_err = "Error upload file."; }
        } else { $gambar_err = "File tidak valid."; }
    }

    if (empty($nama_err) && empty($deskripsi_err) && empty($harga_err) && empty($kategori_err) && empty($stok_err) && empty($gambar_err)) {
        $sql_update = "UPDATE products SET nama=?, deskripsi=?, harga=?, kategori=?, stok=?, gambar_url=? WHERE id=?";
        if ($stmt_update = mysqli_prepare($koneksi, $sql_update)) {
            // PERBAIKAN: Tipe data yang benar adalah ssdsisi (string, string, double, string, integer, string, integer)
            mysqli_stmt_bind_param($stmt_update, "ssdsisi", $nama, $deskripsi, $harga, $kategori, $stok, $gambar_path, $id);
            if (mysqli_stmt_execute($stmt_update)) {
                $_SESSION['message'] = "Produk berhasil diperbarui.";
                header("location: dashboard.php");
                exit();
            } else { echo "Oops! Gagal memperbarui."; }
            mysqli_stmt_close($stmt_update);
        }
    }
    mysqli_close($koneksi);
}
?>

<!DOCTYPE html>
<html lang="id">
<head><title>Edit Produk</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <header class="admin-header"><h1>Edit Produk</h1></header>
    <main class="container">
        <div class="form-wrapper">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $id); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group"><label>Nama</label><input type="text" name="nama" class="form-control" value="<?php echo $nama; ?>"></div>
                <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" class="form-control"><?php echo $deskripsi; ?></textarea></div>
                <div class="form-group"><label>Harga</label><input type="number" name="harga" class="form-control" value="<?php echo $harga; ?>"></div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" class="form-control">
                        <option value="Pria" <?php echo ($kategori == 'Pria') ? 'selected' : ''; ?>>Pria</option>
                        <option value="Wanita" <?php echo ($kategori == 'Wanita') ? 'selected' : ''; ?>>Wanita</option>
                        <option value="Aksesoris" <?php echo ($kategori == 'Aksesoris') ? 'selected' : ''; ?>>Aksesoris</option>
                    </select>
                </div>
                <div class="form-group"><label>Stok</label><input type="number" name="stok" class="form-control" value="<?php echo $stok; ?>"></div>
                <div class="form-group">
                    <label>Gambar Saat Ini</label><br>
                    <?php if(!empty($current_gambar) && file_exists("../" . $current_gambar)): ?>
                        <img src="../<?php echo htmlspecialchars($current_gambar); ?>" width="150" alt="Gambar saat ini">
                    <?php else: ?><p>Tidak ada gambar.</p><?php endif; ?>
                </div>
                <div class="form-group"><label>Upload Gambar Baru</label><input type="file" name="gambar" class="form-control"><span class="help-block"><?php echo $gambar_err; ?></span></div>
                <input type="submit" class="btn btn-primary" value="Simpan">
                <a href="dashboard.php" class="btn btn-default">Batal</a>
            </form>
        </div>
    </main>
</body>
</html>