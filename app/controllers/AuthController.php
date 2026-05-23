<?php
// app/controllers/AuthController.php
// Controller: Bertanggung jawab untuk proses Authentication (Login, Registrasi, Logout)
require_once __DIR__ . '/../models/UserModel.php';
class AuthController {
    private UserModel $model;
    public function __construct() {
        $this->model = new UserModel();
    }
    // ── LOGIN: Tampilkan form login ──
    public function login(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Jika sudah login, langsung ke dashboard
        if (isset($_SESSION['username'])) {
            redirect('index.php?action=index');
        }
        $error = '';
        $pesan = $_GET['pesan'] ?? '';
        $savedUsername = $_COOKIE['remember_user'] ?? '';
        require __DIR__ . '/../views/auth/login.php';
    }
    // ── PROSES LOGIN: Validasi & set session ──
    public function prosesLogin(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $username   = trim($_POST['username'] ?? '');
        $password   = trim($_POST['password'] ?? '');
        $rememberMe = isset($_POST['remember_me']);

        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi.';
            $savedUsername = $_COOKIE['remember_user'] ?? '';
            require __DIR__ . '/../views/auth/login.php';
            return;
        }
        $user = $this->model->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_at'] = date('Y-m-d H:i:s');

            // Fitur Remember Me (Hanya simpan username)
            if ($rememberMe) {
                $expiry = time() + (30 * 24 * 60 * 60); // 30 hari
                setcookie('remember_user', $user['username'], $expiry, '/', '', false, true);
            } else {
                setcookie('remember_user', '', time() - 3600, '/');
            }

            redirect('index.php?action=index');
        } else {
            $error = 'Username atau password salah.';
            if (isset($_COOKIE['remember_user'])) {
                setcookie('remember_user', '', time() - 3600, '/');
            }
            $savedUsername = '';
            require __DIR__ . '/../views/auth/login.php';
        }
    }
    // ── REGISTRASI: Tampilkan form registrasi ──
    public function registrasi(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Jika sudah login, langsung ke dashboard
        if (isset($_SESSION['username'])) {
            redirect('index.php?action=index');
        }
        $errors  = [];
        $success = '';
        $input   = ['username' => ''];
        require __DIR__ . '/../views/auth/registrasi.php';
    }
    // ── PROSES REGISTRASI: Validasi & simpan user baru ──
    public function prosesRegistrasi(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $errors  = [];
        $success = '';
        $input   = [
            'username' => trim($_POST['username'] ?? '')
        ];
        $password   = trim($_POST['password'] ?? '');
        $konfirmasi = trim($_POST['konfirmasi'] ?? '');
        // Validasi
        if (empty($input['username'])) {
            $errors[] = 'Username wajib diisi.';
        } elseif (strlen($input['username']) < 3) {
            $errors[] = 'Username minimal 3 karakter.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $input['username'])) {
            $errors[] = 'Username hanya boleh huruf, angka, dan underscore.';
        }
        if (empty($password)) {
            $errors[] = 'Password wajib diisi.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter.';
        }
        if ($password !== $konfirmasi) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        }
        if (empty($errors)) {
            if ($this->model->isUsernameTaken($input['username'])) {
                $errors[] = 'Username sudah digunakan. Pilih username lain.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $save = $this->model->save($input['username'], $hashedPassword);
                if ($save) {
                    $success = 'Akun berhasil dibuat! Silakan login.';
                    $input = ['username' => ''];
                } else {
                    $errors[] = 'Gagal menyimpan data user.';
                }
            }
        }
        if (!empty($errors)) {
            require __DIR__ . '/../views/auth/registrasi.php';
        } else {
            redirect('index.php?action=login&pesan=' . urlencode($success));
        }
    }
    // ── LOGOUT: Bersihkan session & cookie ──
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();
        if (isset($_COOKIE['remember_user'])) {
            setcookie('remember_user', '', time() - 3600, '/');
        }
        redirect('index.php?action=login');
    }
}