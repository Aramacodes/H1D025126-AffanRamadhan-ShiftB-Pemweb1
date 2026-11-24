<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . base_url('kelas.php')); exit; }

$stmt = $conn->prepare('
    SELECT c.*, cat.nama AS kategori, u.nama AS mentor_nama, u.bio AS mentor_bio,
           u.keahlian AS mentor_keahlian, u.foto AS mentor_foto, u.id AS mentor_id
    FROM courses c
    JOIN categories cat ON cat.id = c.category_id
    JOIN users u ON u.id = c.mentor_id
    WHERE c.id = ? AND c.status = "aktif"
    LIMIT 1
');
$stmt->bind_param('i', $id);
$stmt->execute();
$kelas = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$kelas) { header('Location: ' . base_url('kelas.php')); exit; }

// Hitung terdaftar & sisa kuota
$stmt = $conn->prepare('
    SELECT COUNT(*) FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE oi.course_id = ? AND o.status IN ("aktif","selesai")
');
$stmt->bind_param('i', $id);
$stmt->execute();
$terdaftar = $stmt->get_result()->fetch_row()[0];
$stmt->close();
$sisa = $kelas['kuota'] - $terdaftar;

// Ulasan
$stmt = $conn->prepare('
    SELECT r.*, u.nama AS siswa_nama, u.foto AS siswa_foto
    FROM reviews r JOIN users u ON u.id = r.user_id
    WHERE r.course_id = ?
    ORDER BY r.created_at DESC
');
$stmt->bind_param('i', $id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$avg_rating = $reviews ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;

// Cek apakah siswa sudah beli / sudah di keranjang
$sudah_beli = false;
$di_keranjang = false;
$sudah_review = false;
$bisa_review = false;

if (!empty($_SESSION['user_id']) && $_SESSION['role'] === 'siswa') {
    $uid = $_SESSION['user_id'];

    $stmt = $conn->prepare('
        SELECT o.status FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        WHERE oi.course_id = ? AND o.user_id = ? AND o.status IN ("aktif","selesai","menunggu_bayar","menunggu_verifikasi","ditolak")
        LIMIT 1
    ');
    $stmt->bind_param('ii', $id, $uid);
    $stmt->execute();
    $ord = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $sudah_beli = (bool)$ord;
    $bisa_review = $ord && $ord['status'] === 'selesai';

    $stmt = $conn->prepare('SELECT id FROM cart_items WHERE user_id=? AND course_id=? LIMIT 1');
    $stmt->bind_param('ii', $uid, $id);
    $stmt->execute();
    $di_keranjang = (bool)$stmt->get_result()->fetch_row();
    $stmt->close();

    $stmt = $conn->prepare('SELECT id FROM reviews WHERE user_id=? AND course_id=? LIMIT 1');
    $stmt->bind_param('ii', $uid, $id);
    $stmt->execute();
    $sudah_review = (bool)$stmt->get_result()->fetch_row();
    $stmt->close();
}

// Handle form review
$review_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    require_login();
    require_role('siswa');
    if (!$bisa_review) { set_flash('danger', 'Kamu belum bisa mengulas kelas ini.'); header('Location: ' . $_SERVER['REQUEST_URI']); exit; }

    $rating   = (int)($_POST['rating'] ?? 0);
    $komentar = trim($_POST['komentar'] ?? '');
    if ($rating < 1 || $rating > 5) $review_error = 'Rating harus antara 1 dan 5.';
    elseif (!$komentar) $review_error = 'Komentar tidak boleh kosong.';
    else {
        $uid = $_SESSION['user_id'];
        if ($sudah_review) {
            $stmt = $conn->prepare('UPDATE reviews SET rating=?, komentar=? WHERE user_id=? AND course_id=?');
            $stmt->bind_param('isii', $rating, $komentar, $uid, $id);
        } else {
            $stmt = $conn->prepare('INSERT INTO reviews (user_id, course_id, rating, komentar) VALUES (?,?,?,?)');
            $stmt->bind_param('iiis', $uid, $id, $rating, $komentar);
        }
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Ulasan berhasil disimpan.');
        header('Location: ' . base_url('kelas-detail.php?id=' . $id));
        exit;
    }
}

$page_title = htmlspecialchars($kelas['judul']);
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <?= flash_html() ?>
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('kelas.php') ?>">Kelas</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('kelas.php?kategori=' . $kelas['category_id']) ?>"><?= htmlspecialchars($kelas['kategori']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($kelas['judul']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="bg-white border rounded p-4 mb-4" style="border-radius:12px!important">
                <span class="badge bg-teal-light text-teal mb-2"><?= htmlspecialchars($kelas['kategori']) ?></span>
                <?php if ($kelas['harga'] == 0): ?><span class="badge-gratis ms-1">Gratis</span><?php endif; ?>
                <h1 class="h3 fw-800 mt-2"><?= htmlspecialchars($kelas['judul']) ?></h1>
                <p class="text-muted mb-3">oleh <strong><?= htmlspecialchars($kelas['mentor_nama']) ?></strong></p>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?= rating_stars(round($avg_rating)) ?>
                    <span class="fw-700"><?= number_format($avg_rating, 1) ?></span>
                    <span class="text-muted">(<?= count($reviews) ?> ulasan)</span>
                </div>
                <div class="ratio ratio-16x9 mb-3 rounded overflow-hidden" style="max-height:340px">
                    <img src="<?= thumbnail_url($kelas['thumbnail']) ?>"
                         alt="Thumbnail kelas <?= htmlspecialchars($kelas['judul']) ?>"
                         style="object-fit:cover;width:100%;height:100%"
                         fetchpriority="high">
                </div>
                <h2 class="h5 fw-700 mt-4">Deskripsi Kelas</h2>
                <div class="text-muted" style="white-space:pre-wrap"><?= htmlspecialchars($kelas['deskripsi']) ?></div>

                <div class="row g-3 mt-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded" style="background:#f8fafc">
                            <div class="small text-muted">Jadwal</div>
                            <div class="fw-600"><?= htmlspecialchars($kelas['jadwal'] ?: '-') ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded" style="background:#f8fafc">
                            <div class="small text-muted">Durasi</div>
                            <div class="fw-600"><?= htmlspecialchars($kelas['durasi'] ?: '-') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mentor info -->
            <div class="bg-white border rounded p-4 mb-4" style="border-radius:12px!important">
                <h2 class="h5 fw-700 mb-3">Profil Mentor</h2>
                <div class="d-flex align-items-start gap-3">
                    <img src="<?= foto_url($kelas['mentor_foto']) ?>"
                         alt="Foto <?= htmlspecialchars($kelas['mentor_nama']) ?>"
                         width="56" height="56"
                         style="border-radius:50%;object-fit:cover;border:2px solid var(--teal-light)">
                    <div>
                        <div class="fw-700"><?= htmlspecialchars($kelas['mentor_nama']) ?></div>
                        <?php if ($kelas['mentor_keahlian']): ?>
                            <div class="text-muted small mb-1"><?= htmlspecialchars($kelas['mentor_keahlian']) ?></div>
                        <?php endif; ?>
                        <div class="text-muted"><?= $kelas['mentor_bio'] ? htmlspecialchars($kelas['mentor_bio']) : 'Belum ada bio.' ?></div>
                    </div>
                </div>
            </div>

            <!-- Ulasan -->
            <div class="bg-white border rounded p-4" style="border-radius:12px!important">
                <h2 class="h5 fw-700 mb-3">Ulasan (<?= count($reviews) ?>)</h2>

                <?php if ($bisa_review): ?>
                    <div class="mb-4 p-3 rounded" style="background:#f0fdfa">
                        <h3 class="fs-6 fw-700 mb-3"><?= $sudah_review ? 'Edit ulasan kamu' : 'Tulis ulasan' ?></h3>
                        <?php if ($review_error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($review_error) ?></div>
                        <?php endif; ?>
                        <?php
                        $my_review = null;
                        if ($sudah_review) {
                            foreach ($reviews as $rv) {
                                if ($rv['user_id'] == $_SESSION['user_id']) { $my_review = $rv; break; }
                            }
                        }
                        ?>
                        <form method="post">
                            <div class="mb-2">
                                <label for="rating" class="form-label">Rating</label>
                                <select id="rating" name="rating" class="form-select form-select-sm" style="width:120px" required>
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?= $i ?>" <?= ($my_review && $my_review['rating'] == $i) ? 'selected' : '' ?>>
                                            <?= $i ?> bintang
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="komentar" class="form-label">Komentar</label>
                                <textarea id="komentar" name="komentar" class="form-control" rows="3" required><?= htmlspecialchars($my_review['komentar'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-teal btn-sm">Simpan Ulasan</button>
                        </form>
                    </div>
                <?php endif; ?>

                <?php if (empty($reviews)): ?>
                    <div class="empty-state py-3">
                        <p class="text-muted small">Belum ada ulasan. Jadilah yang pertama!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $rv): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <img src="<?= foto_url($rv['siswa_foto']) ?>"
                                     alt="Foto <?= htmlspecialchars($rv['siswa_nama']) ?>"
                                     width="32" height="32"
                                     style="border-radius:50%;object-fit:cover">
                                <div>
                                    <div class="fw-600 small"><?= htmlspecialchars($rv['siswa_nama']) ?></div>
                                    <div class="d-flex gap-1"><?= rating_stars($rv['rating']) ?></div>
                                </div>
                                <span class="text-muted small ms-auto"><?= date('d M Y', strtotime($rv['created_at'])) ?></span>
                            </div>
                            <p class="mb-0 text-muted small"><?= nl2br(htmlspecialchars($rv['komentar'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar aksi -->
        <aside class="col-lg-4">
            <div class="bg-white border rounded p-4 sticky-top" style="border-radius:12px!important;top:80px">
                <div class="fs-3 fw-800 text-teal mb-1">
                    <?= $kelas['harga'] > 0 ? format_rupiah($kelas['harga']) : '<span style="color:var(--amber)">Gratis</span>' ?>
                </div>
                <div class="text-muted small mb-3">
                    Kuota: <?= $sisa ?> / <?= $kelas['kuota'] ?> tempat tersisa
                </div>

                <?php if (!empty($_SESSION['user_id']) && $_SESSION['role'] === 'siswa'): ?>
                    <?php if ($sudah_beli): ?>
                        <div class="alert alert-success py-2 small">Kamu sudah terdaftar di kelas ini.</div>
                        <a href="<?= base_url('siswa/pesanan.php') ?>" class="btn btn-outline-teal w-100">Lihat Pesanan</a>
                    <?php elseif ($sisa <= 0): ?>
                        <button class="btn btn-secondary w-100" disabled>Kuota Penuh</button>
                    <?php elseif ($di_keranjang): ?>
                        <a href="<?= base_url('siswa/keranjang.php') ?>" class="btn btn-teal w-100">Lihat Keranjang</a>
                    <?php else: ?>
                        <form method="post" action="<?= base_url('siswa/keranjang-aksi.php') ?>">
                            <input type="hidden" name="course_id" value="<?= $id ?>">
                            <input type="hidden" name="aksi" value="tambah">
                            <input type="hidden" name="redirect" value="<?= base_url('kelas-detail.php?id=' . $id) ?>">
                            <button type="submit" class="btn btn-teal w-100">Tambah ke Keranjang</button>
                        </form>
                    <?php endif; ?>
                <?php elseif (empty($_SESSION['user_id'])): ?>
                    <a href="<?= base_url('auth/login.php') ?>" class="btn btn-teal w-100">Masuk untuk Daftar</a>
                <?php else: ?>
                    <div class="alert alert-info py-2 small">Hanya siswa yang bisa mendaftar kelas.</div>
                <?php endif; ?>

                <ul class="list-unstyled mt-3 mb-0 text-muted small">
                    <li class="mb-1"><i class="bi bi-calendar3 me-2 text-teal" aria-hidden="true"></i><?= htmlspecialchars($kelas['jadwal'] ?: 'Belum ditentukan') ?></li>
                    <li class="mb-1"><i class="bi bi-clock me-2 text-teal" aria-hidden="true"></i><?= htmlspecialchars($kelas['durasi'] ?: '-') ?></li>
                    <li><i class="bi bi-people me-2 text-teal" aria-hidden="true"></i>Kuota <?= $kelas['kuota'] ?> siswa</li>
                </ul>
            </div>
        </aside>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
