<?php
include "koneksi.php";

$data_barang  = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY id ASC");
$data_kurir   = mysqli_query($koneksi, "SELECT * FROM kurir ORDER BY id ASC");
$data_pembeli = mysqli_query($koneksi, "SELECT * FROM pembeli ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendataan COD Barang</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            width: fit-content;
            max-width: 100%;
            margin: 20px auto;
            padding: 16px 20px;
        }
        .tabel-wrapper {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }
        .tabel-wrapper table {
            width: auto;
            border-collapse: collapse;
        }
        .tabel-wrapper th, .tabel-wrapper td {
            border: 1px solid #000;
            padding: 4px 10px;
            text-align: left;
        }
    </style>
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
    <div class="tabel-wrapper">
        <table>
            <tr>
                <th>No</th>
                <th>ID Barang</th>
            </tr>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($data_barang)) { ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo sprintf("BR%02d", $row["id"]); ?></td>
            </tr>
            <?php } ?>
        </table>

        <table>
            <tr>
                <th>No</th>
                <th>ID Kurir</th>
            </tr>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($data_kurir)) { ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo sprintf("KR%02d", $row["id"]); ?></td>
            </tr>
            <?php } ?>
        </table>

        <table>
            <tr>
                <th>No</th>
                <th>ID Pembeli</th>
            </tr>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($data_pembeli)) { ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo sprintf("PI%02d", $row["id"]); ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>