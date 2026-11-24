<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

// Aksi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $uid  = (int)($_POST['uid'] ?? 0);

    if ($aksi === 'ubah_role') {
        $role = in_array($_POST['role'], ['admin','mentor','siswa']) ? $_POST['role'] : null;
        if ($role) {
            $stmt = $conn->prepare('UPDATE users SET role=? WHERE id=?');
            $stmt->bind_param('si', $role, $uid);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Peran pengguna diperbarui.');
        }
    } elseif ($aksi === 'hapus') {
        // Jangan hapus diri sendiri
        if ($uid === (int)$_SESSION['user_id']) {
            set_flash('danger', 'Tidak bisa menghapus akun sendiri.');
        } else {
            $stmt = $conn->prepare('DELETE FROM users WHERE id=?');
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Pengguna dihapus.');
        }
    }
    header('Location: ' . base_url('admin/users.php'));
    exit;
}

$users = $conn->query("SELECT id, nama, email, role, created_at FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$page_title = 'Kelola Pengguna';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <h1 class="h4 fw-800 mb-4">Kelola Pengguna</h1>
    <?= flash_html() ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Nama</th><th>Email</th><th>Peran</th><th>Bergabung</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="fw-600"><?= htmlspecialchars($u['nama']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <form method="post" class="d-inline-flex gap-2 align-items-center">
                                <input type="hidden" name="aksi" value="ubah_role">
                                <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                                <select name="role" class="form-select form-select-sm"
                                    style="width:120px" aria-label="Peran <?= htmlspecialchars($u['nama']) ?>">
                                    <option value="siswa"  <?= $u['role']==='siswa'  ? 'selected':'' ?>>Siswa</option>
                                    <option value="mentor" <?= $u['role']==='mentor' ? 'selected':'' ?>>Mentor</option>
                                    <option value="admin"  <?= $u['role']==='admin'  ? 'selected':'' ?>>Admin</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-teal">Simpan</button>
                            </form>
                        </td>
                        <td class="text-muted small"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                        data-confirm="Hapus pengguna <?= htmlspecialchars($u['nama']) ?>?">
                                        Hapus
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">Kamu</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
