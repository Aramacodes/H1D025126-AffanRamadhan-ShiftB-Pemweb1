<?php
// Guard berbasis peran. Panggil di awal setiap halaman terproteksi.
// require_role('admin') atau require_role(['mentor','admin'])

function require_login() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . base_url('auth/login.php'));
        exit;
    }
}

function require_role($roles) {
    require_login();
    if (is_string($roles)) $roles = [$roles];
    if (!in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        include __DIR__ . '/403.php';
        exit;
    }
}

function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $pos = strpos($script, '/mentora');
    $root = ($pos !== false) ? substr($script, 0, $pos + 8) : '';
    return $protocol . '://' . $host . $root . ($path ? '/' . ltrim($path, '/') : '');
}

function current_user() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) return null;
    return [
        'id'   => $_SESSION['user_id'],
        'nama' => $_SESSION['nama'],
        'role' => $_SESSION['role'],
        'foto' => $_SESSION['foto'] ?? null,
    ];
}

function set_flash($type, $msg) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function get_flash() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function flash_html() {
    $f = get_flash();
    if (!$f) return '';
    $cls = $f['type'] === 'success' ? 'alert-success' : ($f['type'] === 'danger' ? 'alert-danger' : 'alert-warning');
    return '<div class="alert ' . $cls . ' alert-dismissible fade show" role="alert">'
        . htmlspecialchars($f['msg'])
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>'
        . '</div>';
}

function format_rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function rating_stars($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating
            ? '<i class="bi bi-star-fill text-warning" aria-hidden="true"></i>'
            : '<i class="bi bi-star text-warning" aria-hidden="true"></i>';
    }
    return $html;
}

function thumbnail_url($file) {
    if ($file && file_exists(__DIR__ . '/../uploads/thumbnail/' . $file)) {
        return base_url('uploads/thumbnail/' . rawurlencode($file));
    }
    return base_url('assets/img/no-thumbnail.svg');
}

function foto_url($file) {
    if ($file && file_exists(__DIR__ . '/../uploads/foto/' . $file)) {
        return base_url('uploads/foto/' . rawurlencode($file));
    }
    return base_url('assets/img/no-avatar.svg');
}
