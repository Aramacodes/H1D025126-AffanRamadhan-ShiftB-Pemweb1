<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('mentor');

$uid = $_SESSION['user_id'];
$id  = (int)($_POST['id'] ?? 0);

// Pastikan kelas milik mentor ini
$stmt = $conn->prepare('SELECT id FROM courses WHERE id=? AND mentor_id=? LIMIT 1');
$stmt->bind_param('ii', $id, $uid);
$stmt->execute();
if (!$stmt->get_result()->fetch_row()) {
    set_flash('danger', 'Kelas tidak ditemukan.');
    header('Location: ' . base_url('mentor/dashboard.php'));
    exit;
}
$stmt->close();

// Cek apakah ada order aktif / selesai
$stmt = $conn->prepare('
    SELECT COUNT(*) FROM order_items oi JOIN orders o ON o.id=oi.order_id
    WHERE oi.course_id=? AND o.status IN ("aktif","selesai","menunggu_verifikasi")
');
$stmt->bind_param('i', $id);
$stmt->execute();
$aktif = $stmt->get_result()->fetch_row()[0];
$stmt->close();

if ($aktif > 0) {
    set_flash('danger', 'Kelas tidak bisa dihapus karena masih ada siswa aktif.');
} else {
    $conn->query('DELETE FROM courses WHERE id=' . $id);
    set_flash('success', 'Kelas berhasil dihapus.');
}

header('Location: ' . base_url('mentor/dashboard.php'));
exit;
