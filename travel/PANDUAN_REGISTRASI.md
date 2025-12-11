# Panduan: Cara Admin/Karyawan Membuat Akun

## Overview

Ada dua jenis pengguna dalam sistem Travel:

1. **Pelanggan** - Pengguna umum yang booking paket wisata (bisa self-register)
2. **Admin/Karyawan** - Tim internal yang mengelola sistem (hanya dibuat oleh admin)

---

## Untuk Pelanggan (Self-Registration)

Pelanggan dapat mendaftar sendiri melalui:

```
http://localhost/travel/auth/register.php
```

### Data yang diperlukan:

- Nama Lengkap
- Nomor Kontak / WhatsApp
- Email (unik, tidak boleh terduplikasi)
- Password (minimal 6 karakter)
- Konfirmasi Password

### Setelah Registrasi:

✅ Data tersimpan di tabel `pelanggan`  
✅ Pelanggan bisa langsung login dengan email dan password  
✅ Bisa akses dashboard pelanggan dan booking paket

---

## Untuk Admin/Karyawan (Admin Registration)

### Langkah 1: Login sebagai Admin

1. Buka halaman login: `http://localhost/travel/auth/login.php`
2. Login dengan akun admin:
   - **Username:** `admin`
   - **Password:** `admin123` (default dari setup.php)

### Langkah 2: Akses Halaman Pendaftaran User

Setelah login, di sidebar dashboard akan tampil menu:

- **Dashboard Admin**
- **Daftar User Baru** ← Klik di sini
- Kelola Pengguna
- Data Armada
- Jadwal Perjalanan
- Data Pemesanan

Atau langsung akses: `http://localhost/travel/admin/register_user.php`

### Langkah 3: Isi Form Pendaftaran User

Form meminta:

| Field                   | Keterangan                                                   |
| ----------------------- | ------------------------------------------------------------ |
| **Username**            | Nama login unik, minimal 3 karakter. Contoh: `budi.karyawan` |
| **Password**            | Minimal 6 karakter, bersifat rahasia                         |
| **Konfirmasi Password** | Harus sama dengan password di atas                           |
| **Level Akses**         | Pilih dari: Karyawan, Manajer, atau Admin                    |

### Level Akses:

- **Karyawan** - Akses terbatas untuk staff operasional
- **Manajer** - Akses menengah untuk supervisor
- **Admin** - Akses penuh untuk administratif

### Langkah 4: Submit & Verifikasi

1. Klik tombol **"Buat User"**
2. Jika berhasil, akan muncul pesan: ✅ `User 'nama' dengan level 'level' berhasil dibuat!`
3. User baru dapat login dengan:
   - **Username:** Sesuai yang dibuat
   - **Password:** Sesuai yang diset

---

## Alur Login

### Login Sebagai Pelanggan:

```
URL: http://localhost/travel/auth/login.php
Input: Email + Password
Tabel: pelanggan
Tujuan: Dashboard pelanggan
```

### Login Sebagai Admin/Karyawan:

```
URL: http://localhost/travel/auth/login.php
Input: Username + Password
Tabel: pengguna
Tujuan: Dashboard admin/manajer/karyawan
```

---

## Contoh Membuat User Baru

**Skenario:** Admin ingin membuat akun untuk karyawan baru bernama Budi

### Step 1: Login as Admin

- Username: `admin`
- Password: `admin123`

### Step 2: Go to "Daftar User Baru" Menu

- Click menu "Daftar User Baru" di sidebar

### Step 3: Isi Data Karyawan

```
Username: budi.wirawan
Password: Budi@12345
Confirm Password: Budi@12345
Level Akses: Karyawan
```

### Step 4: Klik "Buat User"

✅ User berhasil dibuat!

### Step 5: Budi Bisa Login

```
Username: budi.wirawan
Password: Budi@12345
```

---

## Database Schema

### Tabel Pelanggan (Customer)

```sql
CREATE TABLE pelanggan (
    id_pelanggan INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100),
    kontak VARCHAR(15),
    email VARCHAR(100),           -- Unique
    password VARCHAR(100)          -- Hashed
);
```

### Tabel Pengguna (Admin/Staff)

```sql
CREATE TABLE pengguna (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50),          -- Unique
    password VARCHAR(100),         -- Hashed
    level_akses VARCHAR(50)        -- 'admin', 'manajer', 'karyawan'
);
```

---

## Keamanan

✅ **Password Hashing:** Menggunakan `password_hash()` dengan PASSWORD_DEFAULT  
✅ **Input Sanitasi:** Mencegah SQL Injection dengan `mysqli_real_escape_string()`  
✅ **Role-Based Access:** Hanya admin yang bisa akses halaman register_user.php  
✅ **Session Verification:** Sistem cek session sebelum akses halaman admin

---

## Troubleshooting

### ❌ Error: "Tabel pengguna tidak ditemukan"

**Solusi:** Jalankan setup database terlebih dahulu

```
http://localhost/travel/setup.php
→ Klik "Setup Database Sekarang"
```

### ❌ Error: "Username sudah terdaftar"

**Solusi:** Gunakan username yang berbeda, username harus unik

### ❌ Error: "Password dan konfirmasi password tidak cocok"

**Solusi:** Pastikan kedua password field sama persis

### ❌ Tidak bisa akses halaman "Daftar User Baru"

**Solusi:** Pastikan sudah login sebagai admin/superadmin, bukan pelanggan

---

## File-File Terkait

| File                      | Fungsi                                |
| ------------------------- | ------------------------------------- |
| `auth/register.php`       | Registrasi pelanggan                  |
| `admin/register_user.php` | Registrasi user (admin/karyawan)      |
| `auth/login.php`          | Login untuk semua user                |
| `config/database.php`     | Koneksi database                      |
| `config/auth_helper.php`  | Helper functions (validate, sanitize) |
| `setup.php`               | Inisialisasi database                 |

---

**Last Updated:** 2025-12-11  
**Status:** ✅ Complete
