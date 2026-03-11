<?php
require_once 'koneksi.php';

$errors = [];
$input  = ['nama_barang'=>'','jumlah'=>'','harga'=>'','tanggal_masuk'=>date('Y-m-d'),'deskripsi'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & sanitasi input
    $input['nama_barang']   = trim($_POST['nama_barang']   ?? '');
    $input['jumlah']        = trim($_POST['jumlah']        ?? '');
    $input['harga']         = trim($_POST['harga']         ?? '');
    $input['tanggal_masuk'] = trim($_POST['tanggal_masuk'] ?? '');
    $input['deskripsi']     = trim($_POST['deskripsi']     ?? '');

    // Validasi
    if (empty($input['nama_barang']))                         $errors[] = 'Nama barang wajib diisi.';
    if (!is_numeric($input['jumlah']) || (int)$input['jumlah'] < 0)  $errors[] = 'Jumlah harus berupa angka positif.';
    if (!is_numeric($input['harga'])  || (float)$input['harga'] < 0) $errors[] = 'Harga harus berupa angka positif.';
    if (empty($input['tanggal_masuk']))                       $errors[] = 'Tanggal masuk wajib diisi.';

    if (empty($errors)) {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO barang (nama_barang, jumlah, harga, tanggal_masuk, deskripsi)
            VALUES (:nama_barang, :jumlah, :harga, :tanggal_masuk, :deskripsi)
        ");
        $stmt->execute([
            ':nama_barang'   => $input['nama_barang'],
            ':jumlah'        => (int)$input['jumlah'],
            ':harga'         => (float)$input['harga'],
            ':tanggal_masuk' => $input['tanggal_masuk'],
            ':deskripsi'     => $input['deskripsi'] ?: null,
        ]);

        header('Location: index.php?pesan=' . urlencode('Barang "' . $input['nama_barang'] . '" berhasil ditambahkan!') . '&tipe=success');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris — Tambah Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        <div class="navbar-icon">📦</div>
        <div>
            <span>InvenTrack</span>
            <small>Sistem Manajemen Inventaris</small>
        </div>
    </a>
    <ul class="navbar-nav">
        <li><a href="index.php">🏠 Dashboard</a></li>
        <li><a href="tambah.php" class="active">➕ Tambah Barang</a></li>
    </ul>
</nav>

<div class="page-wrapper">

    <div class="page-header">
        <div>
            <h1 class="page-title">Tambah Barang Baru</h1>
            <p class="page-subtitle">Lengkapi form di bawah untuk menambah barang ke inventaris</p>
        </div>
    </div>

    <div class="form-card">

        <!-- BREADCRUMB -->
        <div style="font-size:0.82rem;color:var(--text-light);margin-bottom:1.5rem;display:flex;align-items:center;gap:6px;">
            <a href="index.php" style="color:var(--text-light);text-decoration:none;">🏠 Dashboard</a>
            <span>›</span>
            <span style="color:var(--text-dark);font-weight:600;">Tambah Barang</span>
        </div>

        <!-- ERRORS -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            ❌ <div>
                <?php foreach ($errors as $e): ?>
                <div><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" action="tambah.php" novalidate>
            <div class="form-grid">

                <div class="form-group">
                    <label for="nama_barang">Nama Barang <span style="color:#f4b8c8">*</span></label>
                    <input type="text" id="nama_barang" name="nama_barang"
                        class="form-control"
                        placeholder="cth. Laptop Asus VivoBook 15"
                        value="<?= htmlspecialchars($input['nama_barang']) ?>"
                        required>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label for="jumlah">Jumlah (unit) <span style="color:#f4b8c8">*</span></label>
                        <input type="number" id="jumlah" name="jumlah"
                            class="form-control"
                            placeholder="0"
                            min="0"
                            value="<?= htmlspecialchars($input['jumlah']) ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga Satuan (Rp) <span style="color:#f4b8c8">*</span></label>
                        <input type="number" id="harga" name="harga"
                            class="form-control"
                            placeholder="0"
                            min="0"
                            step="100"
                            value="<?= htmlspecialchars($input['harga']) ?>"
                            required>
                        <div class="form-hint" id="hargaPreview" style="margin-top:6px;font-weight:600;color:var(--text-mid);"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tanggal_masuk">Tanggal Masuk <span style="color:#f4b8c8">*</span></label>
                    <input type="date" id="tanggal_masuk" name="tanggal_masuk"
                        class="form-control"
                        value="<?= htmlspecialchars($input['tanggal_masuk']) ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi <span style="color:var(--text-light);font-weight:400;">(opsional)</span></label>
                    <textarea id="deskripsi" name="deskripsi"
                        class="form-control"
                        rows="3"
                        placeholder="Keterangan singkat tentang barang ini..."><?= htmlspecialchars($input['deskripsi']) ?></textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">✅ Simpan Barang</button>
                <a href="index.php" class="btn btn-secondary">← Batal</a>
            </div>
        </form>
    </div>

</div>

<footer class="footer">
    <p>InvenTrack · Sistem Manajemen Inventaris · Dibangun dengan PHP & PDO</p>
</footer>

<script>
const hargaInput = document.getElementById('harga');
const preview   = document.getElementById('hargaPreview');
function updatePreview() {
    const val = parseFloat(hargaInput.value);
    if (val > 0) {
        preview.textContent = '≈ Rp ' + val.toLocaleString('id-ID');
    } else {
        preview.textContent = '';
    }
}
hargaInput.addEventListener('input', updatePreview);
updatePreview();
</script>

</body>
</html>