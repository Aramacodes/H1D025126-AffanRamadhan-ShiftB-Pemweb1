<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('siswa');

$uid      = $_SESSION['user_id'];
$order_id = (int)($_GET['id'] ?? 0);
if (!$order_id) { header('Location: ' . base_url('siswa/pesanan.php')); exit; }

$stmt = $conn->prepare('SELECT * FROM orders WHERE id=? AND user_id=? LIMIT 1');
$stmt->bind_param('ii', $order_id, $uid);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) { set_flash('danger', 'Pesanan tidak ditemukan.'); header('Location: ' . base_url('siswa/pesanan.php')); exit; }

$stmt = $conn->prepare('
    SELECT oi.harga, c.judul, c.id AS course_id
    FROM order_items oi JOIN courses c ON c.id = oi.course_id
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

// Upload bukti
$upload_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_bukti'])) {
    if (!in_array($order['status'], ['menunggu_bayar','ditolak'])) {
        $upload_error = 'Status pesanan tidak memungkinkan upload bukti.';
    } else {
        $file = $_FILES['bukti'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $upload_error = 'File tidak terupload dengan benar.';
        } else {
            $allowed_ext  = ['jpg','jpeg','png'];
            $allowed_mime = ['image/jpeg','image/png'];
            $ext          = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $finfo        = finfo_open(FILEINFO_MIME_TYPE);
            $mime         = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mime)) {
                $upload_error = 'File harus JPG atau PNG.';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $upload_error = 'Ukuran file maksimal 2 MB.';
            } else {
                $newname  = bin2hex(random_bytes(12)) . '.' . $ext;
                $dest_dir = __DIR__ . '/../uploads/bukti/';
                if (!is_dir($dest_dir)) mkdir($dest_dir, 0755, true);
                move_uploaded_file($file['tmp_name'], $dest_dir . $newname);

                if ($payment) {
                    $stmt = $conn->prepare('UPDATE payments SET bukti=?, status="menunggu", catatan=NULL WHERE order_id=?');
                    $stmt->bind_param('si', $newname, $order_id);
                } else {
                    $stmt = $conn->prepare('INSERT INTO payments (order_id, bukti, status) VALUES (?,?,"menunggu")');
                    $stmt->bind_param('is', $order_id, $newname);
                }
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare('UPDATE orders SET status="menunggu_verifikasi" WHERE id=?');
                $stmt->bind_param('i', $order_id);
                $stmt->execute();
                $stmt->close();

                set_flash('success', 'Bukti pembayaran berhasil diupload. Tunggu verifikasi admin.');
                header('Location: ' . base_url('siswa/pesanan-detail.php?id=' . $order_id));
                exit;
            }
        }
    }
}

$page_title = 'Detail Pesanan #' . str_pad($order_id, 5, '0', STR_PAD_LEFT);
include __DIR__ . '/../includes/header.php';
?>
<div class="container" style="max-width:720px">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="<?= base_url('siswa/pesanan.php') ?>" class="btn btn-sm btn-outline-secondary" aria-label="Kembali ke pesanan">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
        </a>
        <h1 class="h4 fw-800 mb-0">Pesanan #<?= str_pad($order_id, 5, '0', STR_PAD_LEFT) ?></h1>
    </div>
    <?= flash_html() ?>

    <div class="bg-white border rounded p-4 mb-4" style="border-radius:12px">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted small"><?= date('d M Y H:i', strtotime($order['created_at'])) ?></span>
            <span class="badge-status badge-<?= $order['status'] ?> fs-6"><?= str_replace('_', ' ', $order['status']) ?></span>
        </div>
        <ul class="list-group list-group-flush mb-3">
            <?php foreach ($items as $item): ?>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <a href="<?= base_url('kelas-detail.php?id=' . $item['course_id']) ?>" class="text-dark">
                        <?= htmlspecialchars($item['judul']) ?>
                    </a>
                    <span class="fw-600"><?= $item['harga'] > 0 ? format_rupiah($item['harga']) : 'Gratis' ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="d-flex justify-content-between fw-800 fs-5">
            <span>Total</span>
            <span class="text-teal"><?= format_rupiah($order['total']) ?></span>
        </div>
    </div>

    <?php if ($payment): ?>
        <div class="bg-white border rounded p-4 mb-4" style="border-radius:12px">
            <h2 class="h6 fw-700 mb-3">Bukti Pembayaran</h2>
            <?php if ($payment['bukti']): ?>
                <img src="<?= base_url('uploads/bukti/' . rawurlencode($payment['bukti'])) ?>"
                     alt="Bukti pembayaran pesanan"
                     width="400" height="300"
                     style="max-width:100%;border-radius:8px;border:1px solid var(--border)"
                     loading="lazy">
            <?php endif; ?>
            <div class="mt-2">
                <span class="badge-status badge-<?= $payment['status'] ?>">
                    <?= $payment['status'] === 'menunggu' ? 'Menunggu verifikasi' : ucfirst($payment['status']) ?>
                </span>
                <?php if ($payment['catatan'] && $payment['status'] === 'ditolak'): ?>
                    <div class="alert alert-danger mt-2 small"><?= htmlspecialchars($payment['catatan']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (in_array($order['status'], ['menunggu_bayar','ditolak'])): ?>
        <div class="bg-white border rounded p-4" style="border-radius:12px">
            <h2 class="h6 fw-700 mb-3">Upload Bukti Transfer</h2>
            <?php if ($upload_error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($upload_error) ?></div>
            <?php endif; ?>
            <?php if ($order['status'] === 'ditolak'): ?>
                <div class="alert alert-warning small">Pembayaran ditolak. Upload ulang bukti yang benar.</div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="bukti" class="form-label">File Bukti Transfer (JPG/PNG, maks 2 MB)</label>
                    <input type="file" id="bukti" name="bukti" class="form-control"
                           accept=".jpg,.jpeg,.png" required aria-describedby="buktiHelp">
                    <div id="buktiHelp" class="form-text">Format yang diterima: JPG, PNG. Maks 2 MB.</div>
                </div>
                <button type="submit" name="upload_bukti" class="btn btn-teal">Upload Bukti Bayar</button>
            </form>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
