<?php
// logout.php - Proses Logout

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Hapus semua variabel sesi
$_SESSION = [];

// 2. Hapus cookie sesi PHP
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// 3. Hapus cookie remember me
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
    unset($_COOKIE['remember_user']);
}

// 4. Hancurkan sesi
session_destroy();

// 5. Redirect ke login
header('Location: login.php');
exit;