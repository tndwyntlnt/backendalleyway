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
git clone [https://github.com/username-anda/alleyway-backend.git](https://github.com/username-anda/alleyway-backend.git)
cd backendalleyway
