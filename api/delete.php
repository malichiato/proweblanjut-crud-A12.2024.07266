<?php
// api/delete.php - Endpoint untuk menghapus data barang
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../app/models/BarangModel.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST' && $method !== 'DELETE') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        "status" => "error",
        "message" => "Metode HTTP tidak diizinkan. Gunakan POST atau DELETE."
    ]);
    exit;
}

// Membaca data baik dari GET, POST, maupun raw JSON
$id = $_GET['id'] ?? $_POST['id'] ?? null;

if ($id === null) {
    $inputData = json_decode(file_get_contents("php://input"), true);
    $id = $inputData['id'] ?? null;
}

$id = (int)$id;

if ($id <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "status" => "error",
        "message" => "ID barang wajib disertakan dan harus berupa angka positif."
    ]);
    exit;
}

try {
    $model = new BarangModel();
    
    // Cek apakah barang dengan ID tersebut ada
    $barang = $model->getById($id);
    if (!$barang) {
        http_response_code(404); // Not Found
        echo json_encode([
            "status" => "error",
            "message" => "Barang dengan ID $id tidak ditemukan."
        ]);
        exit;
    }

    // Hapus file gambar fisik jika ada
    if (!empty($barang['gambar'])) {
        $uploadDir = __DIR__ . '/../uploads/';
        $filePath  = $uploadDir . $barang['gambar'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Lakukan penghapusan di database
    $result = $model->delete($id);

    if ($result) {
        http_response_code(200); // OK
        echo json_encode([
            "status" => "success",
            "message" => "Barang dengan ID $id berhasil dihapus."
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Gagal menghapus data barang dari database"
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Terjadi kesalahan sistem: " . $e->getMessage()
    ]);
}
