<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$total_user    = $conn->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetch_row()[0];
$total_kelas   = $conn->query("SELECT COUNT(*) FROM courses")->fetch_row()[0];
$total_order   = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$menunggu_ver  = $conn->query("SELECT COUNT(*) FROM orders WHERE status='menunggu_verifikasi'")->fetch_row()[0];

$recent_orders = $conn->query('
    SELECT o.id, o.total, o.status, o.created_at, u.nama
    FROM orders o JOIN users u ON u.id=o.user_id
    ORDER BY o.created_at DESC LIMIT 8
')->fetch_all(MYSQLI_ASSOC);

$page_title = 'Dashboard Admin';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <h1 class="h4 fw-800 mb-4">Dashboard Admin</h1>
    <?= flash_html() ?>

    <div class="row g-3 mb-5">
        <div class="col-sm-6 col-lg-3">
            <div class="bg-white border rounded p-3 text-center" style="border-radius:12px">
                <div class="fs-2 fw-800 text-teal"><?= $total_user ?></div>
                <div class="text-muted small">Pengguna</div>
                <a href="<?= base_url('admin/users.php') ?>" class="btn btn-sm btn-outline-teal mt-2">Kelola</a>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="bg-white border rounded p-3 text-center" style="border-radius:12px">
                <div class="fs-2 fw-800 text-teal"><?= $total_kelas ?></div>
                <div class="text-muted small">Kelas</div>
                <a href="<?= base_url('admin/kelas.php') ?>" class="btn btn-sm btn-outline-teal mt-2">Kelola</a>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="bg-white border rounded p-3 text-center" style="border-radius:12px">
                <div class="fs-2 fw-800 text-teal"><?= $total_order ?></div>
                <div class="text-muted small">Total Pesanan</div>
                <a href="<?= base_url('admin/pesanan.php') ?>" class="btn btn-sm btn-outline-teal mt-2">Lihat</a>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="bg-white border rounded p-3 text-center" style="border-radius:12px">
                <div class="fs-2 fw-800" style="color:var(--amber)"><?= $menunggu_ver ?></div>
                <div class="text-muted small">Menunggu Verifikasi</div>
                <a href="<?= base_url('admin/verifikasi.php') ?>" class="btn btn-sm btn-amber mt-2">Verifikasi</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <h2 class="h5 fw-700 mb-3">Pesanan Terbaru</h2>
            <?php if (empty($recent_orders)): ?>
                <div class="empty-state py-3"><p>Belum ada pesanan.</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr><th>#</th><th>Siswa</th><th>Total</th><th>Status</th><th>Tanggal</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $o): ?>
                                <tr>
                                    <td><a href="<?= base_url('admin/pesanan-detail.php?id=' . $o['id']) ?>" class="fw-600 text-teal">#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></a></td>
                                    <td><?= htmlspecialchars($o['nama']) ?></td>
                                    <td><?= format_rupiah($o['total']) ?></td>
                                    <td><span class="badge-status badge-<?= $o['status'] ?>"><?= str_replace('_',' ',$o['status']) ?></span></td>
                                    <td class="text-muted small"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-4">
            <h2 class="h5 fw-700 mb-3">Menu Cepat</h2>
            <nav class="sidebar-nav">
                <ul class="nav flex-column gap-1">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/users.php') ?>"><i class="bi bi-people" aria-hidden="true"></i> Kelola Pengguna</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/kategori.php') ?>"><i class="bi bi-tags" aria-hidden="true"></i> Kelola Kategori</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/kelas.php') ?>"><i class="bi bi-journal-text" aria-hidden="true"></i> Semua Kelas</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/pesanan.php') ?>"><i class="bi bi-receipt" aria-hidden="true"></i> Semua Pesanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/verifikasi.php') ?>"><i class="bi bi-patch-check" aria-hidden="true"></i> Verifikasi Pembayaran</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
