<?php
// auth.php - Helper untuk cek sesi login
// Tambahkan require_once 'auth.php'; di bagian atas setiap halaman yang dilindungi

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php?pesan=Silakan+login+terlebih+dahulu');
    exit;
}