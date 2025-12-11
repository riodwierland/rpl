<?php
/**
 * Login Page
 * Menangani login untuk pelanggan dan pengguna (admin/karyawan/manajer)
 */
include '../config/database.php';
require_once __DIR__ . '/../config/auth_helper.php';
ensure_session();

$error = '';
$success = isset($_GET['success']) ? 'Registrasi berhasil! Silakan login.' : '';

if (isset($_POST['login'])) {
    $user = sanitize_input($_POST['user'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (empty($user) || empty($pass)) {
        $error = 'Email/Username dan password harus diisi.';
    } else {
        // Cek pelanggan terlebih dahulu
        try {
            $user_escaped = mysqli_real_escape_string($conn, $user);
            $pelanggan = mysqli_query($conn, "SELECT * FROM pelanggan WHERE email = '$user_escaped'");
            
            if ($pelanggan && mysqli_num_rows($pelanggan) === 1) {
                $p = mysqli_fetch_assoc($pelanggan);
                if (password_verify($pass, $p['password'])) {
                    $_SESSION['pelanggan'] = $p;
                    redirect_by_role($p, false);
                } else {
                    $error = 'Password salah.';
                }
            } else {
                // Cek pengguna (admin/karyawan/manajer)
                $pengguna = mysqli_query($conn, "SELECT * FROM pengguna WHERE username = '$user_escaped'");
                
                if ($pengguna && mysqli_num_rows($pengguna) === 1) {
                    $u = mysqli_fetch_assoc($pengguna);
                    if (password_verify($pass, $u['password'])) {
                        $_SESSION['pengguna'] = $u;
                        redirect_by_role($u, true);
                    } else {
                        $error = 'Password salah.';
                    }
                } else {
                    $error = 'Email/Username tidak ditemukan.';
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
    <title>Login - Travel</title>
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

        .btn-login {
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

        .btn-login:hover {
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

        .role-hint {
            background: #f0f6ff;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 16px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2>Masuk ke Travel</h2>
            <div class="subtitle">Akses dashboard dan kelola paket wisata Anda</div>

            <?php if (!empty($error)): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="success-msg"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="role-hint">
                    <strong>Login sebagai:</strong><br>
                    Pelanggan: Email Anda<br>
                    Admin/Karyawan: Username Anda
                </div>

                <div class="form-group">
                    <label for="user">Email atau Username</label>
                    <input type="text" id="user" name="user" placeholder="Masukkan email atau username" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>

                <button type="submit" name="login" class="btn-login">Masuk</button>
            </form>

            <div class="auth-footer">
                Belum punya akun? <a href="register.php">Daftar sekarang</a>
            </div>
            <div class="auth-footer" style="margin-top: 8px;">
                <a href="../index.php">← Kembali ke beranda</a>
            </div>
        </div>
    </div>
</body>
</html>