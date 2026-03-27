<?php
require_once 'auth.php';
require_once 'koneksi.php';

$pdo = getConnection();
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php?pesan=' . urlencode('ID barang tidak valid.') . '&tipe=error');
    exit;
}

$stmt = $pdo->prepare("SELECT nama_barang FROM barang WHERE id = :id");
$stmt->execute([':id' => $id]);
$barang = $stmt->fetch();

if (!$barang) {
    header('Location: index.php?pesan=' . urlencode('Barang tidak ditemukan.') . '&tipe=error');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM barang WHERE id = :id");
$stmt->execute([':id' => $id]);

header('Location: index.php?pesan=' . urlencode('Barang "' . $barang['nama_barang'] . '" berhasil dihapus.') . '&tipe=success');
exit;