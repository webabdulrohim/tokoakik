<?php
// config/config.php
$host = 'localhost';
$db   = 'core_stone_db';
$user = 'root';
$pass = ''; // Kosongkan jika pakai XAMPP default

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

// Mulai session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>