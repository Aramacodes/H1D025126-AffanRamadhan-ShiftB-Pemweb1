<?php
include "koneksi.php";

if (isset($_POST["simpan"])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST["judul"]);
    $konten = mysqli_real_escape_string($koneksi, $_POST["konten"]);

    mysqli_query($koneksi, "INSERT INTO posts (judul, konten) VALUES ('$judul', '$konten')");
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Post Berita!</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Tambah Post Berita!</h2>
    <a href="index.php">Batal</a>
    <form method="post">
        <label for="judul">Judul</label>
        <input type="text" id="judul" name="judul" maxlength="100" required>

        <label for="konten">Konten</label>
        <textarea id="konten" name="konten" maxlength="1000" required></textarea>

        <button type="submit" name="simpan">Tambah</button>
    </form>
</div>
</body>
</html>