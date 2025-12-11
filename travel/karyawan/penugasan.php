<?php
/**
 * Karyawan - Daftar Tugas (Stub)
 */
// include helper and ensure session
if (file_exists(__DIR__ . '/../config/auth_helper.php')) {
    require_once __DIR__ . '/../config/auth_helper.php';
}
ensure_session();
$role = $_SESSION['pengguna']['level_akses'] ?? null;
if (!isset($_SESSION['pengguna']) || !in_array($role, ['karyawan','manajer','admin'])) {
    header('Location: ../auth/login.php');
    exit;
}
include __DIR__ . '/../config/database.php';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Daftar Tugas - Karyawan</title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="container">
    <div class="topbar"><h1>Daftar Tugas</h1> <a class="btn" href="../auth/logout.php">Logout</a></div>
    <div style="padding:20px">Halaman tugas karyawan (stub). Implementasikan penugasan di sini.</div>
    <div style="padding:20px"><a href="dashboard_karyawan.php">← Kembali</a></div>
</div>
</body>
</html>