<?php
include "koneksi.php";

if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET["id"];
$result = mysqli_query($koneksi, "SELECT * FROM posts WHERE id = $id");
$post = mysqli_fetch_assoc($result);

if (!$post) {
    header("Location: index.php");
    exit;
}

if (isset($_POST["update"])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST["judul"]);
    $konten = mysqli_real_escape_string($koneksi, $_POST["konten"]);

    mysqli_query($koneksi, "UPDATE posts SET judul = '$judul', konten = '$konten' WHERE id = $id");
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Post Berita!</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Edit Post Berita!</h2>
    <a href="index.php">Batal</a>
    <form method="post">
        <label for="judul">Judul</label>
        <input type="text" id="judul" name="judul" maxlength="100" value="<?php echo htmlspecialchars($post["judul"]); ?>" required>

        <label for="konten">Konten</label>
        <textarea id="konten" name="konten" maxlength="1000" required><?php echo htmlspecialchars($post["konten"]); ?></textarea>

        <button type="submit" name="update">Update</button>
    </form>
</div>
</body>
</html>