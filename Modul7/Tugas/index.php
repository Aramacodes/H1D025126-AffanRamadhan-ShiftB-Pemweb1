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
    <meta http-equiv="refresh" content="10;url=logout.php">
</head>
<body>
    <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    <p>Kamu akan otomatis logout dalam <span id="countdown">10</span> detik.</p>
    <a href="logout.php">Logout Sekarang</a>

    <script>
        let sisa = 10;
        const el = document.getElementById('countdown');
        const timer = setInterval(() => {
            sisa--;
            el.textContent = sisa;
            if (sisa <= 0) clearInterval(timer);
        }, 1000);
    </script>
</body>
</html>
