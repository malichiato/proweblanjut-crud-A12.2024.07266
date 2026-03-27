<?php
// login.php - Halaman & Proses Login

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kalau sudah login, langsung ke index
if (isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

require_once 'koneksi.php';

$error = '';
$pesan = '';

// Pesan dari redirect
if (isset($_GET['pesan'])) {
    $pesan = htmlspecialchars($_GET['pesan']);
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $pdo  = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil — simpan ke sesi
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_at'] = date('Y-m-d H:i:s');

            header('Location: index.php?pesan=' . urlencode('Selamat datang, ' . $user['username'] . '!') . '&tipe=success');
            exit;
        } else {
            $error = 'Username atau password salah. Silakan coba lagi.';
        }
    }
}
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
        .login-info {
            background: var(--lavender-soft);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            font-size: 0.82rem;
            color: var(--text-mid);
            margin-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <!-- LOGO -->
        <div class="login-logo">
            <div class="login-logo-icon">📦</div>
            <h1>InvenTrack</h1>
            <p>Sistem Manajemen Inventaris</p>
        </div>

        <!-- PESAN REDIRECT -->
        <?php if ($pesan): ?>
        <div class="alert alert-error" style="margin-bottom:1.2rem;">
            🔒 <?= $pesan ?>
        </div>
        <?php endif; ?>

        <!-- ERROR -->
        <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom:1.2rem;">
            ❌ <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- FORM LOGIN -->
        <form method="POST" action="login.php">
            <div class="form-grid">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                        class="form-control"
                        placeholder="Masukkan username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autofocus required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                        class="form-control"
                        placeholder="Masukkan password"
                        required>
                </div>

            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:1.5rem;">
                🔓 Masuk ke Sistem
            </button>
        </form>

    </div>
</div>

</body>
</html>