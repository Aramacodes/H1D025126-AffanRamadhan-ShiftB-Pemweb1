<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$errors = [];
$input  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['nama']     = trim($_POST['nama'] ?? '');
    $input['email']    = trim($_POST['email'] ?? '');
    $input['password'] = $_POST['password'] ?? '';
    $input['konfirm']  = $_POST['konfirm'] ?? '';
    $input['role']     = $_POST['role'] ?? '';

    if (!$input['nama'])                                   $errors[] = 'Nama wajib diisi.';
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (strlen($input['password']) < 8)                    $errors[] = 'Password minimal 8 karakter.';
    if ($input['password'] !== $input['konfirm'])          $errors[] = 'Konfirmasi password tidak cocok.';
    if (!in_array($input['role'], ['siswa','mentor']))     $errors[] = 'Pilih peran yang valid.';

    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $input['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email sudah terdaftar. Gunakan email lain.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hash = password_hash($input['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare('INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $input['nama'], $input['email'], $hash, $input['role']);
        $stmt->execute();
        $new_id = $stmt->insert_id;
        $stmt->close();

        session_regenerate_id(true);
        $_SESSION['user_id'] = $new_id;
        $_SESSION['nama']    = $input['nama'];
        $_SESSION['role']    = $input['role'];
        $_SESSION['foto']    = null;

        $redirect = $input['role'] === 'mentor'
            ? base_url('mentor/dashboard.php')
            : base_url('home.php');
        set_flash('success', 'Selamat datang di Mentora, ' . $input['nama'] . '!');
        header('Location: ' . $redirect);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | Mentora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="d-flex align-items-center py-4" style="min-height:100vh;background:var(--bg)">
<div class="container" style="max-width:460px">
    <div class="text-center mb-4">
        <a href="<?= base_url('index.php') ?>" class="navbar-brand fw-800 text-teal fs-4">
            <span class="brand-icon">M</span>entora
        </a>
        <p class="text-muted mt-1">Buat akun gratis</p>
    </div>
    <div class="card border-0 shadow-sm p-4" style="border-radius:14px">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" novalidate>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="form-control"
                    value="<?= htmlspecialchars($input['nama'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                    value="<?= htmlspecialchars($input['email'] ?? '') ?>" required autocomplete="email">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                    required autocomplete="new-password" minlength="8">
                <div class="form-text">Minimal 8 karakter.</div>
            </div>
            <div class="mb-3">
                <label for="konfirm" class="form-label">Konfirmasi Password</label>
                <input type="password" id="konfirm" name="konfirm" class="form-control"
                    required autocomplete="new-password">
            </div>
            <div class="mb-4">
                <label class="form-label d-block">Daftar sebagai</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="role_siswa" value="siswa"
                            <?= (!isset($input['role']) || $input['role'] === 'siswa') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="role_siswa">Siswa</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="role_mentor" value="mentor"
                            <?= (isset($input['role']) && $input['role'] === 'mentor') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="role_mentor">Mentor</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-teal w-100">Buat Akun</button>
        </form>
        <p class="text-center mt-3 mb-0 text-muted small">
            Sudah punya akun? <a href="<?= base_url('auth/login.php') ?>" class="text-teal fw-600">Masuk</a>
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
