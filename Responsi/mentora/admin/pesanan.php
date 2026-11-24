<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$orders = $conn->query('
    SELECT o.id, o.total, o.status, o.created_at, u.nama AS siswa_nama,
           COUNT(oi.id) AS jml_kelas
    FROM orders o
    JOIN users u ON u.id=o.user_id
    LEFT JOIN order_items oi ON oi.order_id=o.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
')->fetch_all(MYSQLI_ASSOC);

$page_title = 'Semua Pesanan';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <h1 class="h4 fw-800 mb-4">Semua Pesanan</h1>
    <?= flash_html() ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>#</th><th>Siswa</th><th>Jumlah Kelas</th><th>Total</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td class="fw-600">#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></td>
                        <td><?= htmlspecialchars($o['siswa_nama']) ?></td>
                        <td><?= $o['jml_kelas'] ?> kelas</td>
                        <td class="fw-600"><?= format_rupiah($o['total']) ?></td>
                        <td><span class="badge-status badge-<?= $o['status'] ?>"><?= str_replace('_',' ',$o['status']) ?></span></td>
                        <td class="text-muted small"><?= date('d M Y H:i', strtotime($o['created_at'])) ?></td>
                        <td>
                            <a href="<?= base_url('admin/pesanan-detail.php?id=' . $o['id']) ?>"
                               class="btn btn-sm btn-outline-teal">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
