<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('siswa');

$uid = $_SESSION['user_id'];

$stmt = $conn->prepare('
    SELECT ci.id AS cart_id, ci.course_id, c.judul, c.harga, c.thumbnail, c.kuota,
           cat.nama AS kategori,
           u.nama AS mentor_nama,
           (SELECT COUNT(*) FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            WHERE oi.course_id = c.id AND o.status IN ("aktif","selesai")) AS terdaftar
    FROM cart_items ci
    JOIN courses c ON c.id = ci.course_id
    JOIN categories cat ON cat.id = c.category_id
    JOIN users u ON u.id = c.mentor_id
    WHERE ci.user_id = ?
    ORDER BY ci.created_at DESC
');
$stmt->bind_param('i', $uid);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = array_sum(array_column($items, 'harga'));

$page_title = 'Keranjang';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <h1 class="h4 fw-800 mb-4">Keranjang Saya</h1>
    <?= flash_html() ?>

    <?php if (empty($items)): ?>
        <div class="empty-state">
            <i class="bi bi-cart3" aria-hidden="true"></i>
            <p class="fs-6 fw-600">Keranjang masih kosong.</p>
            <a href="<?= base_url('kelas.php') ?>" class="btn btn-teal mt-2">Cari Kelas</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <?php foreach ($items as $item): ?>
                    <?php $sisa = $item['kuota'] - $item['terdaftar']; ?>
                    <div class="card border-0 shadow-sm mb-3" style="border-radius:12px">
                        <div class="card-body d-flex gap-3 align-items-start">
                            <img src="<?= thumbnail_url($item['thumbnail']) ?>"
                                 alt="Thumbnail <?= htmlspecialchars($item['judul']) ?>"
                                 width="100" height="56"
                                 style="object-fit:cover;border-radius:8px;flex-shrink:0"
                                 loading="lazy">
                            <div class="flex-grow-1">
                                <div class="small text-muted"><?= htmlspecialchars($item['kategori']) ?></div>
                                <div class="fw-700">
                                    <a href="<?= base_url('kelas-detail.php?id=' . $item['course_id']) ?>"
                                       class="text-dark text-decoration-none">
                                        <?= htmlspecialchars($item['judul']) ?>
                                    </a>
                                </div>
                                <div class="small text-muted">oleh <?= htmlspecialchars($item['mentor_nama']) ?></div>
                                <?php if ($sisa <= 0): ?>
                                    <span class="badge text-bg-danger small">Kuota penuh</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <div class="fw-700 text-teal mb-2">
                                    <?= $item['harga'] > 0 ? format_rupiah($item['harga']) : '<span style="color:var(--amber)">Gratis</span>' ?>
                                </div>
                                <form method="post" action="<?= base_url('siswa/keranjang-aksi.php') ?>">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="redirect" value="<?= base_url('siswa/keranjang.php') ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                        data-confirm="Hapus kelas ini dari keranjang?"
                                        aria-label="Hapus <?= htmlspecialchars($item['judul']) ?> dari keranjang">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <aside class="col-lg-4">
                <div class="bg-white border rounded p-4 sticky-top" style="border-radius:12px!important;top:80px">
                    <h2 class="h6 fw-700 mb-3">Ringkasan Pesanan</h2>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><?= count($items) ?> kelas</span>
                        <span class="fw-600"><?= format_rupiah($total) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-800 fs-5 mb-3">
                        <span>Total</span>
                        <span class="text-teal"><?= format_rupiah($total) ?></span>
                    </div>
                    <?php
                    $ada_penuh = false;
                    foreach ($items as $item) {
                        if (($item['kuota'] - $item['terdaftar']) <= 0) { $ada_penuh = true; break; }
                    }
                    ?>
                    <?php if ($ada_penuh): ?>
                        <div class="alert alert-warning small py-2">Hapus kelas yang kuotanya penuh sebelum checkout.</div>
                    <?php else: ?>
                        <a href="<?= base_url('siswa/checkout.php') ?>" class="btn btn-teal w-100">Lanjut ke Pembayaran</a>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
