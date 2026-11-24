<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('mentor');

$uid       = $_SESSION['user_id'];
$course_id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare('SELECT judul FROM courses WHERE id=? AND mentor_id=? LIMIT 1');
$stmt->bind_param('ii', $course_id, $uid);
$stmt->execute();
$kelas = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$kelas) { set_flash('danger', 'Kelas tidak ditemukan.'); header('Location: ' . base_url('mentor/dashboard.php')); exit; }

// Handle tandai selesai
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selesaikan'])) {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $stmt = $conn->prepare('
        UPDATE orders SET status="selesai"
        WHERE id=? AND status="aktif"
        AND EXISTS (SELECT 1 FROM order_items WHERE order_id=? AND course_id=?)
    ');
    $stmt->bind_param('iii', $order_id, $order_id, $course_id);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Pesanan ditandai selesai.');
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$stmt = $conn->prepare('
    SELECT o.id AS order_id, o.status, o.created_at, u.nama AS siswa_nama, u.email
    FROM order_items oi
    JOIN orders o ON o.id=oi.order_id
    JOIN users u ON u.id=o.user_id
    WHERE oi.course_id=?
    ORDER BY o.created_at DESC
');
$stmt->bind_param('i', $course_id);
$stmt->execute();
$pendaftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Pendaftar: ' . $kelas['judul'];
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="<?= base_url('mentor/dashboard.php') ?>" class="btn btn-sm btn-outline-secondary" aria-label="Kembali">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
        </a>
        <h1 class="h4 fw-800 mb-0">Pendaftar: <?= htmlspecialchars($kelas['judul']) ?></h1>
    </div>
    <?= flash_html() ?>

    <?php if (empty($pendaftar)): ?>
        <div class="empty-state">
            <i class="bi bi-people" aria-hidden="true"></i>
            <p>Belum ada siswa yang mendaftar di kelas ini.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendaftar as $p): ?>
                        <tr>
                            <td class="fw-600"><?= htmlspecialchars($p['siswa_nama']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($p['email']) ?></td>
                            <td><span class="badge-status badge-<?= $p['status'] ?>"><?= str_replace('_',' ',$p['status']) ?></span></td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                            <td>
                                <?php if ($p['status'] === 'aktif'): ?>
                                    <form method="post">
                                        <input type="hidden" name="order_id" value="<?= $p['order_id'] ?>">
                                        <button type="submit" name="selesaikan"
                                            class="btn btn-sm btn-teal"
                                            data-confirm="Tandai pesanan ini sebagai selesai?">
                                            Tandai Selesai
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
