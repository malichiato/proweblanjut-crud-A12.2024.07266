<?php
// app/controllers/AuthController.php
// Controller: Menangani Login, Logout, Registrasi

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {

    private UserModel $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new UserModel();
    }

    // ── LOGIN: Tampilkan form login ──
    public function login(): void {
        if (isset($_SESSION['username'])) {
            redirect('index.php?action=index');
        }
        $error = '';
        require __DIR__ . '/../views/auth/login.php';
    }

    // ── PROSES LOGIN ──
    public function prosesLogin(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $error    = '';

        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi.';
        } else {
            $user = $this->model->findByUsername($username);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['login_at'] = date('Y-m-d H:i:s');
                redirect('index.php?action=index');
            } else {
                $error = 'Username atau password salah.';
            }
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    // ── LOGOUT ──
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']);
        }
        session_destroy();
        redirect('index.php?action=login');
    }

    // ── REGISTRASI: Tampilkan form ──
    public function registrasi(): void {
        if (isset($_SESSION['username'])) {
            redirect('index.php?action=index');
        }
        $errors  = [];
        $success = '';
        $input   = ['username' => ''];
        require __DIR__ . '/../views/auth/registrasi.php';
    }

    // ── PROSES REGISTRASI ──
    public function prosesRegistrasi(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $username   = trim($_POST['username']  ?? '');
        $password   = trim($_POST['password']  ?? '');
        $konfirmasi = trim($_POST['konfirmasi'] ?? '');
        $errors     = [];
        $success    = '';
        $input      = ['username' => $username];

        if (empty($username))
            $errors[] = 'Username wajib diisi.';
        elseif (strlen($username) < 3)
            $errors[] = 'Username minimal 3 karakter.';
        elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username))
            $errors[] = 'Username hanya boleh huruf, angka, dan underscore.';

        if (empty($password))
            $errors[] = 'Password wajib diisi.';
        elseif (strlen($password) < 6)
            $errors[] = 'Password minimal 6 karakter.';

        if ($password !== $konfirmasi)
            $errors[] = 'Konfirmasi password tidak cocok.';

        if (empty($errors)) {
            if ($this->model->isUsernameTaken($username)) {
                $errors[] = 'Username sudah digunakan.';
            } else {
                $this->model->save($username, password_hash($password, PASSWORD_DEFAULT));
                $success = 'Akun berhasil dibuat! Silakan login.';
                $input   = ['username' => ''];
            }
        }

        require __DIR__ . '/../views/auth/registrasi.php';
    }
}
