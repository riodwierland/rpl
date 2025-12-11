<?php
/**
 * Admin Dashboard View
 */
// include helper and ensure session
if (file_exists(__DIR__ . '/../config/auth_helper.php')) {
    require_once __DIR__ . '/../config/auth_helper.php';
}
ensure_session();
include __DIR__ . '/../config/database.php';

// Security: ensure admin session
if (!isset($_SESSION['pengguna']) || ($_SESSION['pengguna']['level_akses'] ?? '') !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$admin = $_SESSION['pengguna'];

// Simple stats (best-effort)
$total_users = 0;
if (isset($conn) && $conn) {
    $res = @mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM pengguna");
    if ($res) {
        $r = mysqli_fetch_assoc($res);
        $total_users = intval($r['cnt'] ?? 0);
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Dashboard - Travel</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f8fc 0%, #e8f1f8 100%);
            min-height: 100vh;
        }
        
        .topbar {
            background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
            box-shadow: 0 4px 12px rgba(211, 47, 47, 0.15);
        }
        
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .card h3 {
            color: #d32f2f;
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .card .value {
            font-size: 28px;
            font-weight: 700;
            color: #d32f2f;
            margin: 8px 0;
        }
        
        .sidebar {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .sidebar a {
            display: block;
            padding: 14px 18px;
            color: #555;
            text-decoration: none;
            border-left: 3px solid transparent;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            border-bottom: 1px solid #f0f6ff;
        }
        
        .sidebar a:hover {
            background: #fff5f5;
            color: #d32f2f;
            border-left-color: #d32f2f;
            padding-left: 20px;
        }
        
        .section {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .section h3 {
            margin: 0 0 20px;
            font-size: 18px;
            color: #333;
            border-bottom: 2px solid #f0f6ff;
            padding-bottom: 12px;
        }
        
        .grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <div class="brand">
                <div class="logo">🔐</div>
                <div>
                    <h1>Admin Dashboard</h1>
                    <div class="small">Halo, <?= htmlspecialchars($admin['username']) ?></div>
                </div>
            </div>
            <div class="actions">
                <a class="btn" href="../auth/logout.php">Logout</a>
            </div>
        </div>

        <div class="layout">
            <aside class="sidebar">
                <a href="../admin/register_user.php">➕ Daftar User Baru</a>
                <a href="../admin/users.php">👥 Kelola Pengguna</a>
                <a href="../admin/armada.php">🚌 Data Armada</a>
                <a href="../admin/jadwal.php">📅 Jadwal Perjalanan</a>
                <a href="../admin/pemesanan.php">📋 Data Pemesanan</a>
            </aside>

            <main class="main">
                <div class="grid-cards">
                    <div class="card">
                        <h3>👥 Total Pengguna</h3>
                        <div class="value"><?= $total_users ?></div>
                        <div class="small">Termasuk admin/manajer/karyawan</div>
                    </div>
                    <div class="card">
                        <h3>⚙️ Kelola</h3>
                        <div class="small">Gunakan menu samping untuk mengelola data</div>
                    </div>
                    <div class="card">
                        <h3>⚡ Quick Actions</h3>
                        <div class="small"><a href="../admin/register_user.php" style="color:#d32f2f;font-weight:600">Buat User Baru →</a></div>
                    </div>
                </div>

                <div class="section">
                    <h3>📝 Catatan Admin</h3>
                    <p>Halaman ini khusus untuk role admin. Gunakan menu untuk navigasi.</p>
                </div>
            </main>
        </div>
    </div>
</body>
</html>