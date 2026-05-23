<?php
// public/index.php - Entry Point & Router Sederhana
// Semua request masuk melalui file ini

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/BarangController.php';

// Ambil action dari URL parameter
$action = $_GET['action'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];

// ── ROUTER ──
switch ($action) {

    // Auth routes
    case 'login':
        $controller = new AuthController();
        if ($method === 'POST') {
            $controller->prosesLogin();
        } else {
            $controller->login();
        }
        break;

    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;

    case 'registrasi':
        $controller = new AuthController();
        if ($method === 'POST') {
            $controller->prosesRegistrasi();
        } else {
            $controller->registrasi();
        }
        break;

    // Barang routes
    case 'index':
        $controller = new BarangController();
        $controller->index();
        break;

    case 'create':
        $controller = new BarangController();
        $controller->create();
        break;

    case 'store':
        $controller = new BarangController();
        $controller->store();
        break;

    case 'edit':
        $controller = new BarangController();
        $controller->edit();
        break;

    case 'update':
        $controller = new BarangController();
        $controller->update();
        break;

    case 'destroy':
        $controller = new BarangController();
        $controller->destroy();
        break;

    // Default: redirect ke login
    default:
        header('Location: index.php?action=login');
        exit;
}