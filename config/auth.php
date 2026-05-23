<?php
// config/auth.php - Cek Sesi Login

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header('Location: index.php?action=login');
    exit;
}