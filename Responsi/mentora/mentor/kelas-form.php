<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('mentor');

$uid     = $_SESSION['user_id'];
$edit_id = (int)($_GET['id'] ?? 0);
$kelas   = null;
$errors  = [];

if ($edit_id) {
    $stmt = $conn->prepare('SELECT * FROM courses WHERE id=? AND mentor_id=? LIMIT 1');
    $stmt->bind_param('ii', $edit_id, $uid);
    $stmt->execute();
    $kelas = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$kelas) { set_flash('danger', 'Kelas tidak ditemukan.'); header('Location: ' . base_url('mentor/dashboard.php')); exit; }
}

$categories = $conn->query('SELECT * FROM categories ORDER BY nama')->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul      = trim($_POST['judul'] ?? '');
    $deskripsi  = trim($_POST['deskripsi'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $harga      = (float)($_POST['harga'] ?? 0);
    $jadwal     = trim($_POST['jadwal'] ?? '');
    $durasi     = trim($_POST['durasi'] ?? '');
    $kuota      = (int)($_POST['kuota'] ?? 0);
    $status     = $_POST['status'] === 'aktif' ? 'aktif' : 'nonaktif';

    if (!$judul)        $errors[] = 'Judul wajib diisi.';
    if (!$category_id)  $errors[] = 'Pilih kategori.';
    if ($harga < 0)     $errors[] = 'Harga tidak boleh negatif.';
    if ($kuota < 1)     $errors[] = 'Kuota minimal 1.';

    // Upload thumbnail
    $thumbnail = $kelas['thumbnail'] ?? null;
    if (!empty($_FILES['thumbnail']['name'])) {
        $file = $_FILES['thumbnail'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowed_ext  = ['jpg','jpeg','png'];
        $allowed_mime = ['image/jpeg','image/png'];
        if (!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mime)) $errors[] = 'Thumbnail harus JPG/PNG.';
        elseif ($file['size'] > 2 * 1024 * 1024) $errors[] = 'Thumbnail maksimal 2 MB.';
        else {
            $dir = __DIR__ . '/../uploads/thumbnail/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $newname = bin2hex(random_bytes(12)) . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $dir . $newname);
            $thumbnail = $newname;
        }
    }

    if (empty($errors)) {
        if ($edit_id) {
            $stmt = $conn->prepare('
                UPDATE courses SET judul=?,deskripsi=?,category_id=?,harga=?,jadwal=?,durasi=?,kuota=?,thumbnail=?,status=?
                WHERE id=? AND mentor_id=?
            ');
            $stmt->bind_param('ssidssissii', $judul, $deskripsi, $category_id, $harga, $jadwal, $durasi, $kuota, $thumbnail, $status, $edit_id, $uid);
        } else {
            $stmt = $conn->prepare('
                INSERT INTO courses (mentor_id,category_id,judul,deskripsi,harga,jadwal,durasi,kuota,thumbnail,status)
                VALUES (?,?,?,?,?,?,?,?,?,?)
            ');
            $stmt->bind_param('iissdssiss', $uid, $category_id, $judul, $deskripsi, $harga, $jadwal, $durasi, $kuota, $thumbnail, $status);
        }
        $stmt->execute();
        $stmt->close();
        set_flash('success', $edit_id ? 'Kelas berhasil diperbarui.' : 'Kelas berhasil dibuat.');
        header('Location: ' . base_url('mentor/dashboard.php'));
        exit;
    }
}

$page_title = $edit_id ? 'Edit Kelas' : 'Buat Kelas';
include __DIR__ . '/../includes/header.php';
?>
<div class="container" style="max-width:720px">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="<?= base_url('mentor/dashboard.php') ?>" class="btn btn-sm btn-outline-secondary" aria-label="Kembali">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
        </a>
        <h1 class="h4 fw-800 mb-0"><?= $edit_id ? 'Edit Kelas' : 'Buat Kelas Baru' ?></h1>
    </div>
    <?= flash_html() ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul></div>
    <?php endif; ?>

    <div class="bg-white border rounded p-4" style="border-radius:12px">
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul Kelas</label>
                <input type="text" id="judul" name="judul" class="form-control"
                    value="<?= htmlspecialchars($_POST['judul'] ?? $kelas['judul'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" rows="4"><?= htmlspecialchars($_POST['deskripsi'] ?? $kelas['deskripsi'] ?? '') ?></textarea>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="category_id" class="form-label">Kategori</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">Pilih kategori</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                <?= (($_POST['category_id'] ?? $kelas['category_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="harga" class="form-label">Harga (Rp, 0 = gratis)</label>
                    <input type="number" id="harga" name="harga" class="form-control" min="0" step="1000"
                        value="<?= htmlspecialchars($_POST['harga'] ?? $kelas['harga'] ?? '0') ?>">
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="jadwal" class="form-label">Jadwal</label>
                    <input type="text" id="jadwal" name="jadwal" class="form-control"
                        value="<?= htmlspecialchars($_POST['jadwal'] ?? $kelas['jadwal'] ?? '') ?>"
                        placeholder="Contoh: Sabtu 09.00-11.00">
                </div>
                <div class="col-md-6">
                    <label for="durasi" class="form-label">Durasi</label>
                    <input type="text" id="durasi" name="durasi" class="form-control"
                        value="<?= htmlspecialchars($_POST['durasi'] ?? $kelas['durasi'] ?? '') ?>"
                        placeholder="Contoh: 8 pertemuan">
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="kuota" class="form-label">Kuota Siswa</label>
                    <input type="number" id="kuota" name="kuota" class="form-control" min="1" required
                        value="<?= htmlspecialchars($_POST['kuota'] ?? $kelas['kuota'] ?? '20') ?>">
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="aktif" <?= (($_POST['status'] ?? $kelas['status'] ?? 'aktif') === 'aktif') ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= (($_POST['status'] ?? $kelas['status'] ?? '') === 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label for="imgPreviewInput" class="form-label">Thumbnail</label>
                <?php if (!empty($kelas['thumbnail'])): ?>
                    <div class="mb-2">
                        <img id="imgPreview" src="<?= thumbnail_url($kelas['thumbnail']) ?>"
                             alt="Thumbnail saat ini" width="200" height="113"
                             style="border-radius:8px;object-fit:cover">
                    </div>
                <?php else: ?>
                    <img id="imgPreview" src="" alt="" width="200" height="113"
                         style="border-radius:8px;object-fit:cover;display:none">
                <?php endif; ?>
                <input type="file" id="imgPreviewInput" name="thumbnail" class="form-control"
                       accept=".jpg,.jpeg,.png" aria-describedby="thumbnailHelp">
                <div id="thumbnailHelp" class="form-text">JPG/PNG, maks 2 MB.</div>
            </div>
            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-teal"><?= $edit_id ? 'Simpan Perubahan' : 'Buat Kelas' ?></button>
                <a href="<?= base_url('mentor/dashboard.php') ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
