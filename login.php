<?php
// login.php - Halaman & Proses Login + Remember Me

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kalau sudah login, langsung ke index
if (isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

require_once 'koneksi.php';

$error  = '';
$pesan  = '';

// Cek cookie Remember Me - auto login
if (!isset($_SESSION['username']) && isset($_COOKIE['remember_user'])) {
    $pdo  = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $_COOKIE['remember_user']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['login_at'] = date('Y-m-d H:i:s');
        header('Location: index.php?pesan=' . urlencode('Selamat datang kembali, ' . $user['username'] . '!') . '&tipe=success');
        exit;
    }
}

// Pesan dari redirect
if (isset($_GET['pesan'])) {
    $pesan = htmlspecialchars($_GET['pesan']);
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']    ?? '');
    $password   = trim($_POST['password']    ?? '');
    $rememberMe = isset($_POST['remember_me']);

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $pdo  = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Simpan ke sesi
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_at'] = date('Y-m-d H:i:s');

            // Kalau centang Remember Me - simpan cookie 30 hari
            if ($rememberMe) {
                $expiry = time() + (30 * 24 * 60 * 60);
                setcookie('remember_user', $user['username'], $expiry, '/', '', false, true);
            }

            header('Location: index.php?pesan=' . urlencode('Selamat datang, ' . $user['username'] . '!') . '&tipe=success');
            exit;
        } else {
            $error = 'Username atau password salah. Silakan coba lagi.';
            if (isset($_COOKIE['remember_user'])) {
                setcookie('remember_user', '', time() - 3600, '/');
            }
        }
    }
}

$savedUsername = $_COOKIE['remember_user'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvenTrack — Login</title>
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
            background: linear-gradient(135deg, var(--lavender), var(--pink));
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 30px;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px rgba(180,150,240,0.3);
        }
        .login-logo h1 { font-size: 1.6rem; font-weight: 800; color: var(--text-dark); margin-bottom: 4px; }
        .login-logo p  { font-size: 0.85rem; color: var(--text-light); }
        .remember-row  { display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem; }
        .remember-label {
            display: flex; align-items: center; gap: 8px;
            cursor: pointer; font-size: 0.88rem; color: var(--text-mid);
            font-weight: 500; user-select: none;
        }
        .remember-label input[type="checkbox"] { width: 16px; height: 16px; accent-color: #6B52A8; cursor: pointer; }
        .cookie-info {
            background: var(--mint-soft); border-radius: var(--radius-sm);
            padding: 8px 14px; font-size: 0.78rem; color: #3aaa6b;
            margin-top: 8px; display: none;
        }
        .cookie-info.show { display: block; }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-logo">
            <div class="login-logo-icon">📦</div>
            <h1>InvenTrack</h1>
            <p>Sistem Manajemen Inventaris</p>
        </div>

        <?php if ($pesan): ?>
        <div class="alert alert-error" style="margin-bottom:1.2rem;">🔒 <?= $pesan ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom:1.2rem;">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($savedUsername): ?>
        <div class="alert alert-success" style="margin-bottom:1.2rem;">
            🍪 Sesi tersimpan untuk: <strong><?= htmlspecialchars($savedUsername) ?></strong>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-grid">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                        class="form-control"
                        placeholder="Masukkan username"
                        value="<?= htmlspecialchars($_POST['username'] ?? $savedUsername) ?>"
                        autofocus required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div style="position:relative;">
                        <input type="password" id="password" name="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            style="padding-right:44px;"
                            required>
                        <button type="button" onclick="togglePassword()"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;"
                            id="toggleBtn">👁️</button>
                    </div>
                </div>

                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember_me" id="remember_me"
                            <?= $savedUsername ? 'checked' : '' ?>>
                        Ingat saya selama 30 hari
                    </label>
                </div>
                <div class="cookie-info" id="cookieInfo">
                    🍪 Browser akan menyimpan cookie login selama 30 hari
                </div>

            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1.5rem;">
                🔓 Masuk ke Sistem
            </button>
        </form>

        <div style="text-align:center;margin-top:1.5rem;font-size:0.88rem;color:var(--text-light);">
            Belum punya akun?
            <a href="registrasi.php" style="color:#6B52A8;font-weight:700;text-decoration:none;">Daftar di sini</a>
        </div>

    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const btn   = document.getElementById('toggleBtn');
    if (input.type === 'password') { input.type = 'text'; btn.textContent = '🙈'; }
    else { input.type = 'password'; btn.textContent = '👁️'; }
}
const checkbox   = document.getElementById('remember_me');
const cookieInfo = document.getElementById('cookieInfo');
checkbox.addEventListener('change', function() {
    cookieInfo.classList.toggle('show', this.checked);
});
if (checkbox.checked) cookieInfo.classList.add('show');
</script>

</body>
</html>