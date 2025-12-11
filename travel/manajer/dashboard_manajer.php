<?php
/**
 * Manajer Dashboard
 */
// include helper and ensure session
if (file_exists(__DIR__ . '/../config/auth_helper.php')) {
    require_once __DIR__ . '/../config/auth_helper.php';
}
ensure_session();
include __DIR__ . '/../config/database.php';

// Ensure manajer or admin session
$role = $_SESSION['pengguna']['level_akses'] ?? null;
if (!isset($_SESSION['pengguna']) || !in_array($role, ['manajer','admin'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $_SESSION['pengguna'];

// Example stat: count pemesanan
$total_pemesanan = 0;
if (isset($conn) && $conn) {
    $res = @mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM pemesanan");
    if ($res) {
        $r = mysqli_fetch_assoc($res);
        $total_pemesanan = intval($r['cnt'] ?? 0);
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Manajer Dashboard - Travel</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f8fc 0%, #e8f1f8 100%);
            min-height: 100vh;
        }
        
        .topbar {
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.15);
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
            color: #1976d2;
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .card .value {
            font-size: 28px;
            font-weight: 700;
            color: #1976d2;
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
            background: #e3f2fd;
            color: #1976d2;
            border-left-color: #1976d2;
            padding-left: 20px;
        }
        
        .sidebar hr {
            margin: 8px 0;
            border: none;
            border-top: 1px solid #f0f6ff;
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
                <div class="logo">📊</div>
                <div>
                    <h1>Dashboard Manajer</h1>
                    <div class="small">Halo, <?= htmlspecialchars($user['username'] ?? 'Manajer') ?></div>
                </div>
            </div>
            <div class="actions">
                <a class="btn" href="../auth/logout.php">Logout</a>
            </div>
        </div>

        <div class="layout">
            <aside class="sidebar">
                <a href="../admin/pemesanan.php">📋 Data Pemesanan</a>
                <a href="../admin/armada.php">🚌 Data Armada</a>
                <a href="../admin/jadwal.php">📅 Jadwal Perjalanan</a>
                <hr>
                <a href="laporan_penjualan.php">💰 Laporan Penjualan</a>
                <a href="approve_content.php">✅ Persetujuan Konten</a>
            </aside>

            <main class="main">
                <div class="grid-cards">
                    <div class="card">
                        <h3>📊 Total Pemesanan</h3>
                        <div class="value"><?= $total_pemesanan ?></div>
                        <div class="small">Semua status</div>
                    </div>
                    <div class="card">
                        <h3>⚙️ Operasional</h3>
                        <div class="small">Pengelolaan armada dan jadwal</div>
                    </div>
                    <div class="card">
                        <h3>📈 Monitoring</h3>
                        <div class="small">Lihat laporan dan performa</div>
                    </div>
                </div>

                <div class="section card">
                    <h3>Catatan Manajer</h3>
                    <p>Gunakan menu di samping untuk akses data operasional.</p>
                </div>
            </main>
        </div>
    </div>
</body>
</html>