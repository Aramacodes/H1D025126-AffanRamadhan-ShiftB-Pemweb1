<?php
include "koneksi.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"]) && ctype_digit($_POST["id"])) {
    $id = (int) $_POST["id"];
    mysqli_query($koneksi, "DELETE FROM posts WHERE id = $id");
}

header("Location: index.php");
exit;
?>