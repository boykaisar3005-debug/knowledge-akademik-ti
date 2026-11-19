<?php
/**
 * ============================================================
 * FILE     : config/database.php
 * FUNGSI   : Koneksi ke database MySQL menggunakan PDO
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * ============================================================
 * 
 * File ini di-include oleh semua halaman yang butuh akses database.
 * Cara pakai:
 *   require_once __DIR__ . '/../config/database.php';
 *   // variabel $pdo sudah tersedia untuk query
 */

// ============================================================
// Pengaturan koneksi database
// Sesuaikan jika konfigurasi XAMPP kamu berbeda
// ============================================================
$db_host = 'localhost';         // host database (biasanya localhost di XAMPP)
$db_name = 'db_akademik_chatbot'; // nama database
$db_user = 'root';              // username MySQL (default XAMPP: root)
$db_pass = '';                  // password MySQL (default XAMPP: kosong)
$db_port = 3306;                // port MySQL (default: 3306)

// ============================================================
// Membuat koneksi PDO
// ============================================================
try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // tampilkan error sebagai exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // hasil query jadi array asosiatif
        PDO::ATTR_EMULATE_PREPARES   => false,                    // gunakan prepared statement asli
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    
} catch (PDOException $e) {
    // Tampilkan pesan error yang ramah untuk pemula
    die(
        '<div style="font-family:Arial; padding:30px; max-width:600px; margin:50px auto; '
        . 'border:1px solid #e74c3c; border-radius:8px; background:#fef2f2;">'
        . '<h2 style="color:#e74c3c;">Koneksi Database Gagal</h2>'
        . '<p>Pastikan:</p>'
        . '<ol>'
        . '<li>XAMPP (Apache & MySQL) sudah dijalankan</li>'
        . '<li>Database <b>db_akademik_chatbot</b> sudah dibuat di phpMyAdmin</li>'
        . '<li>File <b>database.sql</b> sudah di-import</li>'
        . '</ol>'
        . '<p style="color:#888; font-size:12px;">Detail error: ' . htmlspecialchars($e->getMessage()) . '</p>'
        . '</div>'
    );
}
