<?php
// registrasi.php - Halaman & Proses Registrasi User Baru

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kalau sudah login, langsung ke index
if (isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

require_once 'koneksi.php';

$errors  = [];
$success = '';
$input   = ['username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['username'] = trim($_POST['username'] ?? '');
    $password          = trim($_POST['password']  ?? '');
    $konfirmasi        = trim($_POST['konfirmasi'] ?? '');

    // Validasi
    if (empty($input['username']))
        $errors[] = 'Username wajib diisi.';
    elseif (strlen($input['username']) < 3)
        $errors[] = 'Username minimal 3 karakter.';
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $input['username']))
        $errors[] = 'Username hanya boleh huruf, angka, dan underscore.';

    if (empty($password))
        $errors[] = 'Password wajib diisi.';
    elseif (strlen($password) < 6)
        $errors[] = 'Password minimal 6 karakter.';

    if ($password !== $konfirmasi)
        $errors[] = 'Konfirmasi password tidak cocok.';

    if (empty($errors)) {
        $pdo = getConnection();

        // Cek username sudah ada
        $cek = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $cek->execute([':username' => $input['username']]);

        if ($cek->fetch()) {
            $errors[] = 'Username sudah digunakan. Pilih username lain.';
        } else {
            // Hash password & simpan
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->execute([
                ':username' => $input['username'],
                ':password' => $hashedPassword,
            ]);

            $success = 'Akun berhasil dibuat! Silakan login.';
            $input   = ['username' => ''];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvenTrack — Registrasi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }
        .login-card {
            background: var(--card-bg);
            border: 1.5px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: var(--shadow-lg);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--mint), var(--lavender));
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 30px;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px rgba(180,240,200,0.3);
        }
        .login-logo h1 {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 4px;
        }
        .login-logo p {
            font-size: 0.85rem;
            color: var(--text-light);
        }
        .strength-bar {
            height: 4px;
            border-radius: 4px;
            background: #eee;
            margin-top: 6px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s, background 0.3s;
            width: 0%;
        }
        .strength-text {
            font-size: 0.75rem;
            margin-top: 4px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <!-- LOGO -->
        <div class="login-logo">
            <div class="login-logo-icon">✨</div>
            <h1>Buat Akun Baru</h1>
            <p>InvenTrack — Sistem Manajemen Inventaris</p>
        </div>

        <!-- SUKSES -->
        <?php if ($success): ?>
        <div class="alert alert-success" style="margin-bottom:1.2rem;">
            ✅ <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <!-- ERROR -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error" style="margin-bottom:1.2rem;">
            ❌ <div>
                <?php foreach ($errors as $e): ?>
                <div><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- FORM REGISTRASI -->
        <form method="POST" action="registrasi.php" novalidate>
            <div class="form-grid">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                        class="form-control"
                        placeholder="Minimal 3 karakter"
                        value="<?= htmlspecialchars($input['username']) ?>"
                        autofocus required>
                    <div class="form-hint">Hanya huruf, angka, dan underscore (_)</div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                        class="form-control"
                        placeholder="Minimal 6 karakter"
                        required>
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <div class="strength-text" id="strengthText" style="color:var(--text-light);"></div>
                </div>

                <div class="form-group">
                    <label for="konfirmasi">Konfirmasi Password</label>
                    <input type="password" id="konfirmasi" name="konfirmasi"
                        class="form-control"
                        placeholder="Ulangi password"
                        required>
                    <div class="form-hint" id="matchHint"></div>
                </div>

            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1.5rem;">
                ✅ Daftarkan Akun
            </button>
        </form>

        <!-- LINK KE LOGIN -->
        <div style="text-align:center;margin-top:1.5rem;font-size:0.88rem;color:var(--text-light);">
            Sudah punya akun?
            <a href="login.php" style="color:#6B52A8;font-weight:700;text-decoration:none;">Masuk di sini</a>
        </div>

    </div>
</div>

<script>
// ── Password strength indicator ──
const passInput = document.getElementById('password');
const fill      = document.getElementById('strengthFill');
const txt       = document.getElementById('strengthText');

passInput.addEventListener('input', function() {
    const val = this.value;
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^a-zA-Z0-9]/.test(val)) score++;

    const levels = [
        { w: '0%',   color: '#eee',    label: '' },
        { w: '25%',  color: '#f4b8c8', label: '😟 Lemah' },
        { w: '50%',  color: '#f4d4b8', label: '😐 Sedang' },
        { w: '75%',  color: '#b8dff4', label: '🙂 Cukup Kuat' },
        { w: '100%', color: '#b8f4d8', label: '💪 Kuat' },
    ];

    const lvl = val.length === 0 ? levels[0] : levels[Math.min(score, 4)];
    fill.style.width      = lvl.w;
    fill.style.background = lvl.color;
    txt.textContent       = lvl.label;
    txt.style.color       = lvl.color === '#eee' ? 'var(--text-light)' : lvl.color;
});

// ── Konfirmasi password match ──
const konfInput = document.getElementById('konfirmasi');
const matchHint = document.getElementById('matchHint');

konfInput.addEventListener('input', function() {
    if (this.value === '') {
        matchHint.textContent = '';
        return;
    }
    if (this.value === passInput.value) {
        matchHint.textContent = '✅ Password cocok';
        matchHint.style.color = '#3aaa6b';
    } else {
        matchHint.textContent = '❌ Password tidak cocok';
        matchHint.style.color = '#c0436a';
    }
});
</script>

</body>
</html>