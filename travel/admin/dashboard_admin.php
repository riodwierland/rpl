<?php
/**
 * Admin Dashboard Loader
 * Menampilkan view dashboard khusus admin
 */
// include helper and ensure session
if (file_exists(__DIR__ . '/../config/auth_helper.php')) {
    require_once __DIR__ . '/../config/auth_helper.php';
}
ensure_session();

if (!isset($_SESSION['pengguna']) || ($_SESSION['pengguna']['level_akses'] ?? '') !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Tampilkan view admin
include __DIR__ . '/dashboard_admin_view.php';
exit;
