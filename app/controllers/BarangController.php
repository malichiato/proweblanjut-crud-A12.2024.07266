<?php
// app/controllers/BarangController.php
// Controller: Perantara antara Model dan View
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/BarangModel.php';
class BarangController {
    private BarangModel $model;
    public function __construct() {
        require_once __DIR__ . '/../../config/auth.php';
        $this->model = new BarangModel();
    }
    // ── INDEX: Tampilkan semua barang ──
    public function index(): void {
        $barangs = $this->model->getAll();
        $stats   = $this->model->getStats();
        $pesan   = $_GET['pesan'] ?? '';
        $tipe    = $_GET['tipe']  ?? 'success';
        require __DIR__ . '/../views/barang/index.php';
    }
    // ── CREATE: Tampilkan form tambah ──
    public function create(): void {
        $errors = [];
        $input  = [
            'nama_barang'   => '',
            'jumlah'        => '',
            'harga'         => '',
            'tanggal_masuk' => date('Y-m-d'),
            'deskripsi'     => '',
        ];
        require __DIR__ . '/../views/barang/create.php';
    }
    // ── STORE: Proses simpan barang baru ──
    public function store(): void {
        $errors = [];
        $input  = [
            'nama_barang'   => trim($_POST['nama_barang']   ?? ''),
            'jumlah'        => trim($_POST['jumlah']        ?? ''),
            'harga'         => trim($_POST['harga']         ?? ''),
            'tanggal_masuk' => trim($_POST['tanggal_masuk'] ?? ''),
            'deskripsi'     => trim($_POST['deskripsi']     ?? ''),
        ];
        // Validasi server-side
        if (empty($input['nama_barang']))
            $errors[] = 'Nama barang wajib diisi.';
        elseif (strlen($input['nama_barang']) > 150)
            $errors[] = 'Nama barang maksimal 150 karakter.';
        if (!is_numeric($input['jumlah']) || (int)$input['jumlah'] < 0)
            $errors[] = 'Jumlah harus berupa angka positif.';
        if (!is_numeric($input['harga']) || (float)$input['harga'] < 0)
            $errors[] = 'Harga harus berupa angka positif.';
        if (empty($input['tanggal_masuk']))
            $errors[] = 'Tanggal masuk wajib diisi.';
        // Proses upload gambar
        $namaGambar = $this->uploadGambar();
        if (is_string($namaGambar) && str_starts_with($namaGambar, 'ERROR:')) {
            $errors[] = substr($namaGambar, 6);
            $namaGambar = null;
        }
        if (!empty($errors)) {
            require __DIR__ . '/../views/barang/create.php';
            return;
        }
        // Simpan ke database via Model
        $this->model->save([
            'nama_barang'   => htmlspecialchars($input['nama_barang'],   ENT_QUOTES, 'UTF-8'),
            'jumlah'        => (int)$input['jumlah'],
            'harga'         => (float)$input['harga'],
            'tanggal_masuk' => $input['tanggal_masuk'],
            'deskripsi'     => $input['deskripsi'] ? htmlspecialchars($input['deskripsi'], ENT_QUOTES, 'UTF-8') : null,
            'gambar'        => $namaGambar,
        ]);
        redirect('index.php?action=index&pesan=' . urlencode('Barang "' . $input['nama_barang'] . '" berhasil ditambahkan!') . '&tipe=success');
    }
    // ── EDIT: Tampilkan form edit ──
    public function edit(): void {
        $id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $barang = $this->model->getById($id);
        if (!$barang) {
            redirect('index.php?action=index&pesan=' . urlencode('Barang tidak ditemukan.') . '&tipe=error');
        }
        $errors = [];
        $input  = $barang;
        require __DIR__ . '/../views/barang/edit.php';
    }
    // ── UPDATE: Proses update barang ──
    public function update(): void {
        $id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $barang = $this->model->getById($id);
        if (!$barang) {
            redirect('index.php?action=index&pesan=' . urlencode('Barang tidak ditemukan.') . '&tipe=error');
        }
        $errors = [];
        $input  = [
            'nama_barang'   => trim($_POST['nama_barang']   ?? ''),
            'jumlah'        => trim($_POST['jumlah']        ?? ''),
            'harga'         => trim($_POST['harga']         ?? ''),
            'tanggal_masuk' => trim($_POST['tanggal_masuk'] ?? ''),
            'deskripsi'     => trim($_POST['deskripsi']     ?? ''),
        ];
        // Validasi
        if (empty($input['nama_barang']))
            $errors[] = 'Nama barang wajib diisi.';
        elseif (strlen($input['nama_barang']) > 150)
            $errors[] = 'Nama barang maksimal 150 karakter.';
        if (!is_numeric($input['jumlah']) || (int)$input['jumlah'] < 0)
            $errors[] = 'Jumlah harus berupa angka positif.';
        if (!is_numeric($input['harga']) || (float)$input['harga'] < 0)
            $errors[] = 'Harga harus berupa angka positif.';
        if (empty($input['tanggal_masuk']))
            $errors[] = 'Tanggal masuk wajib diisi.';
        // Proses upload gambar baru
        $namaGambarBaru = $barang['gambar'];
        $gambarBaru = $this->uploadGambar();
        if (is_string($gambarBaru) && str_starts_with($gambarBaru, 'ERROR:')) {
            $errors[] = substr($gambarBaru, 6);
        } elseif ($gambarBaru !== null) {
            // Hapus gambar lama
            $uploadDir = __DIR__ . '/../../uploads/';
            if ($barang['gambar'] && file_exists($uploadDir . $barang['gambar'])) {
                unlink($uploadDir . $barang['gambar']);
            }
            $namaGambarBaru = $gambarBaru;
        }
        // Hapus gambar jika dicentang
        if (isset($_POST['hapus_gambar']) && $barang['gambar']) {
            $uploadDir = __DIR__ . '/../../uploads/';
            if (file_exists($uploadDir . $barang['gambar'])) {
                unlink($uploadDir . $barang['gambar']);
            }
            $namaGambarBaru = null;
        }
        if (!empty($errors)) {
            $input = array_merge($barang, $input);
            require __DIR__ . '/../views/barang/edit.php';
            return;
        }
        // Update via Model
        $this->model->update($id, [
            'nama_barang'   => htmlspecialchars($input['nama_barang'],   ENT_QUOTES, 'UTF-8'),
            'jumlah'        => (int)$input['jumlah'],
            'harga'         => (float)$input['harga'],
            'tanggal_masuk' => $input['tanggal_masuk'],
            'deskripsi'     => $input['deskripsi'] ? htmlspecialchars($input['deskripsi'], ENT_QUOTES, 'UTF-8') : null,
            'gambar'        => $namaGambarBaru,
        ]);
        redirect('index.php?action=index&pesan=' . urlencode('Data "' . $input['nama_barang'] . '" berhasil diperbarui!') . '&tipe=success');
    }
    // ── DESTROY: Hapus barang ──
    public function destroy(): void {
        $id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $barang = $this->model->getById($id);
        if (!$barang) {
            redirect('index.php?action=index&pesan=' . urlencode('Barang tidak ditemukan.') . '&tipe=error');
        }
        // Hapus file gambar jika ada
        if ($barang['gambar']) {
            $filePath = __DIR__ . '/../../uploads/' . $barang['gambar'];
            if (file_exists($filePath)) unlink($filePath);
        }
        $this->model->delete($id);
        redirect('index.php?action=index&pesan=' . urlencode('Barang "' . $barang['nama_barang'] . '" berhasil dihapus.') . '&tipe=success');
    }
    // ── HELPER: Proses upload gambar ──
    private function uploadGambar(): string|null {
        if (!isset($_FILES['gambar']) || $_FILES['gambar']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $file        = $_FILES['gambar'];
        $maxSize     = 2 * 1024 * 1024;
        $allowedExt  = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
        if ($file['error'] !== UPLOAD_ERR_OK)
            return 'ERROR:Terjadi error saat mengunggah file.';
        if ($file['size'] > $maxSize)
            return 'ERROR:Ukuran gambar maksimal 2MB.';
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($ext, $allowedExt) || !in_array($mime, $allowedMime))
            return 'ERROR:Format gambar harus JPG, PNG, atau WEBP.';
        $namaGambar = uniqid('barang_', true) . '.' . $ext;
        $uploadDir  = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $namaGambar))
            return 'ERROR:Gagal menyimpan gambar.';
        return $namaGambar;
    }
}