<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('siswa');

$uid = $_SESSION['user_id'];

// Ambil isi keranjang
$stmt = $conn->prepare('
    SELECT ci.id AS cart_id, ci.course_id, c.judul, c.harga, c.kuota,
           (SELECT COUNT(*) FROM order_items oi
            JOIN orders o ON o.id=oi.order_id
            WHERE oi.course_id=c.id AND o.status IN ("aktif","selesai")) AS terdaftar
    FROM cart_items ci
    JOIN courses c ON c.id=ci.course_id
    WHERE ci.user_id=?
');
$stmt->bind_param('i', $uid);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($items)) {
    set_flash('warning', 'Keranjang kosong.');
    header('Location: ' . base_url('siswa/keranjang.php'));
    exit;
}

// Validasi kuota
foreach ($items as $item) {
    if (($item['kuota'] - $item['terdaftar']) <= 0) {
        set_flash('danger', 'Kelas "' . $item['judul'] . '" sudah penuh. Hapus dulu dari keranjang.');
        header('Location: ' . base_url('siswa/keranjang.php'));
        exit;
    }
}

$total = array_sum(array_column($items, 'harga'));

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi'])) {
    $conn->begin_transaction();
    try {
        $status_order = $total == 0 ? 'aktif' : 'menunggu_bayar';

        $stmt = $conn->prepare('INSERT INTO orders (user_id, total, status) VALUES (?,?,?)');
        $stmt->bind_param('ids', $uid, $total, $status_order);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        foreach ($items as $item) {
            $stmt = $conn->prepare('INSERT INTO order_items (order_id, course_id, harga) VALUES (?,?,?)');
            $stmt->bind_param('iid', $order_id, $item['course_id'], $item['harga']);
            $stmt->execute();
            $stmt->close();
        }

        // Jika gratis, langsung kurangi kuota (tidak perlu payment)
        if ($total == 0) {
            foreach ($items as $item) {
                $conn->query('UPDATE courses SET kuota = GREATEST(0, kuota - 1) WHERE id = ' . (int)$item['course_id']);
            }
        }

        // Kosongkan keranjang
        $stmt = $conn->prepare('DELETE FROM cart_items WHERE user_id=?');
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        $msg = $total == 0
            ? 'Pendaftaran berhasil! Kelas gratis langsung aktif.'
            : 'Pesanan berhasil dibuat. Silakan upload bukti pembayaran.';
        set_flash('success', $msg);
        header('Location: ' . base_url('siswa/pesanan-detail.php?id=' . $order_id));
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        set_flash('danger', 'Gagal memproses pesanan. Coba lagi.');
        header('Location: ' . base_url('siswa/keranjang.php'));
        exit;
    }
}

$page_title = 'Checkout';
include __DIR__ . '/../includes/header.php';
?>
<div class="container" style="max-width:720px">
    <h1 class="h4 fw-800 mb-4">Konfirmasi Pesanan</h1>
    <?= flash_html() ?>

    <div class="bg-white border rounded p-4 mb-4" style="border-radius:12px">
        <h2 class="h6 fw-700 mb-3">Kelas yang dipesan</h2>
        <ul class="list-group list-group-flush">
            <?php foreach ($items as $item): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span><?= htmlspecialchars($item['judul']) ?></span>
                    <span class="fw-700 text-teal">
                        <?= $item['harga'] > 0 ? format_rupiah($item['harga']) : 'Gratis' ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
        <hr>
        <div class="d-flex justify-content-between fw-800 fs-5">
            <span>Total</span>
            <span class="text-teal"><?= format_rupiah($total) ?></span>
        </div>
    </div>

    <?php if ($total == 0): ?>
        <div class="alert alert-success">Semua kelas gratis. Pesanan langsung aktif setelah konfirmasi.</div>
    <?php else: ?>
        <div class="alert alert-info small">
            Setelah konfirmasi, kamu perlu mengupload bukti transfer ke rekening:<br>
            <strong>Bank BCA 1234567890 a.n. Mentora Platform</strong><br>
            (Gunakan rekening nyata milik Anda di produksi)
        </div>
    <?php endif; ?>

    <form method="post" class="d-flex gap-3">
        <button type="submit" name="konfirmasi" class="btn btn-teal">
            <?= $total == 0 ? 'Konfirmasi & Aktifkan Kelas' : 'Konfirmasi Pesanan' ?>
        </button>
        <a href="<?= base_url('siswa/keranjang.php') ?>" class="btn btn-outline-secondary">Kembali ke Keranjang</a>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
