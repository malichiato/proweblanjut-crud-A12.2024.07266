<?php
// auth.php - Proteksi ketat: Wajib login setiap sesi baru

// Mengatur agar session cookie mati saat browser ditutup
session_set_cookie_params(0); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * LOGIKA PROTEKSI:
 * Jika variabel session 'username' tidak ada, berarti user belum login
 * atau browser baru saja dibuka kembali (sesi lama sudah hangus).
 */
if (!isset($_SESSION['username'])) {
    // Arahkan ke login dengan pesan informatif
    header('Location: login.php?pesan=' . urlencode('Sesi telah berakhir. Silakan login kembali.'));
    exit;
}

// Opsional: Cek User-Agent untuk mencegah Session Hijacking sederhana
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} else {
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
}
?>