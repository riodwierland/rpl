<?php
/**
 * Register Page
 * Menangani registrasi pelanggan baru dengan validasi dan penyimpanan data
 */
include '../config/database.php';
require_once __DIR__ . '/../config/auth_helper.php';
ensure_session();

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $nama = sanitize_input($_POST['nama'] ?? '');
    $kontak = sanitize_input($_POST['kontak'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass_confirm = $_POST['password_confirm'] ?? '';

    // Validasi input
    if (empty($nama) || empty($kontak) || empty($email) || empty($pass)) {
        $error = 'Semua field harus diisi.';
    } elseif (!is_valid_email($email)) {
        $error = 'Format email tidak valid.';
    } elseif (!is_valid_password($pass)) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($pass !== $pass_confirm) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } else {
        try {
            // Pastikan koneksi database aktif
            if (!isset($conn) || !$conn) {
                $error = 'Koneksi database gagal. Silakan hubungi administrator.';
            } else {
                // Cek apakah tabel pelanggan ada
                $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'pelanggan'");
                if (!$table_check || mysqli_num_rows($table_check) == 0) {
                    $error = 'Tabel pelanggan tidak ditemukan. Database belum diinisialisasi. Hubungi administrator untuk menjalankan setup.';
                } else {
                    // Cek apakah email sudah terdaftar
                    $email_escaped = mysqli_real_escape_string($conn, $email);
                    $cek = mysqli_query($conn, "SELECT id_pelanggan FROM pelanggan WHERE email = '$email_escaped'");
                    
                    if (!$cek) {
                        $error = 'Error checking email: ' . mysqli_error($conn);
                    } elseif (mysqli_num_rows($cek) > 0) {
                        $error = 'Email sudah terdaftar. Gunakan email lain atau login.';
                    } else {
                        // Hash password
                        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
                        
                        // Escape semua input
                        $nama_escaped = mysqli_real_escape_string($conn, $nama);
                        $kontak_escaped = mysqli_real_escape_string($conn, $kontak);
                        $pass_hash_escaped = mysqli_real_escape_string($conn, $pass_hash);
                        
                        // Insert pelanggan baru
                        $query = "INSERT INTO pelanggan (nama, kontak, email, password) 
                                 VALUES ('$nama_escaped', '$kontak_escaped', '$email_escaped', '$pass_hash_escaped')";
                        
                        if (mysqli_query($conn, $query)) {
                            // Registrasi berhasil, redirect ke login
                            header('Location: login.php?success=1');
                            exit;
                        } else {
                            $error = 'Database Error: ' . mysqli_error($conn);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - Travel</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #eef6ff 0%, #f8fbff 100%);
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: 16px;
        }

        .auth-card {
            background: #fff;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(20, 40, 80, 0.08);
        }

        .auth-card h2 {
            margin: 0 0 8px;
            text-align: center;
            font-size: 24px;
            color: #07204a;
        }

        .auth-card .subtitle {
            text-align: center;
            color: var(--muted);
            margin-bottom: 24px;
            font-size: 13px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #111;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e6eefc;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(43, 140, 255, 0.1);
        }

        .error-msg {
            background: #fee;
            color: #a00;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            border-left: 4px solid #f00;
        }

        .success-msg {
            background: #efe;
            color: #0a0;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            border-left: 4px solid #0f0;
        }

        .btn-register {
            width: 100%;
            padding: 10px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-register:hover {
            background: #1f7acc;
        }

        .auth-footer {
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            color: var(--muted);
        }

        .auth-footer a {
            color: var(--accent);
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .password-hint {
            background: #f0f6ff;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2>Daftar Akun Baru</h2>
            <div class="subtitle">Buat akun untuk memulai petualangan wisata Anda</div>

            <?php if (!empty($error)): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="success-msg"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" required autofocus>
                </div>

                <div class="form-group">
                    <label for="kontak">Nomor Kontak / WhatsApp</label>
                    <input type="text" id="kontak" name="kontak" placeholder="Contoh: +62 812 3456 7890" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Contoh: anda@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                    <div class="password-hint">Password harus minimal 6 karakter dan bersifat rahasia</div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Konfirmasi Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="Ulangi password" required>
                </div>

                <button type="submit" name="register" class="btn-register">Daftar Sekarang</button>
            </form>

            <div class="auth-footer">
                Sudah punya akun? <a href="login.php">Masuk di sini</a>
            </div>
            <div class="auth-footer" style="margin-top: 8px;">
                <a href="../index.php">← Kembali ke beranda</a>
            </div>
        </div>
    </div>
</body>
</html>
