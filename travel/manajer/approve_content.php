++ <?php
/**
 * Persetujuan Konten Layanan - Manajer
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
$msg = '';
// Approval threshold: number of manager approvals required to auto-publish
$approval_threshold = 1;

// Approve action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    $id_konten = intval($_POST['approve_id']);
    if ($id_konten > 0 && isset($conn) && $conn) {
        // Ensure approval table exists
        $create = "CREATE TABLE IF NOT EXISTS konten_approval (
            id_approval INT PRIMARY KEY AUTO_INCREMENT,
            id_konten INT,
            id_manager INT,
            tanggal_approval DATETIME
        )";
        @mysqli_query($conn, $create);

        // Prevent duplicate approvals
        $id_konten_esc = mysqli_real_escape_string($conn, (string)$id_konten);
        $manager_id = intval($user['id_user'] ?? 0);
        $exists = @mysqli_query($conn, "SELECT * FROM konten_approval WHERE id_konten = '$id_konten_esc' AND id_manager = $manager_id");
        if ($exists && mysqli_num_rows($exists) === 0) {
            $now = date('Y-m-d H:i:s');
            @mysqli_query($conn, "INSERT INTO konten_approval (id_konten, id_manager, tanggal_approval) VALUES ($id_konten, $manager_id, '$now')");
            $msg = 'Konten berhasil disetujui.';

            // After inserting, check count and auto-publish if threshold reached
            $cntq = @mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM konten_approval WHERE id_konten = $id_konten");
            $cnt = 0;
            if ($cntq) {
                $crow = mysqli_fetch_assoc($cntq);
                $cnt = intval($crow['cnt'] ?? 0);
            }

            // Ensure konten_layanan has status column
            $colq = @mysqli_query($conn, "SHOW COLUMNS FROM konten_layanan LIKE 'status'");
            if (!$colq || mysqli_num_rows($colq) === 0) {
                @mysqli_query($conn, "ALTER TABLE konten_layanan ADD COLUMN status ENUM('draft','published') NOT NULL DEFAULT 'draft'");
            }

            if ($cnt >= $approval_threshold) {
                @mysqli_query($conn, "UPDATE konten_layanan SET status = 'published' WHERE id_konten = $id_konten");
                $msg .= ' Konten telah dipublikasikan otomatis.';
            }
        } else {
            $msg = 'Anda sudah menyetujui konten ini.';
        }
    }
}

// Load konten layanan
$contents = [];
$approvals = [];
if (isset($conn) && $conn) {
    $q = @mysqli_query($conn, "SELECT k.*, p.username AS pembuat FROM konten_layanan k LEFT JOIN pengguna p ON k.dibuat_oleh = p.id_user ORDER BY k.id_konten DESC");
    if ($q) while ($r = mysqli_fetch_assoc($q)) $contents[] = $r;

    $aq = @mysqli_query($conn, "SELECT id_konten, COUNT(*) AS cnt FROM konten_approval GROUP BY id_konten");
    if ($aq) while ($a = mysqli_fetch_assoc($aq)) $approvals[intval($a['id_konten'])] = intval($a['cnt']);
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Persetujuan Konten - Manajer</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="container">
        <div class="topbar">
            <div class="brand">
                <div class="logo">TR</div>
                <div>
                    <h1>Persetujuan Konten Layanan</h1>
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
                    <?php if (!empty($msg)): ?>
                        <div class="success-msg"><?= htmlspecialchars($msg) ?></div>
                    <?php endif; ?>

                    <?php if (empty($contents)): ?>
                        <p>Belum ada konten layanan yang diajukan.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr><th>ID</th><th>Judul</th><th>Dibuat Oleh</th><th>Jumlah Persetujuan</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($contents as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['id_konten']) ?></td>
                                    <td><?= htmlspecialchars($c['judul']) ?></td>
                                    <td><?= htmlspecialchars($c['pembuat'] ?? 'Unknown') ?></td>
                                    <td><?= intval($approvals[intval($c['id_konten'])] ?? 0) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline">
                                            <input type="hidden" name="approve_id" value="<?= intval($c['id_konten']) ?>">
                                            <button type="submit" class="btn">Setujui</button>
                                        </form>
                                    </td>
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
