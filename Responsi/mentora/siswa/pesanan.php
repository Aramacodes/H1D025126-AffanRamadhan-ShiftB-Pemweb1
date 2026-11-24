<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('siswa');

$uid = $_SESSION['user_id'];
$stmt = $conn->prepare('
    SELECT o.id, o.total, o.status, o.created_at,
           COUNT(oi.id) AS jml_kelas
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
');
$stmt->bind_param('i', $uid);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Pesanan Saya';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <h1 class="h4 fw-800 mb-4">Pesanan Saya</h1>
    <?= flash_html() ?>

    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="bi bi-receipt" aria-hidden="true"></i>
            <p class="fs-6 fw-600">Belum ada pesanan.</p>
            <a href="<?= base_url('kelas.php') ?>" class="btn btn-teal mt-2">Cari Kelas</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Jumlah Kelas</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="fw-600">#<?= str_pad($o['id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td><?= $o['jml_kelas'] ?> kelas</td>
                            <td class="fw-600"><?= format_rupiah($o['total']) ?></td>
                            <td><span class="badge-status badge-<?= $o['status'] ?>"><?= str_replace('_', ' ', $o['status']) ?></span></td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                            <td>
                                <a href="<?= base_url('siswa/pesanan-detail.php?id=' . $o['id']) ?>"
                                   class="btn btn-sm btn-outline-teal">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
