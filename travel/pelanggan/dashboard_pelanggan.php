<?php
/**
 * Pelanggan Dashboard - Travel Booking Interface
 * Mirip dengan index.php tapi dengan booking capability
 */
require_once __DIR__ . '/../config/auth_helper.php';
ensure_session();
include __DIR__ . '/../config/database.php';

// Ensure pelanggan session
if (!isset($_SESSION['pelanggan'])) {
    header('Location: ../auth/login.php');
    exit;
}

$customer = $_SESSION['pelanggan'];
$msg = '';
$error = '';

// Load travel packages
$packages = [];
if (isset($conn) && $conn) {
    $res = @mysqli_query($conn, "SELECT * FROM paket_wisata ORDER BY harga_dasar ASC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $packages[] = $row;
        }
    }
}

// Load customer's bookings
$bookings = [];
if (isset($conn) && $conn && isset($customer['id_pelanggan'])) {
    $bid = intval($customer['id_pelanggan']);
    $bres = @mysqli_query($conn, "SELECT p.*, pr.nilai_potongan FROM pemesanan p LEFT JOIN promo pr ON p.kode_promo = pr.kode_promo WHERE p.id_pelanggan = $bid ORDER BY p.tanggal_pesan DESC");
    if ($bres) {
        while ($brow = mysqli_fetch_assoc($bres)) {
            $bookings[] = $brow;
        }
    }
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_action'])) {
    $action = sanitize_input($_POST['book_action']);

    if ($action === 'new_booking' && isset($_POST['id_paket'], $_POST['tgl_keberangkatan'])) {
        $id_paket = intval($_POST['id_paket']);
        $tgl_keberangkatan = sanitize_input($_POST['tgl_keberangkatan']);
        $kode_promo = sanitize_input($_POST['kode_promo'] ?? '');
        $tujuan = sanitize_input($_POST['tujuan'] ?? '');

        if ($id_paket > 0 && !empty($tgl_keberangkatan)) {
            $id_pelanggan = intval($customer['id_pelanggan']);
            $tgl_pesan = date('Y-m-d');
            $kode_booking = 'BK' . date('YmdHis');

            $kode_promo_esc = !empty($kode_promo) ? "'" . mysqli_real_escape_string($conn, $kode_promo) . "'" : 'NULL';
            $tujuan_esc = mysqli_real_escape_string($conn, $tujuan);
            $tgl_keb_esc = mysqli_real_escape_string($conn, $tgl_keberangkatan);

            $insert_q = "INSERT INTO pemesanan (kode_booking, id_pelanggan, kode_promo, tanggal_pesan, status_pemesanan)
                         VALUES ('$kode_booking', $id_pelanggan, $kode_promo_esc, '$tgl_pesan', 'pending')";
            
            if (@mysqli_query($conn, $insert_q)) {
                $jadwal_q = "INSERT INTO jadwal (kode_booking, tanggal_keberangkatan, tujuan)
                             VALUES ('$kode_booking', '$tgl_keb_esc', '$tujuan_esc')";
                @mysqli_query($conn, $jadwal_q);

                $msg = "Pemesanan berhasil dibuat! Kode booking: <strong>$kode_booking</strong>. Silakan lakukan pembayaran untuk mengkonfirmasi.";
                // Refresh bookings
                $bookings = [];
                $bres = @mysqli_query($conn, "SELECT p.*, pr.nilai_potongan FROM pemesanan p LEFT JOIN promo pr ON p.kode_promo = pr.kode_promo WHERE p.id_pelanggan = $id_pelanggan ORDER BY p.tanggal_pesan DESC");
                if ($bres) {
                    while ($brow = mysqli_fetch_assoc($bres)) {
                        $bookings[] = $brow;
                    }
                }
            } else {
                $error = 'Gagal membuat pemesanan. Silakan coba lagi.';
            }
        } else {
            $error = 'Data pemesanan tidak lengkap.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - Travel</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/landing.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f8fc 0%, #e8f1f8 100%);
        }
        
        .customer-topbar {
            background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
            color: #fff;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: none;
            box-shadow: 0 4px 12px rgba(233, 30, 99, 0.15);
        }
        .customer-topbar strong {
            font-size: 18px;
            font-weight: 600;
        }
        .customer-topbar > div:first-child {
            font-size: 13px;
            color: rgba(255,255,255,0.9);
        }
        .customer-topbar a {
            color: #fff;
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .customer-topbar a:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.8);
        }
        .booking-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }
        .booking-modal.active {
            display: flex;
        }
        .booking-modal-content {
            background: #fff;
            padding: 28px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(233, 30, 99, 0.2);
        }
        .booking-modal-content h2 {
            margin: 0 0 20px;
            color: #e91e63;
            font-size: 20px;
        }
        .booking-modal-close {
            float: right;
            cursor: pointer;
            font-size: 28px;
            color: #ccc;
            line-height: 1;
            transition: 0.2s;
        }
        .booking-modal-close:hover {
            color: #e91e63;
        }
        .booking-form label {
            display: block;
            margin-top: 12px;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #333;
        }
        .booking-form input,
        .booking-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #e6eefc;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .booking-form input:focus,
        .booking-form select:focus {
            outline: none;
            border-color: #e91e63;
            box-shadow: 0 0 0 2px rgba(233, 30, 99, 0.1);
        }
        .package-card {
            position: relative;
        }
        .package-btn {
            width: 100%;
            padding: 11px;
            background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            margin-top: 8px;
            transition: all 0.3s;
        }
        .package-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(233, 30, 99, 0.3);
        }
        .bookings-section {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin: 20px auto;
            max-width: 1200px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-left: auto;
            margin-right: auto;
        }
        .booking-item {
            background: linear-gradient(135deg, #f5f8fc 0%, #ede7f6 100%);
            padding: 14px;
            border-left: 4px solid #e91e63;
            margin: 8px 0;
            border-radius: 6px;
            font-size: 13px;
            transition: all 0.2s;
        }
        .booking-item:hover {
            box-shadow: 0 2px 8px rgba(233, 30, 99, 0.1);
        }
        .status-pending {
            color: #ff9800;
            font-weight: 700;
            background: #fff3e0;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .status-dibayar {
            color: #4caf50;
            font-weight: 700;
            background: #e8f5e9;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .status-selesai {
            color: #2196f3;
            font-weight: 700;
            background: #e3f2fd;
            padding: 2px 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Customer Top Bar -->
    <div class="customer-topbar">
        <div>
            <strong>🎟️ Dashboard Pelanggan</strong>
            <div style="font-size:12px;margin-top:4px">Halo, <?= htmlspecialchars($customer['nama'] ?? 'Pelanggan') ?></div>
        </div>
        <div style="display:flex;gap:8px">
            <a href="../index.php">Beranda</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <!-- Messages -->
    <div style="max-width:1200px;margin:0 auto;padding:0 20px;padding-top:20px">
        <?php if (!empty($msg)): ?>
            <div style="background:#efe;color:#0a0;padding:12px;border-radius:6px;margin-bottom:12px;border-left:4px solid #0f0">
                <?= $msg ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div style="background:#fee;color:#a00;padding:12px;border-radius:6px;margin-bottom:12px;border-left:4px solid #f00">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Destinations Section -->
    <section>
        <h2 class="section-heading">🌍 Destinasi Populer</h2>
        <div class="destinations">
            <article class="destination">
                <div class="destination-icon">🏝️</div>
                <h3>Bali</h3>
                <p class="small">Pantai indah, budaya kaya, dan resort mewah menanti Anda di surga tropis ini.</p>
            </article>
            <article class="destination">
                <div class="destination-icon">🏔️</div>
                <h3>Yogyakarta</h3>
                <p class="small">Jelajahi candi Borobudur, Prambanan, dan budaya Jawa yang memukau.</p>
            </article>
            <article class="destination">
                <div class="destination-icon">🌴</div>
                <h3>Lombok</h3>
                <p class="small">Pantai tersembunyi, gunung indah, dan kehidupan lokal yang autentik.</p>
            </article>
            <article class="destination">
                <div class="destination-icon">🏞️</div>
                <h3>Manado</h3>
                <p class="small">Diving kelas dunia dan keindahan alam bawah laut yang spektakuler.</p>
            </article>
        </div>
    </section>

    <!-- My Bookings Section -->
    <section style="max-width:1200px;margin:0 auto;padding:0 20px">
        <h2 class="section-heading">📋 Pemesanan Saya</h2>
        <div class="bookings-section">
            <?php if (empty($bookings)): ?>
                <p style="margin:0;color:var(--muted)">Belum ada pemesanan. Mulai dengan memilih paket wisata di bawah.</p>
            <?php else: ?>
                <?php foreach ($bookings as $b): ?>
                    <div class="booking-item">
                        <strong>Kode: <?= htmlspecialchars($b['kode_booking']) ?></strong>
                        | Status: <span class="status-<?= $b['status_pemesanan'] ?>"><?= ucfirst($b['status_pemesanan']) ?></span>
                        | Tanggal Pesan: <?= htmlspecialchars($b['tanggal_pesan']) ?>
                        <?php if (!empty($b['kode_promo'])): ?>
                            | Promo: <?= htmlspecialchars($b['kode_promo']) ?> (-Rp <?= number_format((float)$b['nilai_potongan'], 0, ',', '.') ?>)
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Packages Section -->
    <section>
        <h2 class="section-heading">✨ Paket Wisata Unggulan</h2>
        <div class="packages">
            <?php if (empty($packages)): ?>
                <p>Belum ada paket wisata tersedia.</p>
            <?php else: ?>
                <?php foreach ($packages as $pkg): ?>
                    <article class="package">
                        <h3><?= htmlspecialchars($pkg['nama_paket']) ?></h3>
                        <p class="small"><?= htmlspecialchars(substr($pkg['deskripsi'] ?? '', 0, 150)) ?></p>
                        <div class="package-price">Rp <?= number_format((float)$pkg['harga_dasar'], 0, ',', '.') ?> / orang</div>
                        <p class="small"><strong>Durasi:</strong> <?= intval($pkg['durasi_hari']) ?> hari</p>
                        <button type="button" class="package-btn" onclick="openBookingModal(<?= intval($pkg['id_paket']) ?>, '<?= htmlspecialchars($pkg['nama_paket']) ?>')">Pesan Sekarang</button>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section>
        <h2 class="section-heading">⭐ Testimoni Pelanggan</h2>
        <div class="testimonials">
            <article class="testimonial">
                <p class="small">"Paket wisata nya sangat memuaskan! Tour guide profesional dan semua fasilitas sesuai janji. Recommended!"</p>
                <div class="testimonial-name">Budi Santoso</div>
                <p class="small">⭐⭐⭐⭐⭐</p>
            </article>
            <article class="testimonial">
                <p class="small">"Pertama kali pakai Travel ini. Prosesnya mudah, harga kompetitif, dan penanganan komplain sangat cepat. Top!"</p>
                <div class="testimonial-name">Siti Nurhaliza</div>
                <p class="small">⭐⭐⭐⭐⭐</p>
            </article>
            <article class="testimonial">
                <p class="small">"Liburan bersama keluarga jadi lebih berkualitas. Staff ramah, destinasi menarik, dan harga fair. Terima kasih!"</p>
                <div class="testimonial-name">Adi Kusuma</div>
                <p class="small">⭐⭐⭐⭐⭐</p>
            </article>
        </div>
    </section>

    <!-- FAQ Section -->
    <section>
        <h2 class="section-heading">❓ Pertanyaan Umum</h2>
        <div class="faq">
            <div class="faq-item">
                <strong>Bagaimana cara membuat pemesanan?</strong>
                <div class="answer">
                    Pilih paket wisata yang Anda inginkan, klik "Pesan Sekarang", isi tanggal keberangkatan, dan lakukan pembayaran. Konfirmasi akan dikirim via email.
                </div>
            </div>
            <div class="faq-item">
                <strong>Apakah ada garansi uang kembali?</strong>
                <div class="answer">
                    Ya, jika Anda membatalkan booking minimal 7 hari sebelum keberangkatan, uang akan dikembalikan 100%. Kurang dari 7 hari ada biaya pembatalan.
                </div>
            </div>
            <div class="faq-item">
                <strong>Berapa lama proses konfirmasi?</strong>
                <div class="answer">
                    Biasanya konfirmasi dilakukan dalam 1×24 jam setelah pembayaran diterima. Anda akan mendapat email berisi detail lengkap perjalanan.
                </div>
            </div>
            <div class="faq-item">
                <strong>Apakah ada group discount?</strong>
                <div class="answer">
                    Ya! Untuk pemesanan 10 orang atau lebih, kami memberikan diskon khusus. Hubungi tim kami untuk penawaran terbaik.
                </div>
            </div>
            <div class="faq-item">
                <strong>Bagaimana dengan asuransi perjalanan?</strong>
                <div class="answer">
                    Asuransi perjalanan dapat ditambahkan saat pemesanan dengan biaya tambahan. Mencakup kesehatan, penundaan, dan perlindungan bagasi.
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="site-footer-content">
            <div class="site-footer-grid">
                <div class="site-footer-item">
                    <strong>Tentang Kami</strong>
                    <p>Travel adalah platform terpercaya untuk mencari dan memesan paket wisata terbaik. Kami berkomitmen memberikan pengalaman liburan yang tak terlupakan.</p>
                </div>
                <div class="site-footer-item">
                    <strong>Kontak & Bantuan</strong>
                    <p>
                        📧 Email: info@travel.com<br>
                        📱 WhatsApp: +62 812 3456 7890<br>
                        ☎️ Telepon: (021) 1234-5678<br>
                        🕒 Jam Operasional: 08:00 - 20:00 WIB
                    </p>
                </div>
                <div class="site-footer-item">
                    <strong>Links Penting</strong>
                    <p>
                        <a href="../index.php" style="color:var(--accent);text-decoration:none">Beranda</a><br>
                        <a href="../auth/logout.php" style="color:var(--accent);text-decoration:none">Logout</a><br>
                        <a href="#" style="color:var(--accent);text-decoration:none">Kebijakan Privasi</a>
                    </p>
                </div>
            </div>
            <div class="site-footer-divider">
                &copy; <?= date('Y') ?> Travel — Jelajahi Dunia Bersama Kami. Semua hak cipta dilindungi.
            </div>
        </div>
    </footer>

    <!-- Booking Modal -->
    <div id="bookingModal" class="booking-modal">
        <div class="booking-modal-content">
            <span class="booking-modal-close" onclick="closeBookingModal()">&times;</span>
            <h2>Pesan Paket Wisata</h2>
            <form method="POST" class="booking-form">
                <input type="hidden" name="book_action" value="new_booking">
                <input type="hidden" name="id_paket" id="paketId">
                
                <label><strong>Paket:</strong></label>
                <input type="text" id="paketNama" readonly style="background:#f0f6ff">
                
                <label><strong>Tanggal Keberangkatan:</strong></label>
                <input type="date" name="tgl_keberangkatan" required>
                
                <label><strong>Kode Promo (opsional):</strong></label>
                <input type="text" name="kode_promo" placeholder="PROMO123">
                
                <label><strong>Tujuan (opsional):</strong></label>
                <input type="text" name="tujuan" placeholder="Destinasi utama">
                
                <button type="submit" class="package-btn" style="margin-top:16px">Lanjutkan Pemesanan</button>
            </form>
        </div>
    </div>

    <script>
        function openBookingModal(paketId, paketNama) {
            document.getElementById('paketId').value = paketId;
            document.getElementById('paketNama').value = paketNama;
            document.getElementById('bookingModal').classList.add('active');
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').classList.remove('active');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('bookingModal');
            if (event.target == modal) {
                modal.classList.remove('active');
            }
        }
    </script>
</body>
</html>
