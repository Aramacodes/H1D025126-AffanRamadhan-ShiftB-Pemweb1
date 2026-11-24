<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$uid   = $_SESSION['user_id'];
$error = '';

$stmt = $conn->prepare('SELECT * FROM users WHERE id=? LIMIT 1');
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');
    $keahlian = trim($_POST['keahlian'] ?? '');
    $pw_baru  = $_POST['pw_baru'] ?? '';
    $pw_lama  = $_POST['pw_lama'] ?? '';

    $errors = [];
    if (!$nama) $errors[] = 'Nama wajib diisi.';

    // Ganti password opsional
    if ($pw_baru) {
        if (!password_verify($pw_lama, $user['password'])) $errors[] = 'Password lama salah.';
        elseif (strlen($pw_baru) < 8) $errors[] = 'Password baru minimal 8 karakter.';
    }

    // Upload foto profil
    $foto = $user['foto'];
    if (!empty($_FILES['foto']['name'])) {
        $file = $_FILES['foto'];
        $allowed_ext  = ['jpg','jpeg','png'];
        $allowed_mime = ['image/jpeg','image/png'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mime)) $errors[] = 'Foto harus JPG/PNG.';
        elseif ($file['size'] > 2 * 1024 * 1024) $errors[] = 'Foto maksimal 2 MB.';
        else {
            $dir = __DIR__ . '/uploads/foto/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $newname = bin2hex(random_bytes(12)) . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $dir . $newname);
            $foto = $newname;
        }
    }

    if (empty($errors)) {
        if ($pw_baru) {
            $hash = password_hash($pw_baru, PASSWORD_BCRYPT);
            $stmt = $conn->prepare('UPDATE users SET nama=?,bio=?,keahlian=?,foto=?,password=? WHERE id=?');
            $stmt->bind_param('sssssi', $nama, $bio, $keahlian, $foto, $hash, $uid);
        } else {
            $stmt = $conn->prepare('UPDATE users SET nama=?,bio=?,keahlian=?,foto=? WHERE id=?');
            $stmt->bind_param('ssssi', $nama, $bio, $keahlian, $foto, $uid);
        }
        $stmt->execute();
        $stmt->close();
        $_SESSION['nama'] = $nama;
        $_SESSION['foto'] = $foto;
        set_flash('success', 'Profil berhasil disimpan.');
        header('Location: ' . base_url('profil.php'));
        exit;
    } else {
        $error = implode(' ', $errors);
    }
}

$page_title = 'Profil Saya';
include __DIR__ . '/includes/header.php';
?>
<div class="container" style="max-width:600px">
    <h1 class="h4 fw-800 mb-4">Profil Saya</h1>
    <?= flash_html() ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="bg-white border rounded p-4" style="border-radius:12px">
        <form method="post" enctype="multipart/form-data">
            <div class="text-center mb-4">
                <img src="<?= foto_url($user['foto']) ?>"
                     alt="Foto profil" id="imgPreview"
                     width="80" height="80"
                     style="border-radius:50%;object-fit:cover;border:3px solid var(--teal-light)">
                <div class="mt-2">
                    <label for="imgPreviewInput" class="btn btn-sm btn-outline-teal">Ganti Foto</label>
                    <input type="file" id="imgPreviewInput" name="foto" class="d-none"
                           accept=".jpg,.jpeg,.png" aria-label="Upload foto profil">
                </div>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="form-control"
                    value="<?= htmlspecialchars($user['nama']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email_ro" class="form-label">Email</label>
                <input type="email" id="email_ro" class="form-control"
                    value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="bio" class="form-label">Bio</label>
                <textarea id="bio" name="bio" class="form-control" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>
            <?php if ($user['role'] === 'mentor'): ?>
                <div class="mb-3">
                    <label for="keahlian" class="form-label">Keahlian</label>
                    <input type="text" id="keahlian" name="keahlian" class="form-control"
                        value="<?= htmlspecialchars($user['keahlian'] ?? '') ?>"
                        placeholder="Contoh: Python, Web Development">
                </div>
            <?php endif; ?>
            <hr>
            <h2 class="h6 fw-700 mb-3">Ganti Password <span class="text-muted fw-400">(opsional)</span></h2>
            <div class="mb-3">
                <label for="pw_lama" class="form-label">Password Lama</label>
                <input type="password" id="pw_lama" name="pw_lama" class="form-control" autocomplete="current-password">
            </div>
            <div class="mb-4">
                <label for="pw_baru" class="form-label">Password Baru</label>
                <input type="password" id="pw_baru" name="pw_baru" class="form-control"
                    autocomplete="new-password" minlength="8">
            </div>
            <button type="submit" class="btn btn-teal">Simpan Perubahan</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
