<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Jika belum login, arahkan ke landing page
if (empty($_SESSION['user_id'])) {
    header('Location: ' . base_url('index.php'));
    exit;
}

// Redirect mentor dan admin ke dashboard masing-masing jika membuka home
if ($_SESSION['role'] === 'admin') {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
} elseif ($_SESSION['role'] === 'mentor') {
    header('Location: ' . base_url('mentor/dashboard.php'));
    exit;
}

// Ambil kelas terpopuler untuk siswa
$stmt = $conn->prepare('
    SELECT c.id, c.judul, c.harga, c.kuota, c.thumbnail,
           cat.nama AS kategori,
           u.nama AS mentor_nama,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(DISTINCT r.id) AS jml_review,
           (SELECT COUNT(*) FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            WHERE oi.course_id = c.id AND o.status IN ("aktif","selesai")) AS terdaftar
    FROM courses c
    JOIN categories cat ON cat.id = c.category_id
    JOIN users u ON u.id = c.mentor_id
    LEFT JOIN reviews r ON r.course_id = c.id
    WHERE c.status = "aktif"
    GROUP BY c.id
    ORDER BY avg_rating DESC, c.created_at DESC
    LIMIT 6
');
$stmt->execute();
$kelas_unggulan = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Kelas yang sedang diikuti siswa ini
$uid = $_SESSION['user_id'];
$stmt = $conn->prepare('
    SELECT c.id, c.judul, c.jadwal, c.durasi, c.thumbnail,
           u.nama AS mentor_nama, o.status AS status_pesanan
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN courses c ON c.id = oi.course_id
    JOIN users u ON u.id = c.mentor_id
    WHERE o.user_id = ? AND o.status IN ("aktif", "selesai")
    ORDER BY o.created_at DESC
    LIMIT 3
');
$stmt->bind_param('i', $uid);
$stmt->execute();
$kelas_saya = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$categories = $conn->query('SELECT * FROM categories ORDER BY nama')->fetch_all(MYSQLI_ASSOC);

$page_title = 'Beranda Belajar';
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <?= flash_html() ?>

    <!-- Welcome banner siswa -->
    <div class="p-4 mb-4 rounded-3 text-white" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); border-radius: 14px;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge bg-warning text-dark fw-700 px-3 py-1 mb-2 rounded-pill">Siswa Mentora</span>
                <h1 class="h3 fw-800 mb-2">Halo, <?= htmlspecialchars($_SESSION['nama']) ?>!</h1>
                <p class="mb-3 text-white-50">Lanjutkan pembelajaranmu atau temukan materi baru untuk memperkaya pemahaman akademikmu.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= base_url('kelas.php') ?>" class="btn btn-amber fw-700">Jelajahi Semua Kelas</a>
                    <a href="<?= base_url('siswa/pesanan.php') ?>" class="btn btn-outline-light">Lihat Pesanan Saya</a>
                </div>
            </div>
            <div class="col-md-4 text-md-end d-none d-md-block">
                <i class="bi bi-mortarboard" style="font-size: 5rem; opacity: 0.3;" aria-hidden="true"></i>
            </div>
        </div>
    </div>

    <!-- Kelas yang sedang aktif diikuti siswa -->
    <?php if (!empty($kelas_saya)): ?>
    <section class="mb-5" aria-labelledby="heading-kelas-saya">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 id="heading-kelas-saya" class="h5 fw-800 mb-0">Kelas yang Kamu Ikuti</h2>
            <a href="<?= base_url('siswa/pesanan.php') ?>" class="text-teal small fw-600 text-decoration-none">Semua Pesanan</a>
        </div>
        <div class="row g-3">
            <?php foreach ($kelas_saya as $ks): ?>
            <div class="col-md-4">
                <div class="bg-white border rounded p-3 h-100 d-flex flex-column" style="border-radius:12px">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge-status badge-<?= $ks['status_pesanan'] ?>"><?= ucfirst($ks['status_pesanan']) ?></span>
                        <span class="text-muted small ms-auto"><i class="bi bi-clock me-1" aria-hidden="true"></i><?= htmlspecialchars($ks['durasi'] ?: '-') ?></span>
                    </div>
                    <h3 class="fs-6 fw-700 mb-1">
                        <a href="<?= base_url('kelas-detail.php?id=' . $ks['id']) ?>" class="text-dark text-decoration-none">
                            <?= htmlspecialchars($ks['judul']) ?>
                        </a>
                    </h3>
                    <p class="text-muted small mb-2">Mentor: <?= htmlspecialchars($ks['mentor_nama']) ?></p>
                    <div class="mt-auto pt-2 border-top small text-muted">
                        <i class="bi bi-calendar3 me-1 text-teal" aria-hidden="true"></i><?= htmlspecialchars($ks['jadwal'] ?: 'Jadwal belum ditentukan') ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Rekomendasi Kelas Populer -->
    <section class="mb-5" aria-labelledby="heading-unggulan">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h2 id="heading-unggulan" class="h5 fw-800 mb-1">Rekomendasi Kelas Unggulan</h2>
                <p class="text-muted small mb-0">Pilihan kelas berkualitas dengan penilaian terbaik.</p>
            </div>
            <a href="<?= base_url('kelas.php') ?>" class="btn btn-sm btn-outline-teal">Lihat Semua Kelas</a>
        </div>

        <div class="row g-4">
            <?php foreach ($kelas_unggulan as $k): ?>
            <div class="col-md-6 col-lg-4">
                <article class="card-kelas">
                    <a href="<?= base_url('kelas-detail.php?id=' . $k['id']) ?>">
                        <div class="thumbnail-wrapper">
                            <img src="<?= thumbnail_url($k['thumbnail']) ?>"
                                 alt="Thumbnail kelas <?= htmlspecialchars($k['judul']) ?>"
                                 width="400" height="225"
                                 loading="lazy">
                        </div>
                    </a>
                    <div class="card-body d-flex flex-column p-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-teal-light text-teal small"><?= htmlspecialchars($k['kategori']) ?></span>
                            <?php if ($k['harga'] == 0): ?>
                                <span class="badge-gratis">Gratis</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="fs-6 fw-700 mb-1">
                            <a href="<?= base_url('kelas-detail.php?id=' . $k['id']) ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($k['judul']) ?>
                            </a>
                        </h3>
                        <p class="text-muted small mb-2">oleh <?= htmlspecialchars($k['mentor_nama']) ?></p>
                        <div class="d-flex align-items-center gap-1 mb-2">
                            <?= rating_stars(round($k['avg_rating'])) ?>
                            <span class="text-muted small">(<?= $k['jml_review'] ?> ulasan)</span>
                        </div>
                        <div class="mt-auto fw-700 text-teal pt-2 border-top">
                            <?= $k['harga'] > 0 ? format_rupiah($k['harga']) : '<span style="color:var(--amber)">Gratis</span>' ?>
                        </div>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Kategori Cepat -->
    <section class="p-4 rounded-3 bg-white border" style="border-radius:14px;" aria-labelledby="heading-kategori-cepat">
        <h2 id="heading-kategori-cepat" class="h6 fw-800 mb-3">Jelajahi Berdasarkan Kategori</h2>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($categories as $c): ?>
                <a href="<?= base_url('kelas.php?kategori=' . $c['id']) ?>" class="btn btn-sm btn-outline-secondary">
                    <?= htmlspecialchars($c['nama']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
