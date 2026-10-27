<?php
include "koneksi.php";

if (isset($_POST["tambah"])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST["nama"]);
    $hp = mysqli_real_escape_string($koneksi, $_POST["hp"]);
    $alamat = mysqli_real_escape_string($koneksi, $_POST["alamat"]);
    mysqli_query($koneksi, "INSERT INTO pembeli (nama, hp, alamat) VALUES ('$nama', '$hp', '$alamat')");
    header("Location: pembeli.php");
    exit;
}

if (isset($_POST["update"])) {
    $id = (int) $_POST["id"];
    $nama = mysqli_real_escape_string($koneksi, $_POST["nama"]);
    $hp = mysqli_real_escape_string($koneksi, $_POST["hp"]);
    $alamat = mysqli_real_escape_string($koneksi, $_POST["alamat"]);
    mysqli_query($koneksi, "UPDATE pembeli SET nama='$nama', hp='$hp', alamat='$alamat' WHERE id=$id");
    header("Location: pembeli.php");
    exit;
}

if (isset($_GET["hapus"]) && ctype_digit($_GET["hapus"])) {
    $id = (int) $_GET["hapus"];
    mysqli_query($koneksi, "DELETE FROM pembeli WHERE id=$id");
    header("Location: pembeli.php");
    exit;
}

$edit = null;
if (isset($_GET["edit"]) && ctype_digit($_GET["edit"])) {
    $id = (int) $_GET["edit"];
    $hasil = mysqli_query($koneksi, "SELECT * FROM pembeli WHERE id=$id");
    $edit = mysqli_fetch_assoc($hasil);
}
$data_pembeli = mysqli_query($koneksi, "SELECT * FROM pembeli ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pembeli</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>CRUD Data Pembeli</h2>
    <nav><a href="index.php">Halaman Index</a> <a href="barang.php">Data Barang</a> <a href="kurir.php">Data Kurir</a></nav>

    <h3><?php echo $edit ? "Edit Pembeli" : "Tambah Pembeli"; ?></h3>
    <form method="post" class="form">
        <?php if ($edit) { ?><input type="hidden" name="id" value="<?php echo $edit["id"]; ?>"><?php } ?>
        <label>Nama Pembeli</label>
        <input type="text" name="nama" maxlength="100" value="<?php echo $edit ? htmlspecialchars($edit["nama"]) : ""; ?>" required>
        <label>Nomor HP</label>
        <input type="text" name="hp" maxlength="20" value="<?php echo $edit ? htmlspecialchars($edit["hp"]) : ""; ?>" required>
        <label>Alamat</label>
        <textarea name="alamat" maxlength="255" required><?php echo $edit ? htmlspecialchars($edit["alamat"]) : ""; ?></textarea>
        <button type="submit" name="<?php echo $edit ? "update" : "tambah"; ?>"><?php echo $edit ? "Update" : "Tambah"; ?></button>
        <?php if ($edit) { ?><a href="pembeli.php">Batal</a><?php } ?>
    </form>

    <h3>Daftar Pembeli</h3>
    <table>
        <tr><th>ID</th><th>Nama</th><th>Nomor HP</th><th>Alamat</th><th>Aksi</th></tr>
        <?php while ($row = mysqli_fetch_assoc($data_pembeli)) { ?>
        <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo htmlspecialchars($row["nama"]); ?></td>
            <td><?php echo htmlspecialchars($row["hp"]); ?></td>
            <td><?php echo htmlspecialchars($row["alamat"]); ?></td>
            <td><a href="pembeli.php?edit=<?php echo $row["id"]; ?>">Edit</a> | <a href="pembeli.php?hapus=<?php echo $row["id"]; ?>" onclick="return confirm('Hapus pembeli ini?')">Delete</a></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>