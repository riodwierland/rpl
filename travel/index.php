<?php
/**
 * Landing Page - Travel Website
 * Menampilkan informasi umum dan destinasi wisata
 */
// Start session safely
if (file_exists(__DIR__ . '/config/auth_helper.php')) {
    require_once __DIR__ . '/config/auth_helper.php';
}
ensure_session();

// Redirect ke dashboard jika sudah login
if (isset($_SESSION['pengguna']) || isset($_SESSION['pelanggan'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Travel - Platform booking paket wisata terbaik">
    <title>Travel - Jelajahi Dunia Bersama Kami</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <style>
        .landing-topbar {
            background: var(--accent);
            color: #fff;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .landing-topbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 18px;
            font-weight: 600;
        }
        .landing-topbar-links {
            display: flex;
            gap: 8px;
        }
        .landing-topbar a {
            color: #fff;
            text-decoration: none;
            padding: 6px 14px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 6px;
            font-size: 13px;
            transition: 0.2s;
        }
        .landing-topbar a:hover {
            background: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <!-- Landing Topbar -->
    <div class="landing-topbar">
        <div class="landing-topbar-brand">
            🌍 Travel - Jelajahi Dunia Bersama Kami
        </div>
        <div class="landing-topbar-links">
            <a href="auth/login.php">Masuk</a>
            <a href="auth/register.php">Daftar</a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-inner">
            <div class="hero-card">
                <h1>Rencanakan Perjalanan Impian Anda dengan Mudah</h1>
                <p class="lead">Temukan paket wisata terbaik, kelola pemesanan dengan aman, dan nikmati layanan profesional dari tim kami.</p>
                <div style="margin-top:18px;font-size:13px;color:var(--muted)">
                    Butuh bantuan? Hubungi kami di <strong>+62 812 3456 7890</strong>
                </div>
            </div>

            <aside style="display:flex;flex-direction:column;gap:12px">
                <div class="hero-card small">
                    <h3 style="margin:0 0 8px">Kenapa Pilih Kami?</h3>
                    <ul class="small" style="margin:0;padding-left:18px">
                        <li>Konten paket terkurasi</li>
                        <li>Proses pemesanan aman</li>
                        <li>Tim dukungan profesional</li>
                    </ul>
                </div>

                <div class="hero-card small">
                    <h3 style="margin:0 0 8px">Akses Cepat</h3>
                    <div class="small">Jika Anda admin, login untuk melihat dashboard penuh dan kelola paket & pemesanan.</div>
                </div>
            </aside>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <article class="feature">
            <h4>🎯 Paket Wisata Lengkap</h4>
            <p class="small">Pilih dari ratusan paket wisata domestik dan internasional sesuai anggaran dan minat Anda.</p>
        </article>
        <article class="feature">
            <h4>📱 Reservasi Mudah</h4>
            <p class="small">Pesan dan kelola pemesanan dengan mudah melalui dashboard personal Anda.</p>
        </article>
        <article class="feature">
            <h4>🔒 Pembayaran Aman</h4>
            <p class="small">Metode pembayaran terpercaya dengan sistem keamanan tingkat internasional.</p>
        </article>
    </section>

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

    <!-- Packages Section -->
    <section>
        <h2 class="section-heading">✨ Paket Wisata Unggulan</h2>
        <div class="packages">
            <article class="package">
                <h3>🌅 Bali Paradise 5D/4N</h3>
                <p class="small">Pantai Kuta, Ubud, dan Tari Barong menanti. Paket all-inclusive dengan hotel bintang 4.</p>
                <div class="package-price">Rp 3.500.000 / orang</div>
                <p class="small"><strong>Include:</strong> Hotel, Breakfast, Tour Guide, Transport</p>
            </article>
            <article class="package">
                <h3>🏛️ Candi Sepanjang Zaman 4D/3N</h3>
                <p class="small">Borobudur sunrise, Prambanan malam, dan kuliner Jawa yang lezat.</p>
                <div class="package-price">Rp 2.800.000 / orang</div>
                <p class="small"><strong>Include:</strong> Hotel, Breakfast, Tour Guide, Transport</p>
            </article>
            <article class="package">
                <h3>🏝️ Lombok Beach 3D/2N</h3>
                <p class="small">Gili Trawangan, Senggigi, dan snorkeling dengan harga terjangkau.</p>
                <div class="package-price">Rp 2.200.000 / orang</div>
                <p class="small"><strong>Include:</strong> Hotel, Breakfast, Tour, Transport</p>
            </article>
            <article class="package">
                <h3>🤿 Manado Diving 4D/3N</h3>
                <p class="small">Diving di Bunaken, keanekaragaman laut, dan pengalaman tak terlupakan.</p>
                <div class="package-price">Rp 3.200.000 / orang</div>
                <p class="small"><strong>Include:</strong> Hotel, Meals, Dive Trips, Guide</p>
            </article>
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
                    Daftar akun terlebih dahulu, pilih paket wisata yang Anda inginkan, isi data diri, dan lakukan pembayaran. Konfirmasi akan dikirim via email.
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
                        <a href="index.php" style="color:var(--accent);text-decoration:none">Beranda</a><br>
                        <a href="auth/login.php" style="color:var(--accent);text-decoration:none">Login</a><br>
                        <a href="auth/register.php" style="color:var(--accent);text-decoration:none">Daftar</a><br>
                        <a href="#" style="color:var(--accent);text-decoration:none">Kebijakan Privasi</a>
                    </p>
                </div>
            </div>
            <div class="site-footer-divider">
                &copy; <?= date('Y') ?> Travel — Jelajahi Dunia Bersama Kami. Semua hak cipta dilindungi.
            </div>
        </div>
    </footer>

    <script src="assets/js/landing.js"></script>
</body>
</html>