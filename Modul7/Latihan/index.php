<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Index</title>
</head>
<body>
    <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    <p>Kamu berhasil login.</p>
    <a href="logout.php">Logout</a>
</body>
</html>
