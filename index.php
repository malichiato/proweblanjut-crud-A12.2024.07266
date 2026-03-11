<?php
require_once 'koneksi.php';

$pdo = getConnection();

// Tangani pesan sukses/error dari redirect
$pesan = '';
$tipePesan = '';
if (isset($_GET['pesan'])) {
    $pesan = $_GET['pesan'];
    $tipePesan = $_GET['tipe'] ?? 'success';
}

// Ambil semua data barang
$stmt = $pdo->query("SELECT * FROM barang ORDER BY created_at DESC");
$barangs = $stmt->fetchAll();

// Statistik
$stmtStats = $pdo->query("
    SELECT 
        COUNT(*) as total_barang,
        SUM(jumlah) as total_stok,
        SUM(jumlah * harga) as total_nilai,
        SUM(CASE WHEN jumlah <= 5 THEN 1 ELSE 0 END) as stok_rendah
    FROM barang
");
$stats = $stmtStats->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris — Daftar Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        <div class="navbar-icon">📦</div>
        <div>
            <span>InvenTrack</span>
            <small>Sistem Manajemen Inventaris</small>
        </div>
    </a>
    <ul class="navbar-nav">
        <li><a href="index.php" class="active">🏠 Dashboard</a></li>
        <li><a href="tambah.php">➕ Tambah Barang</a></li>
    </ul>
</nav>

<div class="page-wrapper">

    <!-- HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Daftar Barang</h1>
            <p class="page-subtitle">Kelola stok inventaris dengan mudah dan terorganisir</p>
        </div>
        <a href="tambah.php" class="btn btn-primary">
            ✨ Tambah Barang Baru
        </a>
    </div>

    <!-- ALERT -->
    <?php if ($pesan): ?>
    <div class="alert alert-<?= $tipePesan === 'success' ? 'success' : 'error' ?>">
        <?= $tipePesan === 'success' ? '✅' : '❌' ?>
        <?= htmlspecialchars($pesan) ?>
    </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon lavender">🗂️</div>
            <div>
                <div class="stat-value"><?= number_format($stats['total_barang']) ?></div>
                <div class="stat-label">Jenis Barang</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon mint">📦</div>
            <div>
                <div class="stat-value"><?= number_format($stats['total_stok']) ?></div>
                <div class="stat-label">Total Stok</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon peach">💰</div>
            <div>
                <div class="stat-value"><?= $stats['total_nilai'] ? 'Rp ' . number_format($stats['total_nilai']/1000000, 1) . 'jt' : 'Rp 0' ?></div>
                <div class="stat-label">Nilai Inventaris</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pink">⚠️</div>
            <div>
                <div class="stat-value"><?= number_format($stats['stok_rendah']) ?></div>
                <div class="stat-label">Stok Rendah</div>
            </div>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                📋 Data Inventaris
                <span style="font-size:0.8rem;font-weight:500;color:var(--text-light);">(<?= count($barangs) ?> item)</span>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($barangs)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🏷️</div>
                <h3>Belum Ada Data Barang</h3>
                <p>Mulai tambahkan barang pertama Anda</p>
                <a href="tambah.php" class="btn btn-primary" style="margin-top:1.2rem">➕ Tambah Barang</a>
            </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Status Stok</th>
                            <th>Tanggal Masuk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($barangs as $i => $b): ?>
                        <tr>
                            <td class="td-id"><?= str_pad($b['id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div class="td-name"><?= htmlspecialchars($b['nama_barang']) ?></div>
                                <?php if ($b['deskripsi']): ?>
                                <div style="font-size:0.78rem;color:var(--text-light);margin-top:2px;"><?= htmlspecialchars(mb_strimwidth($b['deskripsi'], 0, 40, '…')) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= number_format($b['jumlah']) ?></strong>
                                <span style="font-size:0.78rem;color:var(--text-light);"> unit</span>
                            </td>
                            <td class="price-cell">
                                <?= formatRupiah((float)$b['harga']) ?>
                            </td>
                            <td><?= badgeStok((int)$b['jumlah']) ?></td>
                            <td>
                                <span class="date-chip">
                                    📅 <?= formatTanggal($b['tanggal_masuk']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="edit.php?id=<?= $b['id'] ?>" class="btn-icon btn-edit" title="Edit">✏️</a>
                                    <button class="btn-icon btn-delete" title="Hapus"
                                        onclick="confirmDelete(<?= $b['id'] ?>, '<?= htmlspecialchars($b['nama_barang'], ENT_QUOTES) ?>')">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- MODAL KONFIRMASI HAPUS -->
<div class="modal-backdrop" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">🗑️</div>
        <div class="modal-title">Hapus Barang?</div>
        <div class="modal-body" id="deleteModalBody">
            Barang ini akan dihapus secara permanen dan tidak dapat dikembalikan.
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeModal()">Batal</button>
            <a href="#" class="btn btn-danger" id="deleteConfirmBtn">Ya, Hapus</a>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="footer">
    <p>InvenTrack · Sistem Manajemen Inventaris · Dibangun dengan PHP & PDO</p>
</footer>

<script>
function confirmDelete(id, nama) {
    document.getElementById('deleteModalBody').textContent = 
        `Anda akan menghapus "${nama}". Tindakan ini tidak dapat dibatalkan.`;
    document.getElementById('deleteConfirmBtn').href = `hapus.php?id=${id}`;
    document.getElementById('deleteModal').classList.add('show');
}
function closeModal() {
    document.getElementById('deleteModal').classList.remove('show');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>