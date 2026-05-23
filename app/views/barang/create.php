<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvenTrack — Tambah Barang</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .upload-area { border:2px dashed var(--border); border-radius:var(--radius-md); padding:1.5rem; text-align:center; cursor:pointer; transition:all 0.2s; background:#fafaff; position:relative; }
        .upload-area:hover { border-color:var(--lavender); background:var(--lavender-soft); }
        .upload-area input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
        .preview-img { width:100%; max-height:200px; object-fit:contain; border-radius:var(--radius-sm); margin-top:10px; display:none; }
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
            <li><a href="index.php?action=index">🏠 Dashboard</a></li>
            <li><a href="index.php?action=create" class="active">➕ Tambah</a></li>
        </ul>
        <a href="index.php?action=logout" class="btn btn-danger" style="padding:6px 14px;font-size:0.85rem;">🚪 Logout</a>
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
            <a href="index.php?action=index" style="color:var(--text-light);text-decoration:none;">🏠 Dashboard</a>
            <span>›</span>
            <span style="color:var(--text-dark);font-weight:600;">Tambah Barang</span>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            ❌ <div><?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?></div>
        </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=store" enctype="multipart/form-data" novalidate>
            <div class="form-grid">

                <div class="form-group">
                    <label for="nama_barang">Nama Barang <span style="color:#f4b8c8">*</span></label>
                    <input type="text" id="nama_barang" name="nama_barang" class="form-control"
                        placeholder="cth. Laptop Asus VivoBook 15"
                        value="<?= htmlspecialchars($input['nama_barang']) ?>" maxlength="150" required>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label for="jumlah">Jumlah (unit) <span style="color:#f4b8c8">*</span></label>
                        <input type="number" id="jumlah" name="jumlah" class="form-control"
                            placeholder="0" min="0" value="<?= htmlspecialchars($input['jumlah']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga Satuan (Rp) <span style="color:#f4b8c8">*</span></label>
                        <input type="number" id="harga" name="harga" class="form-control"
                            placeholder="0" min="0" step="100" value="<?= htmlspecialchars($input['harga']) ?>" required>
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
                    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"
                        placeholder="Keterangan singkat tentang barang..."><?= htmlspecialchars($input['deskripsi']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Gambar Barang <span style="color:var(--text-light);font-weight:400;">(opsional, maks 2MB)</span></label>
                    <div class="upload-area" id="uploadArea">
                        <input type="file" name="gambar" id="gambar" accept="image/jpeg,image/png,image/webp" onchange="previewImage(this)">
                        <div style="font-size:2.5rem;margin-bottom:8px;">🖼️</div>
                        <div style="font-size:0.88rem;color:var(--text-mid);font-weight:500;">Klik atau drag gambar ke sini</div>
                        <div style="font-size:0.78rem;color:var(--text-light);margin-top:4px;">Format: JPG, PNG, WEBP • Maks 2MB</div>
                    </div>
                    <img id="previewImg" class="preview-img" src="" alt="Preview">
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">✅ Simpan Barang</button>
                <a href="index.php?action=index" class="btn btn-secondary">← Batal</a>
            </div>
        </form>
    </div>
</div>

<footer class="footer"><p>InvenTrack · MVC · Dibangun dengan PHP & PDO</p></footer>

<script>
function previewImage(input) {
    const preview = document.getElementById('previewImg');
    const area    = document.getElementById('uploadArea');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            area.style.borderColor = 'var(--mint)';
            area.style.background  = 'var(--mint-soft)';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
const hargaInput = document.getElementById('harga');
const preview    = document.getElementById('hargaPreview');
hargaInput.addEventListener('input', () => {
    const val = parseFloat(hargaInput.value);
    preview.textContent = val > 0 ? '≈ Rp ' + val.toLocaleString('id-ID') : '';
});
</script>
</body>
</html>
