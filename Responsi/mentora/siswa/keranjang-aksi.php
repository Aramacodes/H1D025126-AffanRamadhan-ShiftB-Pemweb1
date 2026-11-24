<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('siswa');

$aksi     = $_POST['aksi'] ?? '';
$redirect = $_POST['redirect'] ?? base_url('siswa/keranjang.php');
$uid      = $_SESSION['user_id'];

if ($aksi === 'tambah') {
    $course_id = (int)($_POST['course_id'] ?? 0);
    if (!$course_id) { set_flash('danger', 'Kelas tidak valid.'); header('Location: ' . $redirect); exit; }

    // Cek kelas aktif
    $stmt = $conn->prepare('SELECT id, kuota FROM courses WHERE id=? AND status="aktif" LIMIT 1');
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $kelas = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$kelas) { set_flash('danger', 'Kelas tidak ditemukan.'); header('Location: ' . $redirect); exit; }

    // Cek kuota
    $stmt = $conn->prepare('
        SELECT COUNT(*) FROM order_items oi JOIN orders o ON o.id=oi.order_id
        WHERE oi.course_id=? AND o.status IN ("aktif","selesai")
    ');
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $terdaftar = $stmt->get_result()->fetch_row()[0];
    $stmt->close();
    if ($terdaftar >= $kelas['kuota']) { set_flash('danger', 'Kuota kelas sudah penuh.'); header('Location: ' . $redirect); exit; }

    // Cek sudah beli
    $stmt = $conn->prepare('
        SELECT o.id FROM order_items oi JOIN orders o ON o.id=oi.order_id
        WHERE oi.course_id=? AND o.user_id=? AND o.status IN ("aktif","selesai","menunggu_bayar","menunggu_verifikasi")
        LIMIT 1
    ');
    $stmt->bind_param('ii', $course_id, $uid);
    $stmt->execute();
    if ($stmt->get_result()->fetch_row()) { set_flash('warning', 'Kamu sudah mendaftar di kelas ini.'); header('Location: ' . $redirect); exit; }
    $stmt->close();

    $stmt = $conn->prepare('INSERT IGNORE INTO cart_items (user_id, course_id) VALUES (?,?)');
    $stmt->bind_param('ii', $uid, $course_id);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Kelas berhasil ditambahkan ke keranjang.');

} elseif ($aksi === 'hapus') {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    $stmt = $conn->prepare('DELETE FROM cart_items WHERE id=? AND user_id=?');
    $stmt->bind_param('ii', $cart_id, $uid);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Kelas dihapus dari keranjang.');
}

header('Location: ' . $redirect);
exit;
