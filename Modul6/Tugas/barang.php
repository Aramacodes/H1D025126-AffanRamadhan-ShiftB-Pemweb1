<?php
include "koneksi.php";

if (isset($_POST["tambah"])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST["nama"]);
    $harga = (int) $_POST["harga"];
    mysqli_query($koneksi, "INSERT INTO barang (nama, harga) VALUES ('$nama', $harga)");
    header("Location: barang.php");
    exit;
}

if (isset($_POST["update"])) {
    $id = (int) $_POST["id"];
    $nama = mysqli_real_escape_string($koneksi, $_POST["nama"]);
    $harga = (int) $_POST["harga"];
    mysqli_query($koneksi, "UPDATE barang SET nama='$nama', harga=$harga WHERE id=$id");
    header("Location: barang.php");
    exit;
}

if (isset($_GET["hapus"]) && ctype_digit($_GET["hapus"])) {
    $id = (int) $_GET["hapus"];
    mysqli_query($koneksi, "DELETE FROM barang WHERE id=$id");
    header("Location: barang.php");
    exit;
}

$edit = null;
if (isset($_GET["edit"]) && ctype_digit($_GET["edit"])) {
    $id = (int) $_GET["edit"];
    $hasil = mysqli_query($koneksi, "SELECT * FROM barang WHERE id=$id");
    $edit = mysqli_fetch_assoc($hasil);
}

$data_barang = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>CRUD Data Barang</h2>
    <nav><a href="index.php">Halaman Index</a> <a href="pembeli.php">Data Pembeli</a> <a href="kurir.php">Data Kurir</a></nav>

    <h3><?php echo $edit ? "Edit Barang" : "Tambah Barang"; ?></h3>
    <form method="post" class="form">
        <?php if ($edit) { ?><input type="hidden" name="id" value="<?php echo $edit["id"]; ?>"><?php } ?>
        <label>Nama Barang</label>
        <input type="text" name="nama" maxlength="100" value="<?php echo $edit ? htmlspecialchars($edit["nama"]) : ""; ?>" required>
        <label>Harga</label>
        <input type="number" name="harga" min="0" value="<?php echo $edit ? htmlspecialchars($edit["harga"]) : ""; ?>" required>
        <button type="submit" name="<?php echo $edit ? "update" : "tambah"; ?>"><?php echo $edit ? "Update" : "Tambah"; ?></button>
        <?php if ($edit) { ?><a href="barang.php">Batal</a><?php } ?>
    </form>

    <h3>Daftar Barang</h3>
    <table>
        <tr><th>ID</th><th>Nama Barang</th><th>Harga</th><th>Aksi</th></tr>
        <?php while ($row = mysqli_fetch_assoc($data_barang)) { ?>
        <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo htmlspecialchars($row["nama"]); ?></td>
            <td>Rp <?php echo number_format($row["harga"], 0, ",", "."); ?></td>
            <td><a href="barang.php?edit=<?php echo $row["id"]; ?>">Edit</a> | <a href="barang.php?hapus=<?php echo $row["id"]; ?>" onclick="return confirm('Hapus barang ini?')">Delete</a></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>