<?php
/**
 * Karyawan Dashboard
 */
// include helper and ensure session
if (file_exists(__DIR__ . '/../config/auth_helper.php')) {
    require_once __DIR__ . '/../config/auth_helper.php';
}
ensure_session();
include __DIR__ . '/../config/database.php';

// Ensure karyawan or manajer session
$role = $_SESSION['pengguna']['level_akses'] ?? null;
if (!isset($_SESSION['pengguna']) || !in_array($role, ['karyawan','manajer','admin'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $_SESSION['pengguna'];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Karyawan Dashboard - Travel</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f8fc 0%, #e8f1f8 100%);
            min-height: 100vh;
        }
        
        .topbar {
            background: linear-gradient(135deg, #16a085 0%, #138d75 100%);
            box-shadow: 0 4px 12px rgba(22, 160, 133, 0.15);
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
            color: #16a085;
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .card .value {
            font-size: 28px;
            font-weight: 700;
            color: #16a085;
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
            background: #e0f7f6;
            color: #16a085;
            border-left-color: #16a085;
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
                <div class="logo">👷</div>
                <div>
                    <h1>Dashboard Karyawan</h1>
                    <div class="small">Halo, <?= htmlspecialchars($user['username'] ?? ($user['nama'] ?? 'Karyawan')) ?></div>
                </div>
            </div>
            <div class="actions">
                <a class="btn" href="../auth/logout.php">Logout</a>
            </div>
        </div>

        <div class="layout">
            <aside class="sidebar">
                <a href="../karyawan/penugasan.php">📋 Daftar Tugas</a>
                <a href="../karyawan/jadwal_kerja.php">📅 Jadwal Kerja</a>
                <a href="../admin/pemesanan.php">📞 Lihat Pemesanan</a>
            </aside>

            <main class="main">
                <div class="grid-cards">
                    <div class="card">
                        <h3>✅ Tugas Aktif</h3>
                        <div class="value">-</div>
                        <div class="small">Belum ada tugas terassign</div>
                    </div>
                    <div class="card">
                        <h3>🚌 Info Armada</h3>
                        <div class="small">Cek ketersediaan kendaraan</div>
                    </div>
                    <div class="card">
                        <h3>🗓️ Jadwal</h3>
                        <div class="small">Lihat jadwal keberangkatan</div>
                    </div>
                </div>

                <div class="section card">
                    <h3>Catatan</h3>
                    <p>Dashboard ini memiliki fitur operasional untuk karyawan. Detail tugas dapat dilihat di menu samping.</p>
                </div>
            </main>
        </div>
    </div>
</body>
</html>