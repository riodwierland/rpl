<?php
/**
 * Admin - Data Armada (Stub)
 */
// include helper and ensure session
if (file_exists(__DIR__ . '/../config/auth_helper.php')) {
    require_once __DIR__ . '/../config/auth_helper.php';
}
ensure_session();
if (!isset($_SESSION['pengguna'])) {
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
<title>Data Armada - Admin</title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="container">
    <div class="topbar"><h1>Data Armada</h1> <a class="btn" href="../auth/logout.php">Logout</a></div>
    <div style="padding:20px">Halaman data armada (stub). Tambahkan daftar kendaraan di sini.</div>
    <div style="padding:20px"><a href="dashboard_admin.php">← Kembali</a></div>
</div>
</body>
</html>