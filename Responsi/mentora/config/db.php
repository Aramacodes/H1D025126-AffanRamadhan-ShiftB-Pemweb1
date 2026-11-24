<?php
// Koneksi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mentora');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#dc2626;">
        <h2>Koneksi Database Gagal</h2>
        <p>' . htmlspecialchars($conn->connect_error) . '</p>
        <p>Pastikan MySQL berjalan dan database <strong>mentora</strong> sudah dibuat.</p>
    </div>');
}

$conn->set_charset('utf8mb4');
