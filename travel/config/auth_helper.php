<?php
/**
 * Authentication Helper Functions
 * Menyediakan fungsi-fungsi untuk autentikasi, sanitasi input, dan role-based routing
 */

/**
 * Redirect berdasarkan role pengguna
 * 
 * @param array $user Data pengguna dari database
 * @param bool $is_admin True jika pengguna admin, false jika pelanggan
 * @return void
 */
function redirect_by_role($user, $is_admin = false) {
    if ($is_admin) {
        $role = $user['level_akses'] ?? 'admin';
        
        switch ($role) {
            case 'admin':
            case 'superadmin':
                // Admin: gunakan admin loader yang memeriksa session dan menampilkan view
                header('Location: ../admin/dashboard_admin.php');
                break;
            case 'manajer':
                // Manajer dashboard (terpisah)
                header('Location: ../manajer/dashboard_manajer.php');
                break;
            case 'karyawan':
                // Karyawan dashboard
                header('Location: ../karyawan/dashboard_karyawan.php');
                break;
            default:
                // Fallback ke halaman pelanggan jika role tidak dikenali
                header('Location: ../pelanggan/dashboard_pelanggan.php');
        }
    } else {
        // Redirect pelanggan ke customer dashboard
        header('Location: ../pelanggan/dashboard_pelanggan.php');
    }
    exit;
}

/**
 * Sanitasi input untuk mencegah XSS dan parsing errors
 * 
 * @param string $input Input yang akan disanitasi
 * @return string Input yang sudah disanitasi
 */
function sanitize_input($input) {
    return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}

/**
 * Validasi format email
 * 
 * @param string $email Email yang akan divalidasi
 * @return bool True jika valid, false jika tidak
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validasi password (minimum 6 karakter)
 * 
 * @param string $password Password yang akan divalidasi
 * @return bool True jika valid, false jika tidak
 */
function is_valid_password($password) {
    return strlen($password) >= 6;
}

/**
 * Ensure session is started safely
 */
function ensure_session(): bool {
    // Return true if session is already active
    if (session_status() === PHP_SESSION_ACTIVE) {
        return true;
    }

    // If headers were already sent, starting a session may emit warnings/errors.
    // Try a best-effort start but avoid noisy errors when headers already sent.
    if (headers_sent($file, $line)) {
        @session_start();
        return session_status() === PHP_SESSION_ACTIVE;
    }

    // Normal case: start session and return success status
    session_start();
    return session_status() === PHP_SESSION_ACTIVE;
}
