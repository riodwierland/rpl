<?php
/**
 * Admin - Register User Page
 * Halaman untuk admin membuat akun karyawan/staff baru
 */
include '../config/database.php';
require_once __DIR__ . '/../config/auth_helper.php';
ensure_session();

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['pengguna']) || 
    ($_SESSION['pengguna']['level_akses'] !== 'admin' && 
     $_SESSION['pengguna']['level_akses'] !== 'superadmin')) {
    header('Location: ../auth/login.php');
    exit;
}

$error = '';
$success = '';

if (isset($_POST['register_user'])) {
    $username = sanitize_input($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass_confirm = $_POST['password_confirm'] ?? '';
    $level_akses = $_POST['level_akses'] ?? '';

    // Validasi input
    if (empty($username) || empty($pass) || empty($level_akses)) {
        $error = 'Semua field harus diisi.';
    } elseif (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter.';
    } elseif (!is_valid_password($pass)) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($pass !== $pass_confirm) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } elseif (!in_array($level_akses, ['admin', 'manajer', 'karyawan'])) {
        $error = 'Level akses tidak valid.';
    } else {
        try {
            if (!isset($conn) || !$conn) {
                $error = 'Koneksi database gagal.';
            } else {
                // Cek apakah tabel pengguna ada
                $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'pengguna'");
                if (!$table_check || mysqli_num_rows($table_check) == 0) {
                    $error = 'Tabel pengguna tidak ditemukan. Database belum diinisialisasi.';
                } else {
                    // Cek apakah username sudah terdaftar
                    $username_escaped = mysqli_real_escape_string($conn, $username);
                    $cek = mysqli_query($conn, "SELECT id_user FROM pengguna WHERE username = '$username_escaped'");
                    
                    if (!$cek) {
                        $error = 'Error checking username: ' . mysqli_error($conn);
                    } elseif (mysqli_num_rows($cek) > 0) {
                        $error = 'Username sudah terdaftar. Gunakan username lain.';
                    } else {
                        // Hash password
                        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
                        
                        // Escape semua input
                        $pass_hash_escaped = mysqli_real_escape_string($conn, $pass_hash);
                        $level_akses_escaped = mysqli_real_escape_string($conn, $level_akses);
                        
                        // Insert user baru
                        $query = "INSERT INTO pengguna (username, password, level_akses) 
                                 VALUES ('$username_escaped', '$pass_hash_escaped', '$level_akses_escaped')";
                        
                        if (mysqli_query($conn, $query)) {
                            $success = "User '$username' dengan level '$level_akses' berhasil dibuat!";
                            // Clear form
                            $_POST = [];
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
    <title>Daftar User - Travel Admin</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        body {
            background: linear-gradient(135deg, #eef6ff 0%, #f8fbff 100%);
            padding: 20px;
        }

        .form-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 32px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(20, 40, 80, 0.08);
        }

        .form-container h1 {
            color: #07204a;
            margin-top: 0;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .form-container .subtitle {
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 24px;
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e6eefc;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
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

        .info-box {
            background: #f0f6ff;
            padding: 12px;
            border-radius: 8px;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .footer-links {
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
        }

        .footer-links a {
            color: var(--accent);
            text-decoration: none;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Daftar User Baru</h1>
        <div class="subtitle">Buat akun untuk karyawan, manajer, atau admin</div>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-msg">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="info-box">
            <strong>Catatan:</strong><br>
            • Username harus unik dan minimal 3 karakter<br>
            • Password minimal 6 karakter<br>
            • Level akses menentukan hak akses di sistem
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Contoh: budi.karyawan" 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>

            <div class="form-group">
                <label for="password_confirm">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" 
                       placeholder="Ulangi password" required>
            </div>

            <div class="form-group">
                <label for="level_akses">Level Akses</label>
                <select id="level_akses" name="level_akses" required>
                    <option value="">-- Pilih Level Akses --</option>
                    <option value="karyawan">Karyawan</option>
                    <option value="manajer">Manajer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit" name="register_user" class="btn-register">Buat User</button>
        </form>

        <div class="footer-links">
            <a href="../dashboard.php">← Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>