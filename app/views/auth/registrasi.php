<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvenTrack — Registrasi</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .login-wrapper { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; position:relative; z-index:1; }
        .login-card { background:var(--card-bg); border:1.5px solid var(--border); border-radius:var(--radius-lg); padding:3rem 2.5rem; width:100%; max-width:420px; box-shadow:var(--shadow-lg); }
        .login-logo { text-align:center; margin-bottom:2rem; }
        .login-logo-icon { width:64px; height:64px; background:linear-gradient(135deg,var(--mint),var(--lavender)); border-radius:20px; display:flex; align-items:center; justify-content:center; font-size:30px; margin:0 auto 1rem; }
        .login-logo h1 { font-size:1.6rem; font-weight:800; color:var(--text-dark); margin-bottom:4px; }
        .login-logo p  { font-size:0.85rem; color:var(--text-light); }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-logo">
            <div class="login-logo-icon">✨</div>
            <h1>Buat Akun Baru</h1>
            <p>InvenTrack — Sistem Manajemen Inventaris</p>
        </div>

        <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="margin-bottom:1.2rem;">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error" style="margin-bottom:1.2rem;">
            ❌ <div><?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?></div>
        </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=registrasi" novalidate>
            <div class="form-grid">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                        placeholder="Minimal 3 karakter"
                        value="<?= htmlspecialchars($input['username']) ?>" autofocus required>
                    <div class="form-hint">Hanya huruf, angka, dan underscore (_)</div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Minimal 6 karakter" required>
                </div>
                <div class="form-group">
                    <label for="konfirmasi">Konfirmasi Password</label>
                    <input type="password" id="konfirmasi" name="konfirmasi" class="form-control"
                        placeholder="Ulangi password" required>
                    <div class="form-hint" id="matchHint"></div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1.5rem;">
                ✅ Daftarkan Akun
            </button>
        </form>

        <div style="text-align:center;margin-top:1.5rem;font-size:0.88rem;color:var(--text-light);">
            Sudah punya akun?
            <a href="index.php?action=login" style="color:#6B52A8;font-weight:700;text-decoration:none;">Masuk di sini</a>
        </div>
    </div>
</div>
<script>
const passInput  = document.getElementById('password');
const konfInput  = document.getElementById('konfirmasi');
const matchHint  = document.getElementById('matchHint');
konfInput.addEventListener('input', function() {
    if (!this.value) { matchHint.textContent = ''; return; }
    if (this.value === passInput.value) { matchHint.textContent = '✅ Password cocok'; matchHint.style.color = '#3aaa6b'; }
    else { matchHint.textContent = '❌ Password tidak cocok'; matchHint.style.color = '#c0436a'; }
});
</script>
</body>
</html>
