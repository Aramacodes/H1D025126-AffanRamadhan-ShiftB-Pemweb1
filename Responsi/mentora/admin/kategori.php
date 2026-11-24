<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');

    if ($aksi === 'tambah') {
        if (!$nama) { $error = 'Nama kategori wajib diisi.'; }
        else {
            $stmt = $conn->prepare('INSERT INTO categories (nama) VALUES (?)');
            $stmt->bind_param('s', $nama);
            if ($stmt->execute()) set_flash('success', 'Kategori berhasil ditambahkan.');
            else set_flash('danger', 'Nama kategori sudah ada.');
            $stmt->close();
            header('Location: ' . base_url('admin/kategori.php'));
            exit;
        }
    } elseif ($aksi === 'edit') {
        if (!$nama) { $error = 'Nama kategori wajib diisi.'; }
        else {
            $stmt = $conn->prepare('UPDATE categories SET nama=? WHERE id=?');
            $stmt->bind_param('si', $nama, $id);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Kategori diperbarui.');
            header('Location: ' . base_url('admin/kategori.php'));
            exit;
        }
    } elseif ($aksi === 'hapus') {
        // Cek apakah masih dipakai
        $stmt = $conn->prepare('SELECT COUNT(*) FROM courses WHERE category_id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $pakai = $stmt->get_result()->fetch_row()[0];
        $stmt->close();
        if ($pakai > 0) {
            set_flash('danger', 'Kategori masih dipakai oleh ' . $pakai . ' kelas, tidak bisa dihapus.');
        } else {
            $stmt = $conn->prepare('DELETE FROM categories WHERE id=?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Kategori dihapus.');
        }
        header('Location: ' . base_url('admin/kategori.php'));
        exit;
    }
}

$edit_item = null;
if (!empty($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $stmt = $conn->prepare('SELECT * FROM categories WHERE id=?');
    $stmt->bind_param('i', $eid);
    $stmt->execute();
    $edit_item = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$categories = $conn->query('SELECT c.id, c.nama, COUNT(cs.id) AS jml FROM categories c LEFT JOIN courses cs ON cs.category_id=c.id GROUP BY c.id ORDER BY c.nama')->fetch_all(MYSQLI_ASSOC);
$page_title = 'Kelola Kategori';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <h1 class="h4 fw-800 mb-4">Kelola Kategori</h1>
    <?= flash_html() ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="bg-white border rounded p-4" style="border-radius:12px">
                <h2 class="h6 fw-700 mb-3"><?= $edit_item ? 'Edit Kategori' : 'Tambah Kategori' ?></h2>
                <form method="post">
                    <input type="hidden" name="aksi" value="<?= $edit_item ? 'edit' : 'tambah' ?>">
                    <?php if ($edit_item): ?>
                        <input type="hidden" name="id" value="<?= $edit_item['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Kategori</label>
                        <input type="text" id="nama" name="nama" class="form-control" required
                            value="<?= htmlspecialchars($edit_item['nama'] ?? '') ?>"
                            placeholder="Contoh: Matematika">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-teal"><?= $edit_item ? 'Simpan' : 'Tambah' ?></button>
                        <?php if ($edit_item): ?>
                            <a href="<?= base_url('admin/kategori.php') ?>" class="btn btn-outline-secondary">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr><th>Nama</th><th>Jumlah Kelas</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td class="fw-600"><?= htmlspecialchars($c['nama']) ?></td>
                                <td class="text-muted"><?= $c['jml'] ?> kelas</td>
                                <td>
                                    <a href="<?= base_url('admin/kategori.php?edit=' . $c['id']) ?>"
                                       class="btn btn-sm btn-outline-teal">Edit</a>
                                    <form method="post" class="d-inline ms-1">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            data-confirm="Hapus kategori <?= htmlspecialchars($c['nama']) ?>?">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Belum ada kategori.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
