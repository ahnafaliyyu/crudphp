<?php
session_start();
require_once "../config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

$nama = $deskripsi = $harga = $kategori = $stok = "";
$nama_err = $deskripsi_err = $harga_err = $kategori_err = $stok_err = $gambar_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi form
    if(empty(trim($_POST["nama"]))){ $nama_err = "Nama tidak boleh kosong."; } else { $nama = trim($_POST["nama"]); }
    if(empty(trim($_POST["deskripsi"]))){ $deskripsi_err = "Deskripsi tidak boleh kosong."; } else { $deskripsi = trim($_POST["deskripsi"]); }
    if(empty(trim($_POST["harga"]))){ $harga_err = "Harga tidak boleh kosong."; } else { $harga = trim($_POST["harga"]); }
    if(empty(trim($_POST["kategori"]))){ $kategori_err = "Kategori tidak boleh kosong."; } else { $kategori = trim($_POST["kategori"]); }
    if(!isset($_POST["stok"]) || !is_numeric($_POST["stok"]) || $_POST["stok"] < 0){ $stok_err = "Stok harus angka non-negatif."; } else { $stok = trim($_POST["stok"]); }

    // Logika Upload Gambar
    $gambar_path = "";
    if (isset($_FILES["gambar"]) && $_FILES["gambar"]["error"] == 0) {
        $target_dir = "../uploads/";
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        $file_name = basename($_FILES["gambar"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $unique_file_name = uniqid() . '_' . $file_name;
        $target_file = $target_dir . $unique_file_name;
        if (in_array($file_type, $allowed_types) && $_FILES["gambar"]["size"] <= 5000000) {
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar_path = "uploads/" . $unique_file_name;
            } else { $gambar_err = "Error saat upload file."; }
        } else { $gambar_err = "File tidak valid (tipe/ukuran)."; }
    } else { $gambar_err = "Gambar tidak boleh kosong."; }

    if (empty($nama_err) && empty($deskripsi_err) && empty($harga_err) && empty($kategori_err) && empty($stok_err) && empty($gambar_err)) {
        $sql = "INSERT INTO products (nama, deskripsi, harga, kategori, stok, gambar_url) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($koneksi, $sql)) {
            // PERBAIKAN: Tipe data yang benar adalah ssdsis (string, string, double, string, integer, string)
            mysqli_stmt_bind_param($stmt, "ssdsis", $nama, $deskripsi, $harga, $kategori, $stok, $gambar_path);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Produk baru berhasil ditambahkan.";
                header("location: dashboard.php");
                exit();
            } else { echo "Oops! Terjadi kesalahan."; }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($koneksi);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="admin-header"><h1>Tambah Produk Baru</h1></header>
    <main class="container">
        <div class="form-wrapper">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group"><label>Nama</label><input type="text" name="nama" class="form-control" value="<?php echo $nama; ?>"><span class="help-block"><?php echo $nama_err; ?></span></div>
                <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" class="form-control"><?php echo $deskripsi; ?></textarea><span class="help-block"><?php echo $deskripsi_err; ?></span></div>
                <div class="form-group"><label>Harga</label><input type="number" name="harga" class="form-control" value="<?php echo $harga; ?>"><span class="help-block"><?php echo $harga_err; ?></span></div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" class="form-control">
                        <option value="">Pilih</option>
                        <option value="Pria">Pria</option>
                        <option value="Wanita">Wanita</option>
                        <option value="Aksesoris">Aksesoris</option>
                    </select>
                    <span class="help-block"><?php echo $kategori_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" class="form-control" value="<?php echo $stok; ?>">
                    <span class="help-block"><?php echo $stok_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Gambar</label>
                    <input type="file" name="gambar" class="form-control">
                    <span class="help-block"><?php echo $gambar_err; ?></span>
                </div>
                <input type="submit" class="btn btn-primary" value="Submit">
                <a href="dashboard.php" class="btn btn-default">Batal</a>
            </form>
        </div>
    </main>
</body>
</html>