<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Mentora') ?> | Mentora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<header>
<?php
$brand_target = 'index.php';
if (!empty($_SESSION['user_id'])) {
    $brand_target = match($_SESSION['role']) {
        'admin'  => 'admin/dashboard.php',
        'mentor' => 'mentor/dashboard.php',
        default  => 'home.php',
    };
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top" aria-label="Navigasi utama">
    <div class="container">
        <a class="navbar-brand fw-800 text-teal" href="<?= base_url($brand_target) ?>">
            <span class="brand-icon" aria-hidden="true">M</span>entora
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"
            aria-controls="navMain" aria-expanded="false" aria-label="Buka menu navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('kelas.php') ?>">Cari Kelas</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-sm btn-outline-teal">Dashboard Admin</a>
                    <?php elseif ($_SESSION['role'] === 'mentor'): ?>
                        <a href="<?= base_url('mentor/dashboard.php') ?>" class="btn btn-sm btn-outline-teal">Dashboard Mentor</a>
                    <?php else: ?>
                        <a href="<?= base_url('siswa/keranjang.php') ?>" class="btn btn-sm btn-outline-teal" aria-label="Keranjang belanja">
                            <i class="bi bi-cart3" aria-hidden="true"></i>
                        </a>
                        <a href="<?= base_url('siswa/pesanan.php') ?>" class="btn btn-sm btn-outline-teal">Pesanan Saya</a>
                    <?php endif; ?>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-teal dropdown-toggle" type="button"
                            id="userMenuBtn" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= htmlspecialchars($_SESSION['nama']) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuBtn">
                            <li><a class="dropdown-item" href="<?= base_url('profil.php') ?>">Profil Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= base_url('auth/logout.php') ?>">Keluar</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= base_url('auth/login.php') ?>" class="btn btn-sm btn-outline-teal">Masuk</a>
                    <a href="<?= base_url('auth/register.php') ?>" class="btn btn-sm btn-teal">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
</header>
<main class="main-content">
