<?php
// app/models/BarangModel.php
// Model: Bertanggung jawab untuk semua interaksi dengan database

require_once __DIR__ . '/../../config/database.php';

class BarangModel {

    private PDO $db;

    public function __construct() {
        $this->db = getConnection();
    }

    // ── READ: Ambil semua data barang ──
    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM barang ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    // ── READ: Ambil satu barang berdasarkan ID ──
    public function getById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM barang WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ── READ: Ambil statistik inventaris ──
    public function getStats(): array {
        $stmt = $this->db->query("
            SELECT
                COUNT(*)                                          AS total_barang,
                SUM(jumlah)                                       AS total_stok,
                SUM(jumlah * harga)                               AS total_nilai,
                SUM(CASE WHEN jumlah <= 5 THEN 1 ELSE 0 END)     AS stok_rendah
            FROM barang
        ");
        return $stmt->fetch();
    }

    // ── CREATE: Simpan data barang baru ──
    public function save(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO barang (nama_barang, jumlah, harga, tanggal_masuk, deskripsi, gambar)
            VALUES (:nama_barang, :jumlah, :harga, :tanggal_masuk, :deskripsi, :gambar)
        ");
        return $stmt->execute([
            ':nama_barang'   => $data['nama_barang'],
            ':jumlah'        => $data['jumlah'],
            ':harga'         => $data['harga'],
            ':tanggal_masuk' => $data['tanggal_masuk'],
            ':deskripsi'     => $data['deskripsi'] ?? null,
            ':gambar'        => $data['gambar']    ?? null,
        ]);
    }

    // ── UPDATE: Perbarui data barang ──
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE barang
            SET nama_barang   = :nama_barang,
                jumlah        = :jumlah,
                harga         = :harga,
                tanggal_masuk = :tanggal_masuk,
                deskripsi     = :deskripsi,
                gambar        = :gambar
            WHERE id = :id
        ");
        return $stmt->execute([
            ':nama_barang'   => $data['nama_barang'],
            ':jumlah'        => $data['jumlah'],
            ':harga'         => $data['harga'],
            ':tanggal_masuk' => $data['tanggal_masuk'],
            ':deskripsi'     => $data['deskripsi'] ?? null,
            ':gambar'        => $data['gambar']    ?? null,
            ':id'            => $id,
        ]);
    }

    // ── DELETE: Hapus data barang ──
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM barang WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
