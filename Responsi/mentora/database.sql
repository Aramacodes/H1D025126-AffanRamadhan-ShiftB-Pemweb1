-- Mentora Database Schema & Seed
-- Import ke MySQL: CREATE DATABASE mentora; USE mentora; SOURCE database.sql;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','mentor','siswa') NOT NULL DEFAULT 'siswa',
    bio TEXT,
    keahlian VARCHAR(255),
    foto VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mentor_id INT NOT NULL,
    category_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10,2) NOT NULL DEFAULT 0,
    jadwal VARCHAR(100),
    durasi VARCHAR(50),
    kuota INT NOT NULL DEFAULT 20,
    thumbnail VARCHAR(255),
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cart (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('menunggu_bayar','menunggu_verifikasi','aktif','selesai','ditolak','dibatalkan') NOT NULL DEFAULT 'menunggu_bayar',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    course_id INT NOT NULL,
    harga DECIMAL(10,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    bukti VARCHAR(255),
    status ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
    catatan TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_review (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================== SEED DATA =====================

-- Passwords: semua pakai "password123"
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi (password)
-- Gunakan hash yang valid untuk password_verify PHP

INSERT INTO users (nama, email, password, role, bio, keahlian) VALUES
('Admin Mentora', 'admin@mentora.id', '$2y$10$OSMR63xg8dUbkwlh5Obdge4g1rzJlEqp7RDNNPNDDSAtBPal0Yvp6', 'admin', 'Administrator platform Mentora.', NULL),
('Budi Santoso', 'budi@mentora.id', '$2y$10$OSMR63xg8dUbkwlh5Obdge4g1rzJlEqp7RDNNPNDDSAtBPal0Yvp6', 'mentor', 'Mahasiswa Teknik Informatika semester 6. Berpengalaman mengajar pemrograman web dan Python.', 'Python, JavaScript, Web Development'),
('Sari Dewi', 'sari@mentora.id', '$2y$10$OSMR63xg8dUbkwlh5Obdge4g1rzJlEqp7RDNNPNDDSAtBPal0Yvp6', 'mentor', 'Guru Matematika SMA dengan pengalaman 5 tahun. Spesialis persiapan ujian nasional dan SBMPTN.', 'Matematika, Statistika, Kalkulus'),
('Andi Pratama', 'andi@mentora.id', '$2y$10$OSMR63xg8dUbkwlh5Obdge4g1rzJlEqp7RDNNPNDDSAtBPal0Yvp6', 'siswa', NULL, NULL),
('Rina Kartika', 'rina@mentora.id', '$2y$10$OSMR63xg8dUbkwlh5Obdge4g1rzJlEqp7RDNNPNDDSAtBPal0Yvp6', 'siswa', NULL, NULL),
('Doni Firmansyah', 'doni@mentora.id', '$2y$10$OSMR63xg8dUbkwlh5Obdge4g1rzJlEqp7RDNNPNDDSAtBPal0Yvp6', 'siswa', NULL, NULL);

-- Password untuk semua akun: password123

INSERT INTO categories (nama) VALUES
('Matematika'),
('Pemrograman'),
('Bahasa Inggris'),
('Desain'),
('Fisika'),
('Kimia');

INSERT INTO courses (mentor_id, category_id, judul, deskripsi, harga, jadwal, durasi, kuota, status) VALUES
(2, 2, 'Belajar Python dari Nol', 'Kursus Python untuk pemula. Mulai dari variabel, loop, fungsi, hingga membuat program sederhana. Cocok untuk siswa SMA dan mahasiswa semester awal.', 50000, 'Sabtu & Minggu, 09.00-11.00', '8 pertemuan', 20, 'aktif'),
(2, 2, 'Web Development dengan HTML CSS JS', 'Buat website dari nol menggunakan HTML, CSS, dan JavaScript. Materi meliputi layout, responsif, dan interaktivitas dasar.', 75000, 'Senin & Rabu, 19.00-21.00', '10 pertemuan', 15, 'aktif'),
(2, 2, 'Pemrograman Dasar untuk SMA', 'Pengantar logika pemrograman dan algoritma untuk siswa SMA. Menggunakan pseudocode dan Python sebagai bahasa pengantar.', 0, 'Jumat, 15.00-17.00', '6 pertemuan', 30, 'aktif'),
(3, 1, 'Matematika SBMPTN Intensif', 'Persiapan ujian SBMPTN fokus matematika. Latihan soal dan pembahasan dari tahun-tahun sebelumnya.', 100000, 'Selasa & Kamis, 16.00-18.00', '12 pertemuan', 10, 'aktif'),
(3, 1, 'Kalkulus untuk Mahasiswa', 'Materi kalkulus diferensial dan integral untuk mahasiswa semester 1-2. Dilengkapi latihan soal dan kuis mingguan.', 60000, 'Sabtu, 10.00-12.00', '8 pertemuan', 20, 'aktif'),
(3, 1, 'Matematika Dasar SMP', 'Penguatan konsep matematika SMP. Materi: bilangan, aljabar, geometri, dan statistika dasar. Gratis untuk semua siswa.', 0, 'Minggu, 09.00-11.00', '8 pertemuan', 25, 'aktif');
