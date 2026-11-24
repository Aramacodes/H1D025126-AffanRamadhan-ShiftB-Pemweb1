<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$kelas_list = $conn->query('
    SELECT c.id, c.judul, c.harga, c.kuota, c.status, c.created_at,
           cat.nama AS kategori, u.nama AS mentor_nama
    FROM courses c
    JOIN categories cat ON cat.id=c.category_id
    JOIN users u ON u.id=c.mentor_id
    ORDER BY c.created_at DESC
')->fetch_all(MYSQLI_ASSOC);

$page_title = 'Semua Kelas';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <h1 class="h4 fw-800 mb-4">Semua Kelas</h1>
    <?= flash_html() ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Judul</th><th>Kategori</th><th>Mentor</th><th>Harga</th><th>Kuota</th><th>Status</th><th>Dibuat</th></tr>
            </thead>
            <tbody>
                <?php foreach ($kelas_list as $k): ?>
                    <tr>
                        <td><a href="<?= base_url('kelas-detail.php?id=' . $k['id']) ?>" class="fw-600 text-dark text-decoration-none"><?= htmlspecialchars($k['judul']) ?></a></td>
                        <td class="text-muted small"><?= htmlspecialchars($k['kategori']) ?></td>
                        <td><?= htmlspecialchars($k['mentor_nama']) ?></td>
                        <td><?= $k['harga'] > 0 ? format_rupiah($k['harga']) : 'Gratis' ?></td>
                        <td><?= $k['kuota'] ?></td>
                        <td><span class="badge-status badge-<?= $k['status'] ?>"><?= $k['status'] ?></span></td>
                        <td class="text-muted small"><?= date('d M Y', strtotime($k['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($kelas_list)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada kelas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
