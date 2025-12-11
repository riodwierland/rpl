<?php
/**
 * Dashboard Utama
 * Halaman dashboard untuk admin, manajer, karyawan, dan pelanggan
 * Menampilkan statistik dan data pemesanan terbaru
 */
require_once __DIR__ . '/config/auth_helper.php';
ensure_session();
include __DIR__ . '/config/database.php';

// Tentukan role dan nama pengguna
$userRole = null;
if (isset($_SESSION['pengguna'])) {
    $user = $_SESSION['pengguna'];
    $userRole = $user['level_akses'] ?? 'admin';
    $displayName = $user['username'] ?? 'Admin';
} elseif (isset($_SESSION['pelanggan'])) {
    $user = $_SESSION['pelanggan'];
    $userRole = 'pelanggan';
    $displayName = $user['nama'] ?? 'Pelanggan';
} else {
    header('Location: auth/login.php');
    exit;
}

// Koneksi ke database
$dbConnected = isset($conn) && $conn ? true : false;

/**
 * Helper function untuk menghitung jumlah data dengan multiple table variants
 */
function try_count($conn, $candidates) {
    foreach ($candidates as $q) {
        try {
            $res = @mysqli_query($conn, $q);
            if ($res) {
                $r = mysqli_fetch_assoc($res);
                return intval($r[array_keys($r)[0]] ?? 0);
            }
        } catch (Exception $e) {
            continue;
        }
    }
    return 0;
}

/**
 * Helper function untuk menjumlahkan uang dengan multiple column variants
 */
function try_sum_money($conn, $candidates) {
    foreach ($candidates as $q) {
        try {
            $res = @mysqli_query($conn, $q);
            if ($res) {
                $r = mysqli_fetch_assoc($res);
                $val = $r[array_keys($r)[0]] ?? 0;
                return $val === null ? 0 : $val;
            }
        } catch (Exception $e) {
            continue;
        }
    }
    return 0;
}

// Inisialisasi statistik
$total_paket = 0;
$pesanan_baru = 0;
$pendapatan_bulan = 0;
$latest_orders = [];

if ($dbConnected) {
    // Total paket wisata
    $total_paket = try_count($conn, [
        'SELECT COUNT(*) AS cnt FROM paket_wisata',
    ]);

    // Pemesanan baru
    $pesanan_baru = try_count($conn, [
        "SELECT COUNT(*) AS cnt FROM pemesanan WHERE status_pemesanan = 'pending'",
    ]);

    // Pendapatan bulan ini
    $pendapatan_bulan = try_sum_money($conn, [
        'SELECT IFNULL(SUM(jumlah_bayar), 0) AS total FROM pembayaran WHERE MONTH(id_transaksi) = MONTH(CURRENT_DATE()) AND YEAR(id_transaksi) = YEAR(CURRENT_DATE())',
        'SELECT IFNULL(SUM(jumlah_bayar), 0) AS total FROM pembayaran',
    ]);

    // Pesanan terbaru
    $res = @mysqli_query($conn, "SELECT p.kode_booking, p.id_pelanggan, pl.nama, p.status_pemesanan, p.tanggal_pesan FROM pemesanan p LEFT JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan ORDER BY p.tanggal_pesan DESC LIMIT 5");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $latest_orders[] = $row;
        }
    }
}

// Format pendapatan sebagai mata uang
if (is_numeric($pendapatan_bulan)) {
    $pendapatan_bulan = 'Rp ' . number_format($pendapatan_bulan, 0, ',', '.');
} else {
    $pendapatan_bulan = 'Rp 0';
}

// Array statistik
$stats = [
    'total_paket' => $total_paket,
    'pesanan_baru' => $pesanan_baru,
    'pendapatan_bulan' => $pendapatan_bulan,
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Travel</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f8fc 0%, #e8f1f8 100%);
            min-height: 100vh;
        }
        
        .topbar {
            background: linear-gradient(135deg, var(--accent) 0%, #1f7acc 100%);
            box-shadow: 0 4px 12px rgba(43, 140, 255, 0.15);
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
            color: #333;
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--muted);
        }
        
        .card .value {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
            margin: 8px 0;
        }
        
        .card .small {
            color: var(--muted);
            font-size: 12px;
        }
        
        .grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
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
        
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        
        .table thead {
            background: #f8fbff;
            border-bottom: 2px solid #e6eefc;
        }
        
        .table th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--accent);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f6ff;
            color: #555;
        }
        
        .table tbody tr:hover {
            background: #f8fbff;
        }
        
        .status-pending {
            background: #fff3e0;
            color: #f57c00;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 11px;
        }
        
        .status-dibayar {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 11px;
        }
        
        .status-selesai {
            background: #e3f2fd;
            color: #1565c0;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 11px;
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
        
        .sidebar a:last-child {
            border-bottom: none;
        }
        
        .sidebar a:hover {
            background: #f8fbff;
            color: var(--accent);
            border-left-color: var(--accent);
            padding-left: 20px;
        }
        
        .layout {
            gap: 24px;
        }
        
        .main {
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Topbar -->
        <div class="topbar">
            <div class="brand">
                <div class="logo">🌍</div>
                <div>
                    <h1>Travel Dashboard</h1>
                    <div class="small">Halo, <?= htmlspecialchars($displayName) ?></div>
                </div>
            </div>
            <div class="actions">
                <a class="btn" href="auth/logout.php">Logout</a>
            </div>
        </div>

        <!-- Layout -->
        <div class="layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <?php if ($userRole === 'admin' || $userRole === 'superadmin'): ?>
                    <a href="admin/dashboard_admin.php">Dashboard Admin</a>
                    <a href="admin/register_user.php">Daftar User Baru</a>
                    <a href="admin/users.php">Kelola Pengguna</a>
                    <a href="admin/armada.php">Data Armada</a>
                    <a href="admin/jadwal.php">Jadwal Perjalanan</a>
                    <a href="admin/pemesanan.php">Data Pemesanan</a>
                <?php elseif ($userRole === 'manajer'): ?>
                    <a href="manajer/dashboard_manajer.php">Dashboard Manajer</a>
                    <a href="manajer/laporan_penjualan.php">Laporan Penjualan</a>
                    <a href="manajer/approve_content.php">Persetujuan Konten</a>
                <?php elseif ($userRole === 'karyawan'): ?>
                    <a href="karyawan/dashboard_karyawan.php">Dashboard Karyawan</a>
                    <a href="karyawan/penugasan.php">Penugasan</a>
                    <a href="karyawan/jadwal_kerja.php">Jadwal Kerja</a>
                <?php else: ?>
                    <a href="pelanggan/dashboard_pelanggan.php">Dashboard Saya</a>
                    <a href="pelanggan/paket.php">Paket Wisata</a>
                    <a href="pelanggan/pesanan.php">Pesanan Saya</a>
                    <a href="pelanggan/promo.php">Promo</a>
                <?php endif; ?>
            </aside>

            <!-- Main Content -->
            <main class="main">
                <!-- Statistics Cards -->
                <div class="grid-cards">
                    <div class="card">
                        <h3>Total Paket</h3>
                        <div class="value"><?= $stats['total_paket'] ?></div>
                        <div class="small">Paket aktif saat ini</div>
                    </div>
                    <div class="card">
                        <h3>Pemesanan Baru</h3>
                        <div class="value"><?= $stats['pesanan_baru'] ?></div>
                        <div class="small">Status pending</div>
                    </div>
                    <div class="card">
                        <h3>Pendapatan Bulanan</h3>
                        <div class="value"><?= $stats['pendapatan_bulan'] ?></div>
                        <div class="small">Bulan ini</div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="section card">
                    <h3>Pesanan Terbaru</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kode Booking</th>
                                <th>Pelanggan</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($latest_orders)): ?>
                            <?php foreach ($latest_orders as $o): ?>
                                <tr>
                                    <td><?= htmlspecialchars($o['kode_booking'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($o['nama'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($o['status_pemesanan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($o['tanggal_pesan'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px; color: var(--muted);">
                                    Belum ada pemesanan atau database tidak terhubung
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="assets/js/dashboard.js"></script>
</body>
</html>
