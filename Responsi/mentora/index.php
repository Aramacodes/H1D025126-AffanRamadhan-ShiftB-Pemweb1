<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/landing_data.php';

$data = get_landing_data($conn);
$stats = $data['stats'];
$kelas_unggulan = $data['kelas_unggulan'];
$kategori = $data['kategori'];
$ulasan = $data['ulasan'];

// Deteksi status login untuk navbar
$is_logged_in = !empty($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? 'siswa';
$dashboard_url = match($user_role) {
    'admin'  => base_url('admin/dashboard.php'),
    'mentor' => base_url('mentor/dashboard.php'),
    default  => base_url('home.php'),
};
$dashboard_label = $user_role === 'siswa' ? 'Ke Beranda Belajar' : 'Ke Dashboard';

$page_title = 'Mentora - Marketplace Kelas Belajar & Mentoring Terjangkau';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/landing.css') ?>">
</head>
<body>

<!-- 1. Navbar -->
<header>
<nav class="navbar navbar-expand-lg navbar-light border-bottom sticky-top landing-nav" aria-label="Navigasi landing page">
    <div class="container">
        <a class="navbar-brand fw-800 text-teal" href="<?= base_url('index.php') ?>">
            <span class="brand-icon" aria-hidden="true">M</span>entora
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNavContent"
            aria-controls="landingNavContent" aria-expanded="false" aria-label="Buka menu navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="landingNavContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link" href="#tentang">Tentang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#cara-kerja">Cara Kerja</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#kelas">Kelas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#faq">FAQ</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if ($is_logged_in): ?>
                    <a href="<?= $dashboard_url ?>" class="btn btn-teal">
                        <i class="bi bi-house-door me-1" aria-hidden="true"></i><?= $dashboard_label ?>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('auth/login.php') ?>" class="btn btn-outline-teal">Masuk</a>
                    <a href="<?= base_url('auth/register.php') ?>" class="btn btn-teal">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
</header>

<main>
    <!-- 2. Hero Section -->
    <section class="landing-hero" aria-labelledby="hero-title">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="badge bg-warning text-dark fw-700 px-3 py-2 mb-3 rounded-pill">Platform Edukasi Terjangkau</span>
                    <h1 id="hero-title" class="mb-3">Belajar lebih mudah, tanpa biaya yang memberatkan.</h1>
                    <p class="lead mb-4">Mentora menghubungkan kamu dengan mentor mahasiswa dan guru lewat kelas yang murah, bahkan gratis.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <?php if ($is_logged_in): ?>
                            <a href="<?= $dashboard_url ?>" class="btn btn-amber btn-lg fw-700">Lanjutkan Belajar</a>
                            <a href="<?= base_url('kelas.php') ?>" class="btn btn-outline-light btn-lg">Cari Kelas</a>
                        <?php else: ?>
                            <a href="<?= base_url('auth/register.php') ?>" class="btn btn-amber btn-lg fw-700">Mulai Belajar</a>
                            <a href="#kelas" class="btn btn-outline-light btn-lg">Lihat Kelas</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-img-wrap">
                        <img src="<?= base_url('assets/img/hero-illustration.svg') ?>"
                             alt="Ilustrasi interaksi belajar di Mentora"
                             width="540" height="380"
                             fetchpriority="high">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Statistik Dinamis -->
    <section class="py-5 bg-white border-bottom" aria-labelledby="stats-heading">
        <div class="container">
            <h2 id="stats-heading" class="visually-hidden">Statistik Mentora</h2>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-num"><?= $stats['kelas'] ?></div>
                        <div class="stat-label">Kelas Aktif</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-num"><?= $stats['mentor'] ?></div>
                        <div class="stat-label">Mentor Berpengalaman</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-num"><?= $stats['siswa'] ?></div>
                        <div class="stat-label">Siswa Terdaftar</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-num" style="color:var(--amber)"><?= $stats['gratis'] ?></div>
                        <div class="stat-label">Kelas Gratis Tersedia</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Masalah dan Solusi -->
    <section id="tentang" class="py-5" aria-labelledby="masalah-solusi-heading">
        <div class="container">
            <div class="text-center max-w-700 mx-auto mb-5">
                <h2 id="masalah-solusi-heading" class="section-heading mb-2">Mengapa Mentora Hadir?</h2>
                <p class="text-muted">Mewujudkan pemerataan akses pendidikan berkualitas sesuai SDG 4 dan SDG 10.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card-problem">
                        <span class="badge-title mb-3 d-inline-block">Tantangan yang Dihadapi</span>
                        <h3 class="h5 fw-800 text-danger mb-3">Akses Bimbingan Belajar yang Terbatas</h3>
                        <p class="text-muted mb-3">Biaya les tambahan dan kursus privat sering kali terlampau mahal untuk sebagian besar keluarga dan mahasiswa.</p>
                        <ul class="list-unstyled mb-0 text-muted small">
                            <li class="mb-2"><i class="bi bi-x-circle text-danger me-2" aria-hidden="true"></i>Biaya kursus konvensional jutaan rupiah per paket.</li>
                            <li class="mb-2"><i class="bi bi-x-circle text-danger me-2" aria-hidden="true"></i>Peluang belajar berkualitas belum tersebar merata.</li>
                            <li><i class="bi bi-x-circle text-danger me-2" aria-hidden="true"></i>Mahasiswa pintar kesulitan menyalurkan keahlian mengajar mereka.</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-solution">
                        <span class="badge-title mb-3 d-inline-block">Solusi dari Mentora</span>
                        <h3 class="h5 fw-800 text-teal mb-3">Mentoring Terjangkau untuk Semua</h3>
                        <p class="text-muted mb-3">Mentora memfasilitasi kelas murah dan gratis yang diampu oleh mahasiswa berprestasi serta guru berpengalaman.</p>
                        <ul class="list-unstyled mb-0 text-muted small">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-teal me-2" aria-hidden="true"></i>Biaya ramah kantong, mulai dari gratis hingga puluhan ribu rupiah.</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-teal me-2" aria-hidden="true"></i>Materi langsung terfokus pada kebutuhan materi siswa.</li>
                            <li><i class="bi bi-check-circle-fill text-teal me-2" aria-hidden="true"></i>Membuka peluang bagi mentor untuk berbagi dan mendapat penghasilan.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Cara Kerja -->
    <section id="cara-kerja" class="py-5 bg-white border-top border-bottom" aria-labelledby="cara-kerja-heading">
        <div class="container">
            <div class="text-center mb-5">
                <h2 id="cara-kerja-heading" class="section-heading mb-2">Cara Kerja Mentora</h2>
                <p class="text-muted">Langkah mudah untuk mulai belajar hal baru hari ini.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-num-circle">1</div>
                        <h3 class="fs-6 fw-700 mb-2">Daftar Akun</h3>
                        <p class="text-muted small mb-0">Buat akun siswa secara gratis dalam hitungan detik tanpa syarat rumit.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-num-circle">2</div>
                        <h3 class="fs-6 fw-700 mb-2">Pilih Kelas</h3>
                        <p class="text-muted small mb-0">Cari materi yang kamu butuhkan, filter kategori, dan cek profil mentor.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-num-circle">3</div>
                        <h3 class="fs-6 fw-700 mb-2">Bayar & Verifikasi</h3>
                        <p class="text-muted small mb-0">Checkout lalu unggah bukti transfer. Untuk kelas gratis langsung aktif seketika.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="step-card">
                        <div class="step-num-circle">4</div>
                        <h3 class="fs-6 fw-700 mb-2">Belajar & Ulas</h3>
                        <p class="text-muted small mb-0">Ikuti sesi pembelajaran sesuai jadwal dan beri ulasan setelah kelas berakhir.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Kelas Unggulan -->
    <section id="kelas" class="py-5" aria-labelledby="kelas-heading">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
                <div>
                    <h2 id="kelas-heading" class="section-heading mb-1">Kelas Unggulan</h2>
                    <p class="text-muted mb-0">Pilihan kelas terfavorit dari mentor terbaik Mentora.</p>
                </div>
                <a href="<?= base_url('kelas.php') ?>" class="btn btn-outline-teal">Lihat Semua Kelas</a>
            </div>

            <?php if (empty($kelas_unggulan)): ?>
                <div class="empty-state">
                    <i class="bi bi-journal-text" aria-hidden="true"></i>
                    <p class="fs-6 fw-600">Belum ada kelas aktif saat ini.</p>
                    <p class="small">Kunjungi kembali nanti untuk melihat kelas terbaru.</p>
                </div>
            <?php else: ?>
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
            <?php endif; ?>
        </div>
    </section>

    <!-- 7. Kategori -->
    <section class="py-5 bg-white border-top border-bottom" aria-labelledby="kategori-heading">
        <div class="container text-center">
            <h2 id="kategori-heading" class="section-heading mb-2">Kategori Pilihan</h2>
            <p class="text-muted mb-4">Jelajahi berbagai bidang studi yang sesuai dengan minat belajarmu.</p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <?php foreach ($kategori as $cat): ?>
                    <a href="<?= base_url('kelas.php?kategori=' . $cat['id']) ?>" class="cat-chip">
                        <span><?= htmlspecialchars($cat['nama']) ?></span>
                        <span class="cat-chip-count"><?= $cat['jml_kelas'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 8. Keunggulan Fitur -->
    <section class="py-5" aria-labelledby="keunggulan-heading">
        <div class="container">
            <div class="text-center mb-5">
                <h2 id="keunggulan-heading" class="section-heading mb-2">Keunggulan Mentora</h2>
                <p class="text-muted">Kelebihan yang kami hadirkan untuk pengalaman belajarmu.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-wallet2" aria-hidden="true"></i></div>
                        <h3 class="fs-6 fw-700 mb-2">Harga Sangat Terjangkau</h3>
                        <p class="text-muted small mb-0">Biaya kelas dirancang pas untuk kantong pelajar dan mahasiswa tanpa membebani keuangan keluarga.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-gift" aria-hidden="true"></i></div>
                        <h3 class="fs-6 fw-700 mb-2">Opsi Kelas Gratis</h3>
                        <p class="text-muted small mb-0">Tersedia banyak sesi mentoring gratis sebagai bentuk dedikasi mentor untuk pemerataan pendidikan.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-patch-check" aria-hidden="true"></i></div>
                        <h3 class="fs-6 fw-700 mb-2">Mentor Kompeten</h3>
                        <p class="text-muted small mb-0">Diampu oleh mahasiswa berprestasi dan guru yang memiliki pemahaman kuat di bidang masing-masing.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-calendar-event" aria-hidden="true"></i></div>
                        <h3 class="fs-6 fw-700 mb-2">Jadwal Fleksibel</h3>
                        <p class="text-muted small mb-0">Pilihan jadwal sore dan akhir pekan yang tidak bertabrakan dengan jam belajar formal sekolah.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-shield-check" aria-hidden="true"></i></div>
                        <h3 class="fs-6 fw-700 mb-2">Verifikasi Transparan</h3>
                        <p class="text-muted small mb-0">Setiap proses pembayaran diperiksa secara manual oleh admin agar aman dan akurat.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon-box"><i class="bi bi-chat-square-quote" aria-hidden="true"></i></div>
                        <h3 class="fs-6 fw-700 mb-2">Ulasan Jujur</h3>
                        <p class="text-muted small mb-0">Hanya siswa yang telah menyelesaikan kelas yang dapat memberikan testimoni dan penilaian bintang.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 9. Ulasan Siswa (Muncul jika ada) -->
    <?php if (!empty($ulasan)): ?>
    <section class="py-5 bg-white border-top border-bottom" aria-labelledby="ulasan-heading">
        <div class="container">
            <div class="text-center mb-5">
                <h2 id="ulasan-heading" class="section-heading mb-2">Kata Siswa Kami</h2>
                <p class="text-muted">Pengalaman nyata dari siswa yang telah mengikuti kelas mentoring.</p>
            </div>
            <div class="row g-4">
                <?php foreach ($ulasan as $u): ?>
                <div class="col-md-4">
                    <div class="review-card">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <img src="<?= foto_url($u['siswa_foto']) ?>"
                                 alt="Foto profil <?= htmlspecialchars($u['siswa_nama']) ?>"
                                 width="40" height="40"
                                 style="border-radius:50%;object-fit:cover"
                                 loading="lazy">
                            <div>
                                <div class="fw-700 small"><?= htmlspecialchars($u['siswa_nama']) ?></div>
                                <div class="d-flex gap-1"><?= rating_stars($u['rating']) ?></div>
                            </div>
                        </div>
                        <p class="text-muted small mb-3 flex-grow-1">"<?= htmlspecialchars($u['komentar']) ?>"</p>
                        <div class="small pt-2 border-top text-truncate">
                            <span class="text-muted">Kelas:</span>
                            <a href="<?= base_url('kelas-detail.php?id=' . $u['kelas_id']) ?>" class="text-teal fw-600 text-decoration-none">
                                <?= htmlspecialchars($u['kelas_judul']) ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 10. Ajakan Jadi Mentor -->
    <section class="py-5" aria-labelledby="mentor-banner-heading">
        <div class="container">
            <div class="mentor-banner">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <span class="badge bg-warning text-dark fw-700 px-3 py-1 mb-2 rounded-pill">Peluang Pengajar</span>
                        <h2 id="mentor-banner-heading" class="h3 fw-800 mb-2">Punya Keahlian? Jadilah Mentor di Mentora.</h2>
                        <p class="mb-0 text-white-50">Bantu siswa lain memahami pelajaran favoritmu, dapatkan pengalaman mengajar, dan bangun portofolio yang berharga.</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="<?= base_url('auth/register.php') ?>" class="btn btn-amber btn-lg fw-700">Daftar Sebagai Mentor</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 11. FAQ -->
    <section id="faq" class="py-5 bg-white border-top border-bottom" aria-labelledby="faq-heading">
        <div class="container" style="max-width:820px">
            <div class="text-center mb-5">
                <h2 id="faq-heading" class="section-heading mb-2">Pertanyaan Umum (FAQ)</h2>
                <p class="text-muted">Semua jawaban yang sering ditanyakan seputar layanan Mentora.</p>
            </div>
            <div class="accordion faq-accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h3 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Bagaimana cara mendaftar kelas di Mentora?
                        </button>
                    </h3>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Kamu cukup mendaftar akun sebagai siswa, lalu pilih kelas yang diminati dari katalog. Klik tombol "Tambah ke Keranjang", lanjut ke checkout, dan lakukan konfirmasi pesanan.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Bagaimana metode pembayaran dan proses verifikasinya?
                        </button>
                    </h3>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Pembayaran dilakukan melalui transfer bank ke nomor rekening resmi Mentora yang tertera saat checkout. Setelah transfer, kamu wajib mengunggah foto bukti pembayaran pada halaman detail pesanan.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Apakah kelas gratis benar-benar tidak dipungut biaya?
                        </button>
                    </h3>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Benar, 100% gratis. Begitu kamu melakukan konfirmasi pesanan untuk kelas dengan harga Rp 0, pesananmu akan langsung berstatus aktif tanpa perlu mengunggah bukti pembayaran.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            Bagaimana cara menjadi mentor di Mentora?
                        </button>
                    </h3>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Pilih peran "Mentor" saat mendaftar akun baru. Setelah masuk, kamu dapat melengkapi profil keahlian dan langsung mulai membuat kelas pada Dashboard Mentor.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header" id="headingFive">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                            Berapa lama waktu verifikasi pembayaran oleh admin?
                        </button>
                    </h3>
                    <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Admin memeriksa bukti pembayaran maksimal dalam 1x24 jam kerja. Setelah disetujui, kuota kelas akan terkunci dan status pesanan berubah menjadi aktif.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h3 class="accordion-header" id="headingSix">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                            Apakah pesanan yang sudah dibuat bisa dibatalkan?
                        </button>
                    </h3>
                    <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Pesanan yang belum dibayar dapat dibatalkan melalui admin atau akan otomatis hangus jika bukti transfer ditolak karena ketidaksesuaian data.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 12. Final Call to Action -->
    <section class="py-5" aria-labelledby="cta-heading">
        <div class="container" style="max-width:820px">
            <div class="final-cta">
                <h2 id="cta-heading" class="h3 fw-800 mb-3">Siap Tingkatkan Kemampuan Belajarmu?</h2>
                <p class="text-muted mb-4">Bergabung sekarang bersama ribuan siswa lain dan temukan mentor terbaik untuk mendukung masa depanmu.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?= base_url('auth/register.php') ?>" class="btn btn-teal btn-lg px-4">Daftar Sekarang</a>
                    <a href="<?= base_url('kelas.php') ?>" class="btn btn-outline-teal btn-lg px-4">Eksplorasi Kelas</a>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- 13. Footer -->
<footer class="footer bg-dark-teal text-white mt-4 py-5">
    <div class="container">
        <div class="row g-4 align-items-center mb-4">
            <div class="col-md-6">
                <span class="fw-800 fs-4"><span class="brand-icon" aria-hidden="true">M</span>entora</span>
                <p class="mt-2 mb-0 text-white-50 small">
                    Platform marketplace kelas belajar dan mentoring terjangkau. Mendukung Tujuan Pembangunan Berkelanjutan SDG 4 (Pendidikan Berkualitas) dan SDG 10 (Mengurangi Ketimpangan).
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item me-3"><a href="#tentang" class="text-white-50 text-decoration-none small">Tentang</a></li>
                    <li class="list-inline-item me-3"><a href="#cara-kerja" class="text-white-50 text-decoration-none small">Cara Kerja</a></li>
                    <li class="list-inline-item me-3"><a href="#kelas" class="text-white-50 text-decoration-none small">Kelas</a></li>
                    <li class="list-inline-item me-3"><a href="#faq" class="text-white-50 text-decoration-none small">FAQ</a></li>
                    <li class="list-inline-item"><a href="<?= base_url('kelas.php') ?>" class="text-white-50 text-decoration-none small">Katalog</a></li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary opacity-25">
        <div class="text-center text-white-50 small">
            &copy; <?= date('Y') ?> Mentora. Dibuat untuk akses edukasi yang merata bagi semua.
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="<?= base_url('assets/js/landing.js') ?>" defer></script>
</body>
</html>
