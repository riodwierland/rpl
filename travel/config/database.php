<?php
/**
 * Database Configuration
 * Konfigurasi koneksi ke database MySQL
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'rpl';

// Buat koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$conn) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}
