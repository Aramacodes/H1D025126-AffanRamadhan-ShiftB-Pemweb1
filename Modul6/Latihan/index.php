<?php
include "koneksi.php";
$query = mysqli_query($koneksi, "SELECT * FROM posts ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Post News Ter-Update!</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Post News Ter-Update!</h2>
    <a class="tambah" href="tambah.php">Tambah Post</a>

    <?php while ($post = mysqli_fetch_assoc($query)) { ?>
        <article>
            <h3><?php echo htmlspecialchars($post["id"]); ?>. <?php echo htmlspecialchars($post["judul"]); ?></h3>
            <div class="aksi">
                <a href="edit.php?id=<?php echo $post["id"]; ?>">Edit</a> |
                <form action="hapus.php" method="post" onsubmit="return confirm('Yakin ingin menghapus post ini?');">
                    <input type="hidden" name="id" value="<?php echo $post["id"]; ?>">
                    <button type="submit" class="link-button">Delete</button>
                </form>
            </div>
            <div class="konten">
                <?php
                $paragraf = explode("\n", $post["konten"]);
                foreach ($paragraf as $teks) {
                    if (trim($teks) !== "") {
                        echo "<p>" . nl2br(htmlspecialchars(trim($teks))) . "</p>";
                    }
                }
                ?>
            </div>
        </article>
    <?php } ?>
</div>
</body>
</html>