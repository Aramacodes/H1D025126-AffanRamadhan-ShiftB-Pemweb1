<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare('SELECT id, nama, email, password, role, foto FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['foto']    = $user['foto'];

            $redirect = match($user['role']) {
                'admin'  => base_url('admin/dashboard.php'),
                'mentor' => base_url('mentor/dashboard.php'),
                default  => base_url('home.php'),
            };
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}

$page_title = 'Masuk';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | Mentora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="d-flex align-items-center" style="min-height:100vh;background:var(--bg)">
<div class="container" style="max-width:420px">
    <div class="text-center mb-4">
        <a href="<?= base_url('index.php') ?>" class="navbar-brand fw-800 text-teal fs-4">
            <span class="brand-icon">M</span>entora
        </a>
        <p class="text-muted mt-1">Masuk ke akun kamu</p>
    </div>
    <div class="card border-0 shadow-sm p-4" style="border-radius:14px">
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required autocomplete="email">
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                    required autocomplete="current-password" minlength="8">
            </div>
            <button type="submit" class="btn btn-teal w-100">Masuk</button>
        </form>
        <p class="text-center mt-3 mb-0 text-muted small">
            Belum punya akun? <a href="<?= base_url('auth/register.php') ?>" class="text-teal fw-600">Daftar sekarang</a>
        </p>
    </div>
    <p class="text-center mt-3 text-muted small">
        <strong>Akun demo:</strong> admin@mentora.id / password123
    </p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
