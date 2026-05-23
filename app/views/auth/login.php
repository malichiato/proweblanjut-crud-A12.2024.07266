<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvenTrack — Login</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .login-wrapper { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; position:relative; z-index:1; }
        .login-card { background:var(--card-bg); border:1.5px solid var(--border); border-radius:var(--radius-lg); padding:3rem 2.5rem; width:100%; max-width:420px; box-shadow:var(--shadow-lg); }
        .login-logo { text-align:center; margin-bottom:2rem; }
        .login-logo-icon { width:64px; height:64px; background:linear-gradient(135deg,var(--lavender),var(--pink)); border-radius:20px; display:flex; align-items:center; justify-content:center; font-size:30px; margin:0 auto 1rem; box-shadow:0 8px 24px rgba(180,150,240,0.3); }
        .login-logo h1 { font-size:1.6rem; font-weight:800; color:var(--text-dark); margin-bottom:4px; }
        .login-logo p  { font-size:0.85rem; color:var(--text-light); }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-logo">
            <div class="login-logo-icon">📦</div>
            <h1>InvenTrack</h1>
            <p>Sistem Manajemen Inventaris — MVC</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert alert-error" style="margin-bottom:1.2rem;">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=login">
            <div class="form-grid">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                        placeholder="Masukkan username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autofocus required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div style="position:relative;">
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="Masukkan password" style="padding-right:44px;" required>
                        <button type="button" onclick="togglePassword()"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;"
                            id="toggleBtn">👁️</button>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1.5rem;">
                🔓 Masuk ke Sistem
            </button>
        </form>

        <div style="text-align:center;margin-top:1.5rem;font-size:0.88rem;color:var(--text-light);">
            Belum punya akun?
            <a href="index.php?action=registrasi" style="color:#6B52A8;font-weight:700;text-decoration:none;">Daftar di sini</a>
        </div>
    </div>
</div>
<script>
function togglePassword() {
    const input = document.getElementById('password');
    const btn   = document.getElementById('toggleBtn');
    input.type  = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? '👁️' : '🙈';
}
</script>
</body>
</html>
