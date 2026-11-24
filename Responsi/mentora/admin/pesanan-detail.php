<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$order_id = (int)($_GET['id'] ?? 0);
if (!$order_id) { header('Location: ' . base_url('admin/pesanan.php')); exit; }

// Aksi ubah status manual oleh admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ubah_status'])) {
    $new_status = $_POST['status'] ?? '';
    $valid_status = ['menunggu_bayar','menunggu_verifikasi','aktif','selesai','ditolak','dibatalkan'];
    if (in_array($new_status, $valid_status)) {
        $stmt = $conn->prepare('UPDATE orders SET status=? WHERE id=?');
        $stmt->bind_param('si', $new_status, $order_id);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Status pesanan diubah menjadi: ' . $new_status);
        header('Location: ' . base_url('admin/pesanan-detail.php?id=' . $order_id));
        exit;
    }
}

$stmt = $conn->prepare('
    SELECT o.*, u.nama AS siswa_nama, u.email AS siswa_email
    FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=? LIMIT 1
');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) { set_flash('danger', 'Pesanan tidak ditemukan.'); header('Location: ' . base_url('admin/pesanan.php')); exit; }

$stmt = $conn->prepare('
    SELECT oi.harga, c.judul, c.id AS course_id, cat.nama AS kategori
    FROM order_items oi
    JOIN courses c ON c.id=oi.course_id
    JOIN categories cat ON cat.id=c.category_id
    WHERE oi.order_id=?
');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare('SELECT * FROM payments WHERE order_id=? LIMIT 1');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

$page_title = 'Pesanan #' . str_pad($order_id, 5, '0', STR_PAD_LEFT);
include __DIR__ . '/../includes/header.php';
?>
<div class="container" style="max-width:760px">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="<?= base_url('admin/pesanan.php') ?>" class="btn btn-sm btn-outline-secondary" aria-label="Kembali"><i class="bi bi-arrow-left" aria-hidden="true"></i></a>
        <h1 class="h4 fw-800 mb-0">Pesanan #<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?></h1>
    </div>
    <?= flash_html() ?>

    <div class="bg-white border rounded p-4 mb-4" style="border-radius:12px">
        <div class="row g-2 mb-3">
            <div class="col-sm-6">
                <span class="text-muted small">Siswa:</span>
                <div class="fw-700"><?= htmlspecialchars($order['siswa_nama']) ?> (<?= htmlspecialchars($order['siswa_email']) ?>)</div>
            </div>
            <div class="col-sm-6 text-sm-end">
                <span class="text-muted small">Waktu Pemesanan:</span>
                <div><?= date('d M Y H:i', strtotime($order['created_at'])) ?></div>
            </div>
        </div>
        <hr>
        <ul class="list-group list-group-flush mb-3">
            <?php foreach ($items as $it): ?>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <div>
                        <div class="fw-600"><?= htmlspecialchars($it['judul']) ?></div>
                        <span class="badge bg-teal-light text-teal small"><?= htmlspecialchars($it['kategori']) ?></span>
                    </div>
                    <span class="fw-700"><?= $it['harga'] > 0 ? format_rupiah($it['harga']) : 'Gratis' ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="d-flex justify-content-between fw-800 fs-5">
            <span>Total</span>
            <span class="text-teal"><?= format_rupiah($order['total']) ?></span>
        </div>
    </div>

    <!-- Bukti Pembayaran -->
    <?php if ($payment): ?>
        <div class="bg-white border rounded p-4 mb-4" style="border-radius:12px">
            <h2 class="h6 fw-700 mb-3">Bukti Pembayaran</h2>
            <?php if ($payment['bukti']): ?>
                <div class="mb-3">
                    <a href="<?= base_url('uploads/bukti/' . rawurlencode($payment['bukti'])) ?>" target="_blank" rel="noopener">
                        <img src="<?= base_url('uploads/bukti/' . rawurlencode($payment['bukti'])) ?>"
                             alt="Bukti pembayaran" width="300" height="200"
                             style="max-width:100%;border-radius:8px;border:1px solid var(--border)">
                    </a>
                </div>
            <?php endif; ?>
            <p class="mb-1"><strong>Status:</strong> <span class="badge-status badge-<?= $payment['status'] ?>"><?= $payment['status'] ?></span></p>
            <?php if ($payment['catatan']): ?>
                <p class="mb-0 text-muted small"><strong>Catatan:</strong> <?= htmlspecialchars($payment['catatan']) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Ubah Status Manual -->
    <div class="bg-white border rounded p-4" style="border-radius:12px">
        <h2 class="h6 fw-700 mb-3">Ubah Status Pesanan</h2>
        <form method="post" class="d-flex gap-2 align-items-center">
            <select name="status" class="form-select" style="max-width:240px">
                <?php foreach (['menunggu_bayar','menunggu_verifikasi','aktif','selesai','ditolak','dibatalkan'] as $st): ?>
                    <option value="<?= $st ?>" <?= $order['status']===$st ? 'selected':'' ?>><?= str_replace('_',' ',$st) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="ubah_status" class="btn btn-teal">Ubah Status</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
