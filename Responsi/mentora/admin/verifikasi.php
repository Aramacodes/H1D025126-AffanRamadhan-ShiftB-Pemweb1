<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

// Handle setujui / tolak
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $aksi     = $_POST['aksi'] ?? '';
    $catatan  = trim($_POST['catatan'] ?? '');

    $conn->begin_transaction();
    try {
        if ($aksi === 'setujui') {
            // Update payment
            $stmt = $conn->prepare('UPDATE payments SET status="disetujui", catatan=NULL WHERE order_id=?');
            $stmt->bind_param('i', $order_id);
            $stmt->execute();
            $stmt->close();

            // Update order
            $stmt = $conn->prepare('UPDATE orders SET status="aktif" WHERE id=?');
            $stmt->bind_param('i', $order_id);
            $stmt->execute();
            $stmt->close();

            // Kurangi kuota setiap kelas di order
            $stmt = $conn->prepare('SELECT course_id FROM order_items WHERE order_id=?');
            $stmt->bind_param('i', $order_id);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            foreach ($items as $it) {
                $conn->query('UPDATE courses SET kuota = GREATEST(0, kuota - 1) WHERE id = ' . (int)$it['course_id']);
            }

            set_flash('success', 'Pembayaran pesanan #' . $order_id . ' disetujui. Kelas aktif.');
        } elseif ($aksi === 'tolak') {
            $stmt = $conn->prepare('UPDATE payments SET status="ditolak", catatan=? WHERE order_id=?');
            $stmt->bind_param('si', $catatan, $order_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare('UPDATE orders SET status="ditolak" WHERE id=?');
            $stmt->bind_param('i', $order_id);
            $stmt->execute();
            $stmt->close();

            set_flash('warning', 'Pembayaran pesanan #' . $order_id . ' ditolak. Siswa dapat upload ulang.');
        }
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        set_flash('danger', 'Gagal memproses aksi.');
    }
    header('Location: ' . base_url('admin/verifikasi.php'));
    exit;
}

$antrean = $conn->query('
    SELECT p.id AS payment_id, p.bukti, p.created_at AS waktu_bayar,
           o.id AS order_id, o.total, o.created_at AS waktu_order,
           u.nama AS siswa_nama, u.email AS siswa_email
    FROM payments p
    JOIN orders o ON o.id=p.order_id
    JOIN users u ON u.id=o.user_id
    WHERE p.status="menunggu"
    ORDER BY p.created_at ASC
')->fetch_all(MYSQLI_ASSOC);

$page_title = 'Verifikasi Pembayaran';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <h1 class="h4 fw-800 mb-4">Verifikasi Pembayaran</h1>
    <?= flash_html() ?>

    <?php if (empty($antrean)): ?>
        <div class="empty-state">
            <i class="bi bi-patch-check" aria-hidden="true"></i>
            <p class="fs-6 fw-600">Tidak ada pembayaran yang menunggu verifikasi.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($antrean as $item): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="bg-white border rounded p-3 h-100 d-flex flex-column" style="border-radius:12px">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-700">#<?= str_pad($item['order_id'],5,'0',STR_PAD_LEFT) ?></span>
                            <span class="text-teal fw-800"><?= format_rupiah($item['total']) ?></span>
                        </div>
                        <div class="text-muted small mb-2">
                            <strong><?= htmlspecialchars($item['siswa_nama']) ?></strong><br>
                            <?= htmlspecialchars($item['siswa_email']) ?><br>
                            Waktu: <?= date('d M Y H:i', strtotime($item['waktu_bayar'])) ?>
                        </div>
                        <?php if ($item['bukti']): ?>
                            <div class="mb-3 text-center">
                                <a href="<?= base_url('uploads/bukti/' . rawurlencode($item['bukti'])) ?>" target="_blank" rel="noopener">
                                    <img src="<?= base_url('uploads/bukti/' . rawurlencode($item['bukti'])) ?>"
                                         alt="Bukti transfer pesanan #<?= $item['order_id'] ?>"
                                         width="260" height="160"
                                         style="max-height:160px;object-fit:contain;border-radius:6px;border:1px solid var(--border)">
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="mt-auto pt-2 border-top">
                            <div class="d-flex gap-2">
                                <form method="post" class="flex-grow-1">
                                    <input type="hidden" name="order_id" value="<?= $item['order_id'] ?>">
                                    <input type="hidden" name="aksi" value="setujui">
                                    <button type="submit" class="btn btn-sm btn-teal w-100"
                                        data-confirm="Setujui pembayaran pesanan #<?= $item['order_id'] ?>?">
                                        Setujui
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger flex-grow-1"
                                    data-bs-toggle="collapse" data-bs-target="#tolakForm-<?= $item['order_id'] ?>">
                                    Tolak
                                </button>
                            </div>
                            <div class="collapse mt-2" id="tolakForm-<?= $item['order_id'] ?>">
                                <form method="post">
                                    <input type="hidden" name="order_id" value="<?= $item['order_id'] ?>">
                                    <input type="hidden" name="aksi" value="tolak">
                                    <textarea name="catatan" class="form-control form-control-sm mb-2"
                                        rows="2" placeholder="Alasan penolakan..." required></textarea>
                                    <button type="submit" class="btn btn-sm btn-danger w-100">Kirim Penolakan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
