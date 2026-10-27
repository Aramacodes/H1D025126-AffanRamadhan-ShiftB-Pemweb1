<?php
include "koneksi.php";

if (isset($_POST["tambah"])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST["nama"]);
    $hp = mysqli_real_escape_string($koneksi, $_POST["hp"]);
    mysqli_query($koneksi, "INSERT INTO kurir (nama, hp) VALUES ('$nama', '$hp')");
    header("Location: kurir.php");
    exit;
}

if (isset($_POST["update"])) {
    $id = (int) $_POST["id"];
    $nama = mysqli_real_escape_string($koneksi, $_POST["nama"]);
    $hp = mysqli_real_escape_string($koneksi, $_POST["hp"]);
    mysqli_query($koneksi, "UPDATE kurir SET nama='$nama', hp='$hp' WHERE id=$id");
    header("Location: kurir.php");
    exit;
}

if (isset($_GET["hapus"]) && ctype_digit($_GET["hapus"])) {
    $id = (int) $_GET["hapus"];
    mysqli_query($koneksi, "DELETE FROM kurir WHERE id=$id");
    header("Location: kurir.php");
    exit;
}

$edit = null;
if (isset($_GET["edit"]) && ctype_digit($_GET["edit"])) {
    $id = (int) $_GET["edit"];
    $hasil = mysqli_query($koneksi, "SELECT * FROM kurir WHERE id=$id");
    $edit = mysqli_fetch_assoc($hasil);
}
$data_kurir = mysqli_query($koneksi, "SELECT * FROM kurir ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kurir</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>CRUD Data Kurir</h2>
    <nav><a href="index.php">Halaman Index</a> <a href="barang.php">Data Barang</a> <a href="pembeli.php">Data Pembeli</a></nav>

    <h3><?php echo $edit ? "Edit Kurir" : "Tambah Kurir"; ?></h3>
    <form method="post" class="form">
        <?php if ($edit) { ?><input type="hidden" name="id" value="<?php echo $edit["id"]; ?>"><?php } ?>
        <label>Nama Kurir</label>
        <input type="text" name="nama" maxlength="100" value="<?php echo $edit ? htmlspecialchars($edit["nama"]) : ""; ?>" required>
        <label>Nomor HP</label>
        <input type="text" name="hp" maxlength="20" value="<?php echo $edit ? htmlspecialchars($edit["hp"]) : ""; ?>" required>
        <button type="submit" name="<?php echo $edit ? "update" : "tambah"; ?>"><?php echo $edit ? "Update" : "Tambah"; ?></button>
        <?php if ($edit) { ?><a href="kurir.php">Batal</a><?php } ?>
    </form>

    <h3>Daftar Kurir</h3>
    <table>
        <tr><th>ID</th><th>Nama</th><th>Nomor HP</th><th>Aksi</th></tr>
        <?php while ($row = mysqli_fetch_assoc($data_kurir)) { ?>
        <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo htmlspecialchars($row["nama"]); ?></td>
            <td><?php echo htmlspecialchars($row["hp"]); ?></td>
            <td><a href="kurir.php?edit=<?php echo $row["id"]; ?>">Edit</a> | <a href="kurir.php?hapus=<?php echo $row["id"]; ?>" onclick="return confirm('Hapus kurir ini?')">Delete</a></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>