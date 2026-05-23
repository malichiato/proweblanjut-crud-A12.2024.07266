<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvenTrack — Daftar Barang</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .user-badge { display:flex; align-items:center; gap:10px; }
        .user-info  { display:flex; align-items:center; gap:8px; background:var(--lavender-soft); padding:6px 14px; border-radius:999px; font-size:0.85rem; font-weight:600; color:var(--text-mid); }
        .user-avatar { width:28px; height:28px; background:linear-gradient(135deg,var(--lavender),var(--pink)); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:14px; color:white; font-weight:700; }
        .barang-img { width:48px; height:48px; object-fit:cover; border-radius:8px; border:1px solid var(--border); }
        .no-img     { width:48px; height:48px; background:var(--lavender-soft); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:20px; border:1px solid var(--border); }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php?action=index" class="navbar-brand">
        <div class="navbar-icon">📦</div>
        <div><span>InvenTrack</span><small>Sistem Manajemen Inventaris</small></div>
    </a>
    <div style="display:flex;align-items:center;gap:12px;">
        <ul class="navbar-nav">
            <li><a href="index.php?action=index" class="active">🏠 Dashboard</a></li>
            <li><a href="index.php?action=create">➕ Tambah Barang</a></li>
        </ul>
        <div class="user-badge">
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <?= htmlspecialchars($_SESSION['username']) ?>
            </div>
            <a href="index.php?action=logout" class="btn btn-danger" style="padding:6px 14px;font-size:0.85rem;">🚪 Logout</a>
        </div>
    </div>
</nav>

<div class="page-wrapper">

    <div class="page-header">
        <div>
            <h1 class="page-title">Daftar Barang</h1>
            <p class="page-subtitle">Selamat datang, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>! Kelola stok inventaris dengan mudah.</p>
        </div>
        <a href="index.php?action=create" class="btn btn-primary">✨ Tambah Barang Baru</a>
    </div>

    <?php if ($pesan): ?>
    <div class="alert alert-<?= $tipe === 'success' ? 'success' : 'error' ?>">
        <?= $tipe === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($pesan) ?>
    </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon lavender">🗂️</div>
            <div><div class="stat-value"><?= number_format($stats['total_barang']) ?></div><div class="stat-label">Jenis Barang</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon mint">📦</div>
            <div><div class="stat-value"><?= number_format($stats['total_stok']) ?></div><div class="stat-label">Total Stok</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon peach">💰</div>
            <div><div class="stat-value"><?= $stats['total_nilai'] ? 'Rp ' . number_format($stats['total_nilai']/1000000, 1) . 'jt' : 'Rp 0' ?></div><div class="stat-label">Nilai Inventaris</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pink">⚠️</div>
            <div><div class="stat-value"><?= number_format($stats['stok_rendah']) ?></div><div class="stat-label">Stok Rendah</div></div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📋 Data Inventaris <span style="font-size:0.8rem;font-weight:500;color:var(--text-light);">(<?= count($barangs) ?> item)</span></div>
        </div>
        <div class="card-body">
            <?php if (empty($barangs)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🏷️</div>
                <h3>Belum Ada Data Barang</h3>
                <p>Mulai tambahkan barang pertama Anda</p>
                <a href="index.php?action=create" class="btn btn-primary" style="margin-top:1.2rem">➕ Tambah Barang</a>
            </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Gambar</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Status Stok</th>
                            <th>Tanggal Masuk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($barangs as $b): ?>
                        <tr>
                            <td class="td-id"><?= str_pad($b['id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <?php if ($b['gambar'] && file_exists(__DIR__ . '/../../uploads/' . $b['gambar'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($b['gambar']) ?>" alt="<?= htmlspecialchars($b['nama_barang']) ?>" class="barang-img">
                                <?php else: ?>
                                <div class="no-img">📦</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="td-name"><?= htmlspecialchars($b['nama_barang']) ?></div>
                                <?php if ($b['deskripsi']): ?>
                                <div style="font-size:0.78rem;color:var(--text-light);margin-top:2px;"><?= htmlspecialchars(mb_strimwidth($b['deskripsi'], 0, 40, '…')) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= number_format($b['jumlah']) ?></strong> <span style="font-size:0.78rem;color:var(--text-light);">unit</span></td>
                            <td class="price-cell"><?= formatRupiah((float)$b['harga']) ?></td>
                            <td><?= badgeStok((int)$b['jumlah']) ?></td>
                            <td><span class="date-chip">📅 <?= formatTanggal($b['tanggal_masuk']) ?></span></td>
                            <td>
                                <div class="actions">
                                    <a href="index.php?action=edit&id=<?= $b['id'] ?>" class="btn-icon btn-edit" title="Edit">✏️</a>
                                    <button class="btn-icon btn-delete" title="Hapus"
                                        onclick="confirmDelete(<?= $b['id'] ?>, '<?= htmlspecialchars($b['nama_barang'], ENT_QUOTES) ?>')">🗑️</button>
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

<!-- MODAL HAPUS -->
<div class="modal-backdrop" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">🗑️</div>
        <div class="modal-title">Hapus Barang?</div>
        <div class="modal-body" id="deleteModalBody">Barang ini akan dihapus secara permanen.</div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeModal()">Batal</button>
            <a href="#" class="btn btn-danger" id="deleteConfirmBtn">Ya, Hapus</a>
        </div>
    </div>
</div>

<footer class="footer"><p>InvenTrack · MVC · Dibangun dengan PHP & PDO</p></footer>

<script>
function confirmDelete(id, nama) {
    document.getElementById('deleteModalBody').textContent = `Anda akan menghapus "${nama}".`;
    document.getElementById('deleteConfirmBtn').href = `index.php?action=destroy&id=${id}`;
    document.getElementById('deleteModal').classList.add('show');
}
function closeModal() { document.getElementById('deleteModal').classList.remove('show'); }
document.getElementById('deleteModal').addEventListener('click', function(e) { if(e.target===this) closeModal(); });
</script>
</body>
</html>
