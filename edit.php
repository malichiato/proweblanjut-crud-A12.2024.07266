<?php
require_once 'auth.php';
require_once 'koneksi.php';

$pdo = getConnection();
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php?pesan=' . urlencode('ID barang tidak valid.') . '&tipe=error');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM barang WHERE id = :id");
$stmt->execute([':id' => $id]);
$barang = $stmt->fetch();

if (!$barang) {
    header('Location: index.php?pesan=' . urlencode('Barang tidak ditemukan.') . '&tipe=error');
    exit;
}

$errors = [];
$input  = $barang;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['nama_barang']   = trim($_POST['nama_barang']   ?? '');
    $input['jumlah']        = trim($_POST['jumlah']        ?? '');
    $input['harga']         = trim($_POST['harga']         ?? '');
    $input['tanggal_masuk'] = trim($_POST['tanggal_masuk'] ?? '');
    $input['deskripsi']     = trim($_POST['deskripsi']     ?? '');

    if (empty($input['nama_barang']))                                  $errors[] = 'Nama barang wajib diisi.';
    if (!is_numeric($input['jumlah']) || (int)$input['jumlah'] < 0)   $errors[] = 'Jumlah harus berupa angka positif.';
    if (!is_numeric($input['harga'])  || (float)$input['harga'] < 0)  $errors[] = 'Harga harus berupa angka positif.';
    if (empty($input['tanggal_masuk']))                                $errors[] = 'Tanggal masuk wajib diisi.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE barang SET nama_barang=:nm, jumlah=:jml, harga=:hrg, tanggal_masuk=:tgl, deskripsi=:desk WHERE id=:id");
        $stmt->execute([
            ':nm'   => $input['nama_barang'],
            ':jml'  => (int)$input['jumlah'],
            ':hrg'  => (float)$input['harga'],
            ':tgl'  => $input['tanggal_masuk'],
            ':desk' => $input['deskripsi'] ?: null,
            ':id'   => $id,
        ]);
        header('Location: index.php?pesan=' . urlencode('Data "' . $input['nama_barang'] . '" berhasil diperbarui!') . '&tipe=success');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris — Edit Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        <div class="navbar-icon">📦</div>
        <div><span>InvenTrack</span><small>Sistem Manajemen Inventaris</small></div>
    </a>
    <div style="display:flex;align-items:center;gap:12px;">
        <ul class="navbar-nav">
            <li><a href="index.php">🏠 Dashboard</a></li>
            <li><a href="tambah.php">➕ Tambah Barang</a></li>
        </ul>
        <a href="logout.php" class="btn btn-danger" style="padding:6px 14px;font-size:0.85rem;">🚪 Logout</a>
    </div>
</nav>

<div class="page-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Barang</h1>
            <p class="page-subtitle">Perbarui informasi barang yang sudah ada</p>
        </div>
    </div>

    <div class="form-card">
        <div style="font-size:0.82rem;color:var(--text-light);margin-bottom:1.5rem;display:flex;align-items:center;gap:6px;">
            <a href="index.php" style="color:var(--text-light);text-decoration:none;">🏠 Dashboard</a>
            <span>›</span>
            <span style="color:var(--text-dark);font-weight:600;">Edit Barang</span>
        </div>

        <div style="background:var(--lavender-soft);border-radius:10px;padding:12px 16px;margin-bottom:1.5rem;font-size:0.86rem;color:var(--text-mid);">
            ✏️ Mengedit: <strong><?= htmlspecialchars($barang['nama_barang']) ?></strong>
            <span style="color:var(--text-light);font-size:0.8rem;"> (ID #<?= str_pad($id, 3, '0', STR_PAD_LEFT) ?>)</span>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            ❌ <div><?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?></div>
        </div>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?= $id ?>" novalidate>
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama_barang">Nama Barang <span style="color:#f4b8c8">*</span></label>
                    <input type="text" id="nama_barang" name="nama_barang" class="form-control"
                        value="<?= htmlspecialchars($input['nama_barang']) ?>" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label for="jumlah">Jumlah (unit) <span style="color:#f4b8c8">*</span></label>
                        <input type="number" id="jumlah" name="jumlah" class="form-control"
                            min="0" value="<?= htmlspecialchars($input['jumlah']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga Satuan (Rp) <span style="color:#f4b8c8">*</span></label>
                        <input type="number" id="harga" name="harga" class="form-control"
                            min="0" step="100" value="<?= htmlspecialchars($input['harga']) ?>" required>
                        <div class="form-hint" id="hargaPreview" style="margin-top:6px;font-weight:600;color:var(--text-mid);"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="tanggal_masuk">Tanggal Masuk <span style="color:#f4b8c8">*</span></label>
                    <input type="date" id="tanggal_masuk" name="tanggal_masuk" class="form-control"
                        value="<?= htmlspecialchars($input['tanggal_masuk']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi <span style="color:var(--text-light);font-weight:400;">(opsional)</span></label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($input['deskripsi'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="index.php" class="btn btn-secondary">← Batal</a>
            </div>
        </form>
    </div>
</div>

<footer class="footer"><p>InvenTrack · Sistem Manajemen Inventaris · Dibangun dengan PHP & PDO</p></footer>

<script>
const hargaInput = document.getElementById('harga');
const preview = document.getElementById('hargaPreview');
function updatePreview() {
    const val = parseFloat(hargaInput.value);
    preview.textContent = val > 0 ? '≈ Rp ' + val.toLocaleString('id-ID') : '';
}
hargaInput.addEventListener('input', updatePreview);
updatePreview();
</script>
</body>
</html>