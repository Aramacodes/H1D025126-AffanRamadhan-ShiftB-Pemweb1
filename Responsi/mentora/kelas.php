<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$search   = trim($_GET['q'] ?? '');
$cat_id   = (int)($_GET['kategori'] ?? 0);
$harga_min = (float)($_GET['harga_min'] ?? 0);
$harga_max = (float)($_GET['harga_max'] ?? 999999);
$urut     = $_GET['urut'] ?? 'terbaru';

$where    = ['c.status = "aktif"'];
$params   = [];
$types    = '';

if ($search) {
    $where[]  = 'c.judul LIKE ?';
    $params[] = '%' . $search . '%';
    $types   .= 's';
}
if ($cat_id) {
    $where[]  = 'c.category_id = ?';
    $params[] = $cat_id;
    $types   .= 'i';
}
$where[]  = 'c.harga >= ?';
$params[] = $harga_min;
$types   .= 'd';
$where[]  = 'c.harga <= ?';
$params[] = $harga_max;
$types   .= 'd';

$order = match($urut) {
    'harga_asc'  => 'c.harga ASC',
    'harga_desc' => 'c.harga DESC',
    'rating'     => 'avg_rating DESC',
    default      => 'c.created_at DESC',
};

$sql = '
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
    WHERE ' . implode(' AND ', $where) . '
    GROUP BY c.id
    ORDER BY ' . $order;

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$kelas_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$categories = $conn->query('SELECT * FROM categories ORDER BY nama')->fetch_all(MYSQLI_ASSOC);

$page_title = 'Cari Kelas';
include __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="row">
        <!-- Sidebar filter -->
        <aside class="col-lg-3 mb-4" aria-label="Filter kelas">
            <div class="bg-white border rounded p-3" style="border-radius:12px!important">
                <h2 class="fs-6 fw-700 mb-3">Filter Kelas</h2>
                <form method="get" id="formFilter">
                    <div class="mb-3">
                        <label for="q" class="form-label small">Cari judul</label>
                        <input type="text" id="q" name="q" class="form-control form-control-sm"
                            value="<?= htmlspecialchars($search) ?>" placeholder="Ketik judul kelas...">
                    </div>
                    <div class="mb-3">
                        <label for="kategori" class="form-label small">Kategori</label>
                        <select id="kategori" name="kategori" class="form-select form-select-sm">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $cat_id == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Rentang harga (Rp)</label>
                        <div class="d-flex gap-2">
                            <input type="number" name="harga_min" class="form-control form-control-sm"
                                placeholder="Min" min="0"
                                value="<?= $harga_min > 0 ? (int)$harga_min : '' ?>">
                            <input type="number" name="harga_max" class="form-control form-control-sm"
                                placeholder="Maks" min="0"
                                value="<?= $harga_max < 999999 ? (int)$harga_max : '' ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="urut" class="form-label small">Urutkan</label>
                        <select id="urut" name="urut" class="form-select form-select-sm">
                            <option value="terbaru" <?= $urut==='terbaru' ? 'selected' : '' ?>>Terbaru</option>
                            <option value="rating" <?= $urut==='rating' ? 'selected' : '' ?>>Rating tertinggi</option>
                            <option value="harga_asc" <?= $urut==='harga_asc' ? 'selected' : '' ?>>Harga: rendah ke tinggi</option>
                            <option value="harga_desc" <?= $urut==='harga_desc' ? 'selected' : '' ?>>Harga: tinggi ke rendah</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-teal w-100 btn-sm">Terapkan Filter</button>
                    <a href="<?= base_url('kelas.php') ?>" class="btn btn-link w-100 btn-sm text-muted mt-1">Reset</a>
                </form>
            </div>
        </aside>

        <!-- Daftar kelas -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="fs-5 fw-700 mb-0">
                    <?= $search ? 'Hasil pencarian: "' . htmlspecialchars($search) . '"' : 'Semua Kelas' ?>
                </h1>
                <span class="text-muted small"><?= count($kelas_list) ?> kelas ditemukan</span>
            </div>

            <?php if (empty($kelas_list)): ?>
                <div class="empty-state">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <p class="fs-6 fw-600">Tidak ada kelas yang cocok.</p>
                    <p class="small">Coba ubah filter atau kata kunci pencarian.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($kelas_list as $k): ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="card-kelas">
                            <a href="<?= base_url('kelas-detail.php?id=' . $k['id']) ?>">
                                <div class="thumbnail-wrapper">
                                    <img src="<?= thumbnail_url($k['thumbnail']) ?>"
                                         alt="Thumbnail <?= htmlspecialchars($k['judul']) ?>"
                                         width="400" height="225" loading="lazy">
                                </div>
                            </a>
                            <div class="card-body d-flex flex-column p-3">
                                <div class="d-flex flex-wrap align-items-center gap-1 mb-1">
                                    <span class="badge bg-teal-light text-teal small"><?= htmlspecialchars($k['kategori']) ?></span>
                                    <?php if ($k['harga'] == 0): ?>
                                        <span class="badge-gratis">Gratis</span>
                                    <?php endif; ?>
                                    <?php
                                    $sisa = $k['kuota'] - $k['terdaftar'];
                                    if ($sisa <= 3 && $sisa > 0): ?>
                                        <span class="badge text-bg-warning small">Sisa <?= $sisa ?> tempat</span>
                                    <?php elseif ($sisa <= 0): ?>
                                        <span class="badge text-bg-danger small">Penuh</span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="fs-6 fw-700 mb-1">
                                    <a href="<?= base_url('kelas-detail.php?id=' . $k['id']) ?>"
                                       class="text-dark text-decoration-none">
                                        <?= htmlspecialchars($k['judul']) ?>
                                    </a>
                                </h3>
                                <p class="text-muted small mb-2">oleh <?= htmlspecialchars($k['mentor_nama']) ?></p>
                                <div class="d-flex align-items-center gap-1 mb-2">
                                    <?= rating_stars(round($k['avg_rating'])) ?>
                                    <span class="text-muted small">(<?= $k['jml_review'] ?>)</span>
                                </div>
                                <div class="mt-auto fw-700 text-teal">
                                    <?= $k['harga'] > 0 ? format_rupiah($k['harga']) : '<span style="color:var(--amber)">Gratis</span>' ?>
                                </div>
                            </div>
                        </article>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
