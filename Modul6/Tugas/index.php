<?php
include "koneksi.php";

/*
    Halaman index menampilkan nama barang, pembeli, dan kurir
    yang mempunyai ID sama. Ketiga tabel memakai ID sendiri.
*/
$query = mysqli_query($koneksi, "
    SELECT barang.id, barang.nama AS nama_barang,
           pembeli.nama AS nama_pembeli,
           kurir.nama AS nama_kurir
    FROM barang
    INNER JOIN pembeli ON barang.id = pembeli.id
    INNER JOIN kurir ON barang.id = kurir.id
    ORDER BY barang.id ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendataan COD Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Pendataan COD Barang</h2>

    <nav>
        <a href="barang.php">Data Barang</a>
        <a href="pembeli.php">Data Pembeli</a>
        <a href="kurir.php">Data Kurir</a>
    </nav>

    <h3>Daftar COD</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Nama Barang</th>
            <th>Nama Pembeli</th>
            <th>Nama Kurir</th>
        </tr>
        <?php while ($data = mysqli_fetch_assoc($query)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($data["id"]); ?></td>
            <td><?php echo htmlspecialchars($data["nama_barang"]); ?></td>
            <td><?php echo htmlspecialchars($data["nama_pembeli"]); ?></td>
            <td><?php echo htmlspecialchars($data["nama_kurir"]); ?></td>
        </tr>
        <?php } ?>
    </table>
    <?php if (mysqli_num_rows($query) == 0) { ?>
        <p>Belum ada data COD dengan ID yang sama di ketiga tabel.</p>
    <?php } ?>
</div>
</body>
</html>