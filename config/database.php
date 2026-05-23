<?php
// config/database.php - Konfigurasi Koneksi Database

define('DB_HOST',    'localhost');
define('DB_NAME',    'inventaris_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

function getConnection(): PDO {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        die("Koneksi database gagal: " . $e->getMessage());
    }
}

// Helper functions
function formatRupiah(float $angka): string {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function formatTanggal(string $tanggal): string {
    $bulan = [
        1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
        7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'
    ];
    $ts = strtotime($tanggal);
    return date('d', $ts) . ' ' . $bulan[(int)date('m', $ts)] . ' ' . date('Y', $ts);
}

function badgeStok(int $jumlah): string {
    if ($jumlah <= 5)      return '<span class="badge badge-low">⚠ Rendah</span>';
    elseif ($jumlah <= 20) return '<span class="badge badge-mid">● Sedang</span>';
    else                   return '<span class="badge badge-high">✓ Cukup</span>';
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}