<?php
// logout.php - Proses Logout & Hancurkan Sesi

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simpan username untuk pesan perpisahan
$username = $_SESSION['username'] ?? 'Pengguna';

// 1. Hapus semua variabel sesi
$_SESSION = [];

// 2. Hapus cookie sesi (jika ada)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// 3. Hancurkan sesi sepenuhnya
session_destroy();

// 4. Redirect ke halaman login
header('Location: login.php?pesan=' . urlencode('Anda telah berhasil logout. Sampai jumpa, ' . $username . '!'));
exit;