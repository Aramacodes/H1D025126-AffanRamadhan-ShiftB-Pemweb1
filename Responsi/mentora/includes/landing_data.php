<?php
// includes/landing_data.php - Mengambil data dinamis untuk landing page

function get_landing_data($conn) {
    // 1. Statistik
    $total_kelas  = (int)($conn->query('SELECT COUNT(*) FROM courses WHERE status="aktif"')->fetch_row()[0] ?? 0);
    $total_mentor = (int)($conn->query('SELECT COUNT(*) FROM users WHERE role="mentor"')->fetch_row()[0] ?? 0);
    $total_siswa  = (int)($conn->query('SELECT COUNT(*) FROM users WHERE role="siswa"')->fetch_row()[0] ?? 0);
    $total_gratis = (int)($conn->query('SELECT COUNT(*) FROM courses WHERE status="aktif" AND harga = 0')->fetch_row()[0] ?? 0);

    // 2. Kelas Unggulan (maks 6, status aktif, urut rating lalu terbaru)
    $stmt = $conn->prepare('
        SELECT c.id, c.judul, c.harga, c.kuota, c.thumbnail,
               cat.nama AS kategori,
               u.nama AS mentor_nama,
               COALESCE(AVG(r.rating), 0) AS avg_rating,
               COUNT(DISTINCT r.id) AS jml_review
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

    // 3. Seluruh Kategori
    $stmt = $conn->prepare('
        SELECT c.id, c.nama, COUNT(cs.id) AS jml_kelas
        FROM categories c
        LEFT JOIN courses cs ON cs.category_id = c.id AND cs.status = "aktif"
        GROUP BY c.id
        ORDER BY c.nama ASC
    ');
    $stmt->execute();
    $kategori_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // 4. Ulasan Siswa (maks 3 terbaru dengan rating tinggi >= 4)
    $stmt = $conn->prepare('
        SELECT r.rating, r.komentar, r.created_at,
               u.nama AS siswa_nama, u.foto AS siswa_foto,
               c.judul AS kelas_judul, c.id AS kelas_id
        FROM reviews r
        JOIN users u ON u.id = r.user_id
        JOIN courses c ON c.id = r.course_id
        WHERE r.rating >= 4
        ORDER BY r.created_at DESC
        LIMIT 3
    ');
    $stmt->execute();
    $ulasan_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return [
        'stats' => [
            'kelas'  => $total_kelas,
            'mentor' => $total_mentor,
            'siswa'  => $total_siswa,
            'gratis' => $total_gratis,
        ],
        'kelas_unggulan' => $kelas_unggulan,
        'kategori'       => $kategori_list,
        'ulasan'         => $ulasan_list,
    ];
}
