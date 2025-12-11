-- SQL schema
CREATE TABLE paket_wisata (
    id_paket INT PRIMARY KEY AUTO_INCREMENT,
    nama_paket VARCHAR(100),
    deskripsi TEXT,
    durasi_hari INT,
    harga_dasar DECIMAL(12,2)
);

CREATE TABLE itinerary (
    id_itinerary INT PRIMARY KEY AUTO_INCREMENT,
    id_paket INT,
    hari_ke INT,
    jam_mulai TIME,
    lokasi VARCHAR(255),
    FOREIGN KEY (id_paket) REFERENCES paket_wisata(id_paket)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE pelanggan (
    id_pelanggan INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100),
    kontak VARCHAR(15),
    email VARCHAR(100),
    password VARCHAR(100)
);

CREATE TABLE promo (
    kode_promo VARCHAR(20) PRIMARY KEY,
    periode_mulai DATE,
    nilai_potongan DECIMAL(10,2)
);

CREATE TABLE pemesanan (
    kode_booking VARCHAR(10) PRIMARY KEY,
    id_pelanggan INT,
    kode_promo VARCHAR(20),
    tanggal_pesan DATE,
    status_pemesanan ENUM('pending','dibayar','dibatalkan','selesai'),
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (kode_promo) REFERENCES promo(kode_promo)
        ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE jadwal (
    id_jadwal INT PRIMARY KEY AUTO_INCREMENT,
    kode_booking VARCHAR(10),
    no_kendaraan VARCHAR(15),
    tanggal_keberangkatan DATE,
    tujuan VARCHAR(100),
    FOREIGN KEY (kode_booking) REFERENCES pemesanan(kode_booking)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE armada (
    no_kendaraan VARCHAR(15) PRIMARY KEY,
    kapasitas INT,
    data_sopir VARCHAR(100),
    status_operasional ENUM('aktif','maintenance','nonaktif')
);

CREATE TABLE pembayaran (
    id_transaksi INT PRIMARY KEY AUTO_INCREMENT,
    kode_booking VARCHAR(10),
    jumlah_bayar DECIMAL(12,2),
    metode_bayar VARCHAR(50),
    bukti_transfer VARCHAR(255),
    FOREIGN KEY (kode_booking) REFERENCES pemesanan(kode_booking)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE rating (
    id_rating INT PRIMARY KEY AUTO_INCREMENT,
    id_pemesanan VARCHAR(10),
    id_pelanggan INT,
    nilai_rating INT,
    komentar TEXT,
    tanggal_rating DATETIME,
    FOREIGN KEY (id_pemesanan) REFERENCES pemesanan(kode_booking)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE log_cetak (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    id_pemesanan VARCHAR(10),
    id_pelanggan INT,
    waktu_cetak DATETIME,
    FOREIGN KEY (id_pemesanan) REFERENCES pemesanan(kode_booking)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE pengguna (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50),
    password VARCHAR(100),
    level_akses VARCHAR(20)
);

CREATE TABLE konten_layanan (
    id_itinerary INT,
    id_konten INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(255),
    deskripsi TEXT,
    dibuat_oleh INT,
    status ENUM('draft','published') NOT NULL DEFAULT 'draft',
    FOREIGN KEY (id_itinerary) REFERENCES itinerary(id_itinerary)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (dibuat_oleh) REFERENCES pengguna(id_user)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Table to record manager approvals for content; used to implement approval workflow
CREATE TABLE konten_approval (
    id_approval INT PRIMARY KEY AUTO_INCREMENT,
    id_konten INT,
    id_manager INT,
    tanggal_approval DATETIME,
    FOREIGN KEY (id_konten) REFERENCES konten_layanan(id_konten)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_manager) REFERENCES pengguna(id_user)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE penilaian_kinerja (
    id_penilaian INT PRIMARY KEY AUTO_INCREMENT,
    id_manager INT,
    id_karyawan INT,
    periode DATE,
    nilai_kinerja INT,
    catatan TEXT,
    FOREIGN KEY (id_manager) REFERENCES pengguna(id_user)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_karyawan) REFERENCES pengguna(id_user)
        ON UPDATE CASCADE ON DELETE RESTRICT
);
