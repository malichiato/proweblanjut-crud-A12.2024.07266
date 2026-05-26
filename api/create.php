<?php
// api/create.php - Endpoint untuk menambah data barang baru
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../app/models/BarangModel.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        "status" => "error",
        "message" => "Metode HTTP tidak diizinkan. Gunakan POST."
    ]);
    exit;
}

// Membaca data baik dari raw JSON (php://input) maupun form-urlencoded ($_POST)
$inputData = json_decode(file_get_contents("php://input"), true);
if (!$inputData) {
    $inputData = $_POST;
}

$nama_barang   = trim($inputData['nama_barang']   ?? '');
$jumlah        = isset($inputData['jumlah'])        ? trim($inputData['jumlah'])        : '';
$harga         = isset($inputData['harga'])         ? trim($inputData['harga'])         : '';
$tanggal_masuk = trim($inputData['tanggal_masuk'] ?? date('Y-m-d'));
$deskripsi     = trim($inputData['deskripsi']     ?? '');

$errors = [];

// Validasi
if (empty($nama_barang)) {
    $errors[] = "Nama barang wajib diisi.";
} elseif (strlen($nama_barang) > 150) {
    $errors[] = "Nama barang maksimal 150 karakter.";
}

if ($jumlah === '') {
    $errors[] = "Jumlah wajib diisi.";
} elseif (!is_numeric($jumlah) || (int)$jumlah < 0) {
    $errors[] = "Jumlah harus berupa angka positif.";
}

if ($harga === '') {
    $errors[] = "Harga wajib diisi.";
} elseif (!is_numeric($harga) || (float)$harga < 0) {
    $errors[] = "Harga harus berupa angka positif.";
}

if (!empty($errors)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "status" => "error",
        "message" => "Validasi gagal",
        "errors" => $errors
    ]);
    exit;
}

try {
    $model = new BarangModel();
    
    $saveData = [
        "nama_barang"   => htmlspecialchars($nama_barang, ENT_QUOTES, 'UTF-8'),
        "jumlah"        => (int)$jumlah,
        "harga"         => (float)$harga,
        "tanggal_masuk" => $tanggal_masuk,
        "deskripsi"     => !empty($deskripsi) ? htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8') : null,
        "gambar"        => null // Upload gambar opsional lewat API diatur null secara default
    ];

    $result = $model->save($saveData);

    if ($result) {
        http_response_code(201); // Created
        echo json_encode([
            "status" => "success",
            "message" => "Barang berhasil ditambahkan",
            "data" => [
                "nama_barang" => $nama_barang,
                "jumlah" => (int)$jumlah,
                "harga" => (float)$harga,
                "tanggal_masuk" => $tanggal_masuk,
                "deskripsi" => $deskripsi
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Gagal menyimpan data barang ke database"
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Terjadi kesalahan sistem: " . $e->getMessage()
    ]);
}
