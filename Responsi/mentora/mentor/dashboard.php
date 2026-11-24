<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('mentor');

$uid = $_SESSION['user_id'];

// Jumlah kelas
$total_kelas = $conn->query("SELECT COUNT(*) FROM courses WHERE mentor_id=$uid")->fetch_row()[0];

// Total siswa terdaftar di kelas mentor
$stmt = $conn->prepare('
    SELECT COUNT(DISTINCT oi.id) FROM order_items oi
    JOIN orders o ON o.id=oi.order_id
    JOIN courses c ON c.id=oi.course_id
    WHERE c.mentor_id=? AND o.status IN ("aktif","selesai")
');
$stmt->bind_param('i', $uid);
$stmt->execute();
$total_siswa = $stmt->get_result()->fetch_row()[0];
$stmt->close();

// Total pendapatan
$stmt = $conn->prepare('
    SELECT COALESCE(SUM(oi.harga),0) FROM order_items oi
    JOIN orders o ON o.id=oi.order_id
    JOIN courses c ON c.id=oi.course_id
    WHERE c.mentor_id=? AND o.status IN ("aktif","selesai")
');
$stmt->bind_param('i', $uid);
$stmt->execute();
$total_pendapatan = $stmt->get_result()->fetch_row()[0];
$stmt->close();

// Kelas mentor
$stmt = $conn->prepare('
    SELECT c.*, cat.nama AS kategori,
           COUNT(DISTINCT r.id) AS jml_review,
           COALESCE(AVG(r.rating),0) AS avg_rating,
           (SELECT COUNT(*) FROM order_items oi JOIN orders o ON o.id=oi.order_id
            WHERE oi.course_id=c.id AND o.status IN ("aktif","selesai")) AS terdaftar
    FROM courses c
    JOIN categories cat ON cat.id=c.category_id
    LEFT JOIN reviews r ON r.course_id=c.id
    WHERE c.mentor_id=?
    GROUP BY c.id
    ORDER BY c.created_at DESC
');
$stmt->bind_param('i', $uid);
$stmt->execute();
$kelas_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Dashboard Mentor';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-800 mb-0">Dashboard Mentor</h1>
        <a href="<?= base_url('mentor/kelas-form.php') ?>" class="btn btn-teal">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Buat Kelas
        </a>
    </div>
    <?= flash_html() ?>

    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="bg-white border rounded p-3 text-center" style="border-radius:12px">
                <div class="fs-2 fw-800 text-teal"><?= $total_kelas ?></div>
                <div class="text-muted small">Kelas Dibuat</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="bg-white border rounded p-3 text-center" style="border-radius:12px">
                <div class="fs-2 fw-800 text-teal"><?= $total_siswa ?></div>
                <div class="text-muted small">Siswa Terdaftar</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="bg-white border rounded p-3 text-center" style="border-radius:12px">
                <div class="fs-2 fw-800" style="color:var(--amber)"><?= format_rupiah($total_pendapatan) ?></div>
                <div class="text-muted small">Total Pendapatan</div>
            </div>
        </div>
    </div>

    <h2 class="h5 fw-700 mb-3">Kelas Saya</h2>
    <?php if (empty($kelas_list)): ?>
        <div class="empty-state">
            <i class="bi bi-journal-text" aria-hidden="true"></i>
            <p>Belum ada kelas. Buat kelas pertama kamu!</p>
            <a href="<?= base_url('mentor/kelas-form.php') ?>" class="btn btn-teal mt-2">Buat Kelas</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Kuota</th>
                        <th>Terdaftar</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kelas_list as $k): ?>
                        <tr>
                            <td class="fw-600"><?= htmlspecialchars($k['judul']) ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($k['kategori']) ?></td>
                            <td><?= $k['harga'] > 0 ? format_rupiah($k['harga']) : 'Gratis' ?></td>
                            <td><?= $k['kuota'] ?></td>
                            <td><?= $k['terdaftar'] ?></td>
                            <td>
                                <?= number_format($k['avg_rating'], 1) ?>
                                <i class="bi bi-star-fill text-warning" aria-hidden="true"></i>
                                <span class="text-muted small">(<?= $k['jml_review'] ?>)</span>
                            </td>
                            <td><span class="badge-status badge-<?= $k['status'] ?>"><?= $k['status'] ?></span></td>
                            <td>
                                <a href="<?= base_url('mentor/kelas-form.php?id=' . $k['id']) ?>"
                                   class="btn btn-sm btn-outline-teal">Edit</a>
                                <a href="<?= base_url('mentor/pendaftar.php?id=' . $k['id']) ?>"
                                   class="btn btn-sm btn-outline-secondary ms-1">Pendaftar</a>
                                <form method="post" action="<?= base_url('mentor/kelas-hapus.php') ?>" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger ms-1"
                                        data-confirm="Hapus kelas ini? Aksi tidak bisa dibatalkan.">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
