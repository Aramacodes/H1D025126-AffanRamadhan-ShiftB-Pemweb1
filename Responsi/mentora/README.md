# Mentora

Platform marketplace kelas belajar dan mentoring dengan harga terjangkau untuk siswa dan mahasiswa.

## Latar Belakang & Tujuan
Akses pendidikan tambahan sering kali mahal dan tidak merata. Mentora menghubungkan pengajar independen (guru dan mahasiswa berprestasi) dengan siswa yang membutuhkan bimbingan dengan biaya terjangkau atau bahkan gratis.

Proyek ini mendukung pencapaian Tujuan Pembangunan Berkelanjutan (SDGs):
- **SDG 4 (Quality Education):** Memastikan pendidikan yang inklusif, berkualitas, dan setara.
- **SDG 10 (Reduced Inequalities):** Mengurangi ketimpangan akses terhadap materi belajar tambahan.

---

## Fitur & Modul

1. **Authentication:** Registrasi akun siswa/mentor, login, logout, proteksi password (bcrypt).
2. **User Management:** Kelola pengguna, ubah peran (admin/mentor/siswa), edit profil, ganti password, upload foto profil.
3. **Kategori Mapel:** Kelola kategori (tambah/edit/hapus). Kategori yang masih dipakai kelas diproteksi agar tidak bisa dihapus.
4. **Manajemen Kelas:** Mentor dapat menambah, mengedit, dan menghapus kelasnya. Siswa/pengunjung dapat mencari kelas berdasarkan judul, filter kategori, rentang harga, dan sorting harga/rating.
5. **Keranjang Belanja:** Siswa dapat menambahkan kelas, melihat total, dan menghapus kelas. Kelas yang sudah dibeli atau penuh dicegah.
6. **Checkout:** Konfirmasi pesanan, kelas gratis otomatis langsung aktif tanpa pembayaran, kelas berbayar menunggu upload bukti.
7. **Manajemen Pesanan:** Riwayat pesanan, status pesanan (`menunggu_bayar`, `menunggu_verifikasi`, `aktif`, `selesai`, `ditolak`, `dibatalkan`).
8. **Pembayaran & Verifikasi:** Siswa mengunggah bukti transfer (validasi MIME, ekstensi, maks 2 MB). Admin memeriksa dan menyetujui (kuota otomatis berkurang) atau menolak disertai catatan.
9. **Review & Rating:** Siswa yang telah menyelesaikan kelas dapat memberikan rating 1-5 bintang beserta komentar. Rating rata-rata kelas dihitung otomatis.

---

## Teknologi
- **Backend:** PHP Native (tanpa framework) dengan MySQLi Prepared Statements
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5.3 via CDN, Bootstrap Icons
- **Autentikasi:** PHP Session, `password_hash()` dan `password_verify()`

---

## Panduan Instalasi & Konfigurasi

### 1. Prasyarat
- XAMPP / Laragon dengan PHP 8.0+ dan MySQL/MariaDB
- Web server Apache aktif

### 2. Import Database
Database telah dibuat dengan nama `mentora`. Import skema dan seed data:
```bash
mysql -u root -p mentora < database.sql
```
Atau melalui **phpMyAdmin**:
1. Buka `http://localhost/phpmyadmin`
2. Pilih database `mentora`
3. Klik tab **Import**, pilih file `database.sql`, lalu klik **Go**

### 3. Konfigurasi Database
Sesuaikan kredensial di file `config/db.php` jika diperlukan:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mentora');
```

### 4. Folder Upload
Pastikan folder berikut memiliki izin tulis (write permission):
- `uploads/thumbnail/`
- `uploads/bukti/`
- `uploads/foto/`

---

## Akun Demo

Semua akun demo menggunakan password: **`password123`**

| Peran | Email | Password |
|---|---|---|
| **Admin** | `admin@mentora.id` | `password123` |
| **Mentor 1** | `budi@mentora.id` | `password123` |
| **Mentor 2** | `sari@mentora.id` | `password123` |
| **Siswa 1** | `andi@mentora.id` | `password123` |
| **Siswa 2** | `rina@mentora.id` | `password123` |
| **Siswa 3** | `doni@mentora.id` | `password123` |

---

## Struktur Folder

```
mentora/
├── config/
│   └── db.php                  # Koneksi database
├── includes/
│   ├── header.php              # Header & navigasi
│   ├── footer.php              # Footer
│   ├── functions.php           # Helper & auth guard
│   └── 403.php                 # Halaman akses ditolak
├── auth/
│   ├── login.php               # Form masuk
│   ├── register.php            # Form pendaftaran
│   └── logout.php              # Keluar sesi
├── admin/
│   ├── dashboard.php           # Ringkasan statistik & pesanan baru
│   ├── users.php               # Kelola pengguna & peran
│   ├── kategori.php            # CRUD kategori
│   ├── kelas.php               # Daftar semua kelas
│   ├── pesanan.php             # Daftar semua pesanan
│   ├── pesanan-detail.php      # Detail & ubah status pesanan
│   └── verifikasi.php          # Verifikasi bukti transfer
├── mentor/
│   ├── dashboard.php           # Ringkasan pendapatan & kelas
│   ├── kelas-form.php          # Tambah & edit kelas
│   ├── kelas-hapus.php         # Hapus kelas
│   └── pendaftar.php           # Daftar siswa per kelas
├── siswa/
│   ├── keranjang.php           # Keranjang belanja
│   ├── keranjang-aksi.php      # Tambah/hapus keranjang
│   ├── checkout.php            # Ringkasan & konfirmasi order
│   ├── pesanan.php             # Riwayat pesanan
│   └── pesanan-detail.php      # Upload bukti bayar
├── uploads/
│   ├── thumbnail/              # Gambar kelas
│   ├── bukti/                  # Bukti transfer
│   └── foto/                   # Foto profil user
├── assets/
│   ├── css/style.css           # Styling kustom
│   ├── js/app.js               # Interaksi frontend
│   └── img/                    # SVG placeholder
├── index.php                   # Landing page (sebelum login)
├── home.php                    # Homepage/Beranda siswa (setelah login)
├── kelas.php                   # Katalog & pencarian kelas
├── kelas-detail.php            # Detail kelas & review
├── profil.php                  # Edit profil & ganti password
├── database.sql                # Skema dan data awal
└── README.md
```
