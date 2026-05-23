<?php
// app/models/UserModel.php
// Model: Bertanggung jawab untuk interaksi database tabel users

require_once __DIR__ . '/../../config/database.php';

class UserModel {

    private PDO $db;

    public function __construct() {
        $this->db = getConnection();
    }

    // Cari user berdasarkan username
    public function findByUsername(string $username): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    // Simpan user baru
    public function save(string $username, string $hashedPassword): bool {
        $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        return $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
        ]);
    }

    // Cek apakah username sudah ada
    public function isUsernameTaken(string $username): bool {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return (bool)$stmt->fetch();
    }
}