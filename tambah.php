<?php
require_once 'auth.php';
require_once 'koneksi.php';

$errors = [];
$input  = [
    'nama_barang'   => '',
    'jumlah'        => '',
    'harga'         => '',
    'tanggal_masuk' => date('Y-m-d'),
    'deskripsi'     => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & sanitasi input
    $input['nama_barang']   = trim($_POST['nama_barang']   ?? '');
    $input['jumlah']        = trim($_POST['jumlah']        ?? '');
    $input['harga']         = trim($_POST['harga']         ?? '');
    $input['tanggal_masuk'] = trim($_POST['tanggal_masuk'] ?? '');
    $input['deskripsi']     = trim($_POST['deskripsi']     ?? '');

    // ── VALIDASI SERVER-SIDE ──
    if (empty($input['nama_barang']))
        $errors[] = 'Nama barang wajib diisi.';
    elseif (strlen($input['nama_barang']) > 150)
        $errors[] = 'Nama barang maksimal 150 karakter.';

    if (!is_numeric($input['jumlah']) || (int)$input['jumlah'] < 0)
        $errors[] = 'Jumlah harus berupa angka positif.';

    if (!is_numeric($input['harga']) || (float)$input['harga'] < 0)
        $errors[] = 'Harga harus berupa angka positif.';

    if (empty($input['tanggal_masuk']))
        $errors[] = 'Tanggal masuk wajib diisi.';

    // ── VALIDASI & PROSES UPLOAD GAMBAR ──
    $namaGambar = null;

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file       = $_FILES['gambar'];
        $maxSize    = 2 * 1024 * 1024; // 2MB
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMime= ['image/jpeg', 'image/png', 'image/webp'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Terjadi error saat mengunggah file.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Ukuran gambar maksimal 2MB.';
        } else {
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mime = mime_content_type($file['tmp_name']);

            if (!in_array($ext, $allowedExt) || !in_array($mime, $allowedMime)) {
                $errors[] = 'Format gambar harus JPG, PNG, atau WEBP.';
            } else {
                // Nama file unik
                $namaGambar = uniqid('barang_', true) . '.' . $ext;
                $uploadDir  = __DIR__ . '/uploads/';

                // Buat folder uploads jika belum ada
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (!move_uploaded_file($file['tmp_name'], $uploadDir . $namaGambar)) {
                    $errors[] = 'Gagal menyimpan gambar. Cek izin folder uploads/.';
                    $namaGambar = null;
                }
            }
        }
    }

    // ── SIMPAN KE DATABASE ──
    if (empty($errors)) {
        $pdo  = getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO barang (nama_barang, jumlah, harga, tanggal_masuk, deskripsi, gambar)
            VALUES (:nama_barang, :jumlah, :harga, :tanggal_masuk, :deskripsi, :gambar)
        ");
        $stmt->execute([
            ':nama_barang'   => htmlspecialchars($input['nama_barang'], ENT_QUOTES, 'UTF-8'),
            ':jumlah'        => (int)$input['jumlah'],
            ':harga'         => (float)$input['harga'],
            ':tanggal_masuk' => $input['tanggal_masuk'],
            ':deskripsi'     => $input['deskripsi'] ? htmlspecialchars($input['deskripsi'], ENT_QUOTES, 'UTF-8') : null,
            ':gambar'        => $namaGambar,
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
    <style>
        .upload-area {
            border: 2px dashed var(--border);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #fafaff;
            position: relative;
        }
        .upload-area:hover { border-color: var(--lavender); background: var(--lavender-soft); }
        .upload-area input[type="file"] {
            position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
        }
        .upload-icon { font-size: 2.5rem; margin-bottom: 8px; }
        .upload-text { font-size: 0.88rem; color: var(--text-mid); font-weight: 500; }
        .upload-hint { font-size: 0.78rem; color: var(--text-light); margin-top: 4px; }
        .preview-img { width: 100%; max-height: 200px; object-fit: contain; border-radius: var(--radius-sm); margin-top: 10px; display: none; }
    </style>
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
            <li><a href="tambah.php" class="active">➕ Tambah Barang</a></li>
        </ul>
        <a href="logout.php" class="btn btn-danger" style="padding:6px 14px;font-size:0.85rem;">🚪 Logout</a>
    </div>
</nav>

<div class="page-wrapper">
    <div class="page-header">
        <div>
            <h1 class="page-title">Tambah Barang Baru</h1>
            <p class="page-subtitle">Lengkapi form di bawah untuk menambah barang ke inventaris</p>
        </div>
    </div>

    <div class="form-card">
        <div style="font-size:0.82rem;color:var(--text-light);margin-bottom:1.5rem;display:flex;align-items:center;gap:6px;">
            <a href="index.php" style="color:var(--text-light);text-decoration:none;">🏠 Dashboard</a>
            <span>›</span>
            <span style="color:var(--text-dark);font-weight:600;">Tambah Barang</span>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            ❌ <div><?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?></div>
        </div>
        <?php endif; ?>

        <!-- enctype wajib untuk upload file -->
        <form method="POST" action="tambah.php" enctype="multipart/form-data" novalidate>
            <div class="form-grid">

                <div class="form-group">
                    <label for="nama_barang">Nama Barang <span style="color:#f4b8c8">*</span></label>
                    <input type="text" id="nama_barang" name="nama_barang"
                        class="form-control"
                        placeholder="cth. Laptop Asus VivoBook 15"
                        value="<?= htmlspecialchars($input['nama_barang']) ?>"
                        maxlength="150" required>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label for="jumlah">Jumlah (unit) <span style="color:#f4b8c8">*</span></label>
                        <input type="number" id="jumlah" name="jumlah"
                            class="form-control" placeholder="0" min="0"
                            value="<?= htmlspecialchars($input['jumlah']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga Satuan (Rp) <span style="color:#f4b8c8">*</span></label>
                        <input type="number" id="harga" name="harga"
                            class="form-control" placeholder="0" min="0" step="100"
                            value="<?= htmlspecialchars($input['harga']) ?>" required>
                        <div class="form-hint" id="hargaPreview" style="margin-top:6px;font-weight:600;color:var(--text-mid);"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tanggal_masuk">Tanggal Masuk <span style="color:#f4b8c8">*</span></label>
                    <input type="date" id="tanggal_masuk" name="tanggal_masuk"
                        class="form-control"
                        value="<?= htmlspecialchars($input['tanggal_masuk']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi <span style="color:var(--text-light);font-weight:400;">(opsional)</span></label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"
                        placeholder="Keterangan singkat tentang barang ini..."><?= htmlspecialchars($input['deskripsi']) ?></textarea>
                </div>

                <!-- UPLOAD GAMBAR -->
                <div class="form-group">
                    <label>Gambar Barang <span style="color:var(--text-light);font-weight:400;">(opsional, maks 2MB)</span></label>
                    <div class="upload-area" id="uploadArea">
                        <input type="file" name="gambar" id="gambar" accept="image/jpeg,image/png,image/webp"
                            onchange="previewImage(this)">
                        <div class="upload-icon">🖼️</div>
                        <div class="upload-text">Klik atau drag gambar ke sini</div>
                        <div class="upload-hint">Format: JPG, PNG, WEBP • Maks 2MB</div>
                    </div>
                    <img id="previewImg" class="preview-img" src="" alt="Preview">
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">✅ Simpan Barang</button>
                <a href="index.php" class="btn btn-secondary">← Batal</a>
            </div>
        </form>
    </div>
</div>

<footer class="footer"><p>InvenTrack · Sistem Manajemen Inventaris · Dibangun dengan PHP & PDO</p></footer>

<script>
// Preview gambar sebelum upload
function previewImage(input) {
    const preview = document.getElementById('previewImg');
    const area    = document.getElementById('uploadArea');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            area.style.borderColor = 'var(--mint)';
            area.style.background  = 'var(--mint-soft)';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
// Preview harga
const hargaInput = document.getElementById('harga');
const preview    = document.getElementById('hargaPreview');
function updatePreview() {
    const val = parseFloat(hargaInput.value);
    preview.textContent = val > 0 ? '≈ Rp ' + val.toLocaleString('id-ID') : '';
}
hargaInput.addEventListener('input', updatePreview);
updatePreview();
</script>
</body>
</html>