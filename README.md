## 👥 Anggota Tim
Berikut adalah kontributor yang bekerja dalam pengembangan proyek ini:

|Nama| NIM | GitHub |
| :--- | :---: | :--- |
| **Christy Jones** | 535240070 | [GitHub](https://github.com/christyjns) |
| **Vanesa Yolanda** | 535240071 | [GitHub](https://github.com/nesa28) |
| **Cathrine Sandrina** | 535240075 | [GitHub](https://github.com/Einnyboi) |
| **Tandwiyan Talenta** | 535240176 | [GitHub](https://github.com/tndwyntlnt) |
| **Naisya Yuen Ra’af** | 535240187 | [GitHub](https://github.com/itsyuenai) |

# ☕ Alleyway Muse - Backend API & Admin Panel

**Alleyway Muse Backend** adalah sistem manajemen loyalitas (*loyalty membership*) yang dirancang khusus untuk operasional kedai kopi modern. Proyek ini dibangun menggunakan **Laravel 11** dan berfungsi sebagai penyedia layanan RESTful API untuk aplikasi mobile (Flutter), sekaligus menyediakan Dashboard Admin yang kuat berbasis **FilamentPHP**.

Sistem ini menangani siklus hidup pelanggan mulai dari registrasi, transaksi pembelian, akumulasi poin, kenaikan level keanggotaan (Bronze/Silver/Gold), hingga penukaran hadiah.

---

## 🛠️ Teknologi Utama

Proyek ini dibangun di atas fondasi teknologi yang solid dan modern:

* **Framework:** Laravel 11.x
* **Admin Panel:** FilamentPHP v3
* **Database:** MySQL
* **Authentication:** Laravel Sanctum (Token-based API)
* **Email Service:** SMTP (Gmail)
* **Asset Management:** Local Storage Symlink

---

## ✨ Fitur & Fungsionalitas

### 📱 1. Fitur Mobile API (Untuk Pelanggan)
Backend ini menyediakan endpoint lengkap untuk aplikasi Flutter:

* **Autentikasi Aman:** Login, Register, Logout, dan Reset Password (OTP via Email) menggunakan Laravel Sanctum.
* **Sistem Poin & Leveling:**
    * **Redeem Code:** Pelanggan mendapatkan poin dengan memasukkan kode unik yang tertera pada struk transaksi.
    * **Auto Tiering:** Sistem otomatis menaikkan status member (Bronze → Silver → Gold) saat poin mencapai ambang batas tertentu.
* **Dompet Digital:**
    * **Rewards Catalog:** Menampilkan daftar hadiah yang bisa ditukar.
    * **My Rewards:** Menyimpan voucher *rewards* hadiah yang dimiliki user (beserta status kadaluwarsa).
* **Riwayat Aktivitas:** Mencatat semua *history* poin masuk (transaksi) dan poin keluar (penukaran hadiah) secara kronologis.
* **Promo & Banner:** Menampilkan informasi promo spesial ("Buy 1 Get 1", "Diskon", dll).

### 🖥️ 2. Fitur Admin Dashboard (Untuk Kasir/Owner)
Panel admin Filament digunakan untuk manajemen operasional sehari-hari:

* **Manajemen Pesanan (Order):**
    * Membuat pesanan baru untuk pelanggan.
    * Opsi menetapkan pesanan ke member terdaftar atau anonim.
    * Generate kode transaksi unik (`ALW-XXXXXX`) untuk klaim poin mandiri.
* **Manajemen Produk & Hadiah:** CRUD (Create, Read, Update, Delete) lengkap untuk menu kopi dan item hadiah.
* **Manajemen Pelanggan:** Memantau data member, saldo poin, dan riwayat transaksi.
* **Validasi Voucher:** Fitur untuk kasir menandai voucher milik pelanggan sebagai **"Terpakai" (Used)** saat ditukarkan di toko.
* **Manajemen Promo:** Mengatur banner promo yang tampil di aplikasi mobile.

---

## 📚 Dokumentasi API Ringkas

Berikut adalah daftar endpoint utama yang tersedia. Semua request ke endpoint yang dilindungi (*Protected*) wajib menyertakan Header: `Authorization: Bearer <token>`.

### 🔐 Authentication
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `POST` | `/api/register` | Mendaftar akun member baru |
| `POST` | `/api/login` | Masuk dan mendapatkan Token Akses |
| `POST` | `/api/forgot-password` | Request token reset password ke email |
| `POST` | `/api/verify-token` | Verifikasi validitas token reset |
| `POST` | `/api/reset-password` | Mengubah password baru |

### 👤 User Profile (Protected)
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/api/profile` | Mendapatkan data profil, poin, dan status member |
| `POST` | `/api/profile/update` | Update nama, telepon, tgl lahir, dan foto profil |
| `POST` | `/api/change-password` | Mengganti password (user login) |
| `POST` | `/api/logout` | Menghapus token akses (Keluar) |

### 💎 Loyalty Features (Protected)
| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `POST` | `/api/redeem-code` | Input kode transaksi untuk klaim poin |
| `GET` | `/api/rewards` | Melihat katalog hadiah yang tersedia |
| `POST` | `/api/rewards/redeem` | Menukar poin dengan hadiah tertentu |
| `GET` | `/api/my-rewards` | Melihat daftar voucher aktif milik user |
| `GET` | `/api/promos` | Melihat daftar promo spesial |
| `GET` | `/api/notifications` | Melihat riwayat aktivitas (Struk & Redeem) |

---

## 🚀 Instalasi & Konfigurasi

Ikuti langkah-langkah ini untuk menjalankan proyek di komputer lokal (Localhost).

### 1. Clone Repositori
```bash
git clone https://github.com/tndwyntlnt/backendalleyway.git
cd backendalleyway
```

### 2. Install Dependencies
Pastikan Anda memiliki Composer dan PHP versi 8.2+.
```bash
composer install
```

### 3. Konfigurasi Environment
Duplikasi file contoh .env dan sesuaikan dengan konfigurasi database lokal Anda.
```bash
cp .env.example .env
```
Buka file .env dan atur koneksi database:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Migrasi Database
Jalankan perintah ini untuk membuat semua tabel yang diperlukan.
```bash
php artisan migrate
```

### 6. Setup Storage (Penting untuk Gambar)
Agar gambar produk dan promo bisa diakses publik, buat symbolic link:
```bash
php artisan storage:link
```

### 7. Buat Akun Admin
Buat akun untuk login ke dashboard Filament.
```bash
php artisan make:filament-user
```

### 8. Jalankan Server
```bash
php artisan serve
```

### 📧 Konfigurasi Email (Opsional)
Fitur Lupa Password memerlukan konfigurasi SMTP. Untuk pengembangan lokal, disarankan menggunakan Mailtrap atau Gmail App Password.
Contoh konfigurasi .env untuk Gmail:
```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email_anda@gmail.com
MAIL_PASSWORD=app_password_16_digit
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@alleyway.com"
```
