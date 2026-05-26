<?php
// api/read.php - Endpoint untuk membaca semua data barang
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../app/models/BarangModel.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(451); // 405 Method Not Allowed
    echo json_encode([
        "status" => "error",
        "message" => "Metode HTTP tidak diizinkan. Gunakan GET."
    ]);
    exit;
}

try {
    $model = new BarangModel();
    $barangs = $model->getAll();

    // Map data ke tipe data yang sesuai jika perlu, atau langsung encoding
    $response = [];
    foreach ($barangs as $barang) {
        $response[] = [
            "id" => (int)$barang['id'],
            "nama_barang" => $barang['nama_barang'],
            "jumlah" => (int)$barang['jumlah'],
            "harga" => (float)$barang['harga'],
            "tanggal_masuk" => $barang['tanggal_masuk'],
            "deskripsi" => $barang['deskripsi'],
            "gambar" => $barang['gambar'] ? "uploads/" . $barang['gambar'] : null
        ];
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Berhasil mengambil data barang",
        "total" => count($response),
        "data" => $response
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Gagal mengambil data: " . $e->getMessage()
    ]);
}
