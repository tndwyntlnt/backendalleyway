# ☕ Alleyway Muse - Backend API & Admin Panel

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)
![Filament](https://img.shields.io/badge/Filament-v3-F28D15?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/Database-MySQL-005C84?style=for-the-badge&logo=mysql)
![Sanctum](https://img.shields.io/badge/Auth-Sanctum-shield?style=for-the-badge)

**Alleyway Muse Backend** adalah sistem manajemen loyalitas dan _membership_ modern yang dibangun khusus untuk _coffee shop_. Proyek ini berfungsi sebagai **RESTful API** untuk aplikasi mobile (Flutter) dan menyediakan **Admin Dashboard** yang kuat untuk manajemen operasional.

Backend ini menangani seluruh logika bisnis mulai dari manajemen pengguna, perhitungan poin otomatis, penukaran hadiah, hingga manajemen promo marketing.

---

## 📸 Admin Dashboard Preview

_(Ganti bagian ini dengan screenshot dashboard Filament kamu, misal: halaman Dashboard utama dan halaman Order)_

|                          Dashboard Overview                          |                       Order Management                        |
| :------------------------------------------------------------------: | :-----------------------------------------------------------: |
| ![Dashboard](https://placehold.co/600x400?text=Dashboard+Screenshot) | ![Orders](https://placehold.co/600x400?text=Order+Screenshot) |

---

## ✨ Fitur Utama

### 📱 Untuk Aplikasi Mobile (API)

-   **Secure Authentication:** Login, Register, Logout, dan Forgot Password (OTP via Email) menggunakan **Laravel Sanctum**.
-   **Loyalty System:**
    -   **Redeem Code:** User menginput kode unik dari struk untuk mendapatkan poin.
    -   **Leveling Otomatis:** Sistem otomatis menaikkan status member (Bronze, Silver, Gold) berdasarkan capaian poin.
-   **Rewards Marketplace:** Katalog penukaran poin dengan _merchandise_ atau menu gratis.
-   **My Voucher:** Manajemen voucher hadiah yang dimiliki user (status: _unclaimed_, _used_, _expired_).
-   **Digital Receipt & History:** Riwayat aktivitas lengkap (poin masuk & keluar) yang digabung secara kronologis.
-   **Promo & Banner:** Menampilkan promo spesial yang diatur dari admin.

### 🖥️ Untuk Admin (Filament Panel)

-   **CRUD Produk & Reward:** Manajemen menu kopi dan hadiah penukaran.
-   **Order Management:**
    -   Membuat pesanan baru (Kasir).
    -   Pilihan untuk menetapkan pesanan ke member tertentu atau anonim.
    -   Generate kode transaksi unik otomatis.
-   **Customer Management:** Melihat profil member, riwayat transaksi, dan saldo poin.
-   **Promo Management:** Upload banner dan deskripsi promo untuk aplikasi.
-   **Validasi Voucher:** Menandai voucher milik user sebagai "Terpakai" (_Mark as Used_).

---

## 🛠️ Teknologi yang Digunakan

-   **Framework:** [Laravel 11](https://laravel.com/)
-   **Admin Panel:** [FilamentPHP v3](https://filamentphp.com/)
-   **Database:** MySQL / MariaDB
-   **Authentication:** Laravel Sanctum
-   **Email Service:** SMTP (Gmail / Mailtrap)
-   **Storage:** Local Storage (Symlink)

---

## 🚀 Instalasi & Setup

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di komputer lokal Anda.

### Prasyarat

-   PHP >= 8.2
-   Composer
-   MySQL

### Langkah-langkah

1.  **Clone Repository**

    ```bash
    git clone [https://github.com/username-anda/alleyway-backend.git](https://github.com/username-anda/alleyway-backend.git)
    cd alleyway-backend
    ```

2.  **Install Dependencies**

    ```bash
    composer install
    ```

3.  **Setup Environment (.env)**
    Salin file contoh `.env` dan konfigurasi database Anda.

    ```bash
    cp .env.example .env
    ```

    _Buka file `.env` dan sesuaikan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`._

4.  **Generate App Key**

    ```bash
    php artisan key:generate
    ```

5.  **Migrasi Database**
    Jalankan migrasi untuk membuat tabel.

    ```bash
    php artisan migrate
    ```

6.  **Setup Storage Link**
    Wajib dilakukan agar gambar produk/promo bisa diakses publik.

    ```bash
    php artisan storage:link
    ```

7.  **Buat Akun Admin**
    Buat akun untuk login ke dashboard Filament.

    ```bash
    php artisan make:filament-user
    ```

8.  **Jalankan Server**
    ```bash
    php artisan serve
    ```
    _Backend akan berjalan di `http://127.0.0.1:8000` (atau gunakan `--host` untuk akses jaringan)._

---

## 📚 Dokumentasi API

Berikut adalah daftar _endpoint_ utama yang tersedia untuk aplikasi Frontend.

### Authentication

| Method | Endpoint               | Deskripsi                             |
| :----- | :--------------------- | :------------------------------------ |
| `POST` | `/api/register`        | Mendaftar akun member baru            |
| `POST` | `/api/login`           | Masuk dan mendapatkan Token Bearer    |
| `POST` | `/api/forgot-password` | Request token reset password ke email |
| `POST` | `/api/verify-token`    | Verifikasi token reset password       |
| `POST` | `/api/reset-password`  | Mengubah password dengan token        |

### User & Profile (Protected)

_Header: `Authorization: Bearer <token>`_

| Method | Endpoint               | Deskripsi                                |
| :----- | :--------------------- | :--------------------------------------- |
| `GET`  | `/api/profile`         | Mendapatkan data user (Poin, Level, dll) |
| `POST` | `/api/profile/update`  | Update nama, telepon, tgl lahir, foto    |
| `POST` | `/api/change-password` | Ganti password (user login)              |
| `POST` | `/api/logout`          | Hapus token akses                        |

### Features (Protected)

| Method | Endpoint              | Deskripsi                              |
| :----- | :-------------------- | :------------------------------------- |
| `POST` | `/api/redeem-code`    | Input kode transaksi untuk dapat poin  |
| `GET`  | `/api/rewards`        | List katalog hadiah                    |
| `POST` | `/api/rewards/redeem` | Menukar poin dengan hadiah             |
| `GET`  | `/api/my-rewards`     | List voucher hadiah milik user (Aktif) |
| `GET`  | `/api/promos`         | List banner promo spesial              |
| `GET`  | `/api/notifications`  | Riwayat aktivitas (Struk & Redeem)     |

---

## 🧪 Pengujian Email (Mailtrap/Gmail)

Untuk fitur **Forgot Password**, pastikan Anda telah mengonfigurasi SMTP di file `.env`.

Contoh konfigurasi menggunakan **Gmail App Password**:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email_anda@gmail.com
MAIL_PASSWORD=password_aplikasi_16_digit
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@alleyway.com"
```
