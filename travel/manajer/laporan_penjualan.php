++ <?php
/**
 * Laporan Penjualan - Manajer
 */
require_once __DIR__ . '/../config/auth_helper.php';
ensure_session();
include __DIR__ . '/../config/database.php';

// Permission check
$role = $_SESSION['pengguna']['level_akses'] ?? null;
if (!isset($_SESSION['pengguna']) || !in_array($role, ['manajer','admin'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $_SESSION['pengguna'];

// Date range filter and export handling
$sales_by_month = [];
$recent = [];
$date_from = $_GET['from'] ?? '';
$date_to = $_GET['to'] ?? '';

// Build WHERE clause for tanggal_pesan if provided
$where = '';
if (!empty($date_from) && !empty($date_to)) {
    $from = mysqli_real_escape_string($conn, $date_from);
    $to = mysqli_real_escape_string($conn, $date_to);
    $where = "WHERE p.tanggal_pesan BETWEEN '$from' AND '$to'";
}

if (isset($conn) && $conn) {
    $sql = "SELECT DATE_FORMAT(p.tanggal_pesan, '%Y-%m') AS bulan, SUM(pay.jumlah_bayar) AS total
            FROM pembayaran pay
            JOIN pemesanan p ON pay.kode_booking = p.kode_booking
            $where
            GROUP BY bulan
            ORDER BY bulan DESC";
    $res = @mysqli_query($conn, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $sales_by_month[] = $row;
        }
    }

    // Recent payments (apply same date filter)
    $recent_sql = "SELECT pay.*, p.tanggal_pesan, p.id_pelanggan FROM pembayaran pay JOIN pemesanan p ON pay.kode_booking = p.kode_booking $where ORDER BY pay.id_transaksi DESC LIMIT 50";
    $rq = @mysqli_query($conn, $recent_sql);
    if ($rq) {
        while ($r = mysqli_fetch_assoc($rq)) $recent[] = $r;
    }
}

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Send CSV of recent payments
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=laporan_penjualan.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id_transaksi', 'kode_booking', 'jumlah_bayar', 'metode_bayar', 'tanggal_pesan', 'id_pelanggan']);
    foreach ($recent as $row) {
        fputcsv($out, [
            $row['id_transaksi'] ?? '',
            $row['kode_booking'] ?? '',
            $row['jumlah_bayar'] ?? '',
            $row['metode_bayar'] ?? '',
            $row['tanggal_pesan'] ?? '',
            $row['id_pelanggan'] ?? ''
        ]);
    }
    fclose($out);
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Laporan Penjualan - Manajer</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="container">
        <div class="topbar">
            <div class="brand">
                <div class="logo">TR</div>
                <div>
                    <h1>Laporan Penjualan</h1>
                    <div class="small">Halo, <?= htmlspecialchars($user['username'] ?? '') ?></div>
                </div>
            </div>
            <div class="actions">
                <a class="btn" href="../auth/logout.php">Logout</a>
            </div>
        </div>

        <div class="layout">
            <aside class="sidebar">
                <a href="../admin/pemesanan.php">Data Pemesanan</a>
                <a href="../admin/armada.php">Data Armada</a>
                <a href="../admin/jadwal.php">Jadwal Perjalanan</a>
                <hr>
                <a href="laporan_penjualan.php">Laporan Penjualan</a>
                <a href="approve_content.php">Persetujuan Konten Layanan</a>
            </aside>

            <main class="main">
                <div class="card">
                    <h3>Ringkasan Penjualan per Bulan</h3>
                    <form method="GET" class="small" style="margin-bottom:12px">
                        <label>Filter tanggal: </label>
                        <input type="date" name="from" value="<?= htmlspecialchars($date_from) ?>"> -
                        <input type="date" name="to" value="<?= htmlspecialchars($date_to) ?>">
                        <button type="submit" class="btn">Terapkan</button>
                        <a class="btn" href="?<?= http_build_query(array_filter(['from'=>$date_from,'to'=>$date_to,'export'=>'csv'])) ?>">Export CSV</a>
                    </form>

                    <?php if (empty($sales_by_month)): ?>
                        <p>Tidak ada data penjualan.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr><th>Bulan</th><th>Total (Rp)</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($sales_by_month as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['bulan']) ?></td>
                                    <td><?= number_format((float)$s['total'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3>Pembayaran Terbaru</h3>
                    <?php if (empty($recent)): ?>
                        <p>Tidak ada pembayaran tercatat.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr><th>ID</th><th>Kode Booking</th><th>Jumlah</th><th>Tanggal Pesan</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($recent as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['id_transaksi']) ?></td>
                                    <td><?= htmlspecialchars($p['kode_booking']) ?></td>
                                    <td><?= number_format((float)$p['jumlah_bayar'],2,',','.') ?></td>
                                    <td><?= htmlspecialchars($p['tanggal_pesan'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
