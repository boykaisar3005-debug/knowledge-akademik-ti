<?php
/**
 * ============================================================
 * FILE     : config/helpers.php
 * FUNGSI   : Fungsi-fungsi bantu yang dipakai di banyak halaman
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * ============================================================
 */

/**
 * Membersihkan input dari karakter berbahaya (XSS prevention)
 * 
 * @param string $data  Input dari user
 * @return string       Input yang sudah dibersihkan
 */
function clean_input($data) {
    $data = trim($data);          // hapus spasi di awal/akhir
    $data = stripslashes($data);  // hapus backslash
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // konversi karakter khusus
    return $data;
}

/**
 * Menghasilkan URL dasar project secara otomatis
 * Jadi tidak perlu hardcode URL di setiap file
 * 
 * @return string  Base URL project (contoh: http://localhost/knowledge-akademik-ti)
 */
function base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Ambil path folder project dari SCRIPT_NAME
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    
    // Cari folder project "knowledge-akademik-ti" di path
    $pos = strpos($script_dir, 'knowledge-akademik-ti');
    if ($pos !== false) {
        $base = substr($script_dir, 0, $pos + strlen('knowledge-akademik-ti'));
    } else {
        $base = $script_dir;
    }
    
    return $protocol . '://' . $host . $base;
}

/**
 * Redirect ke halaman lain
 * 
 * @param string $path  Path tujuan (contoh: '/admin/login.php')
 */
function redirect($path) {
    header('Location: ' . base_url() . $path);
    exit;
}

/**
 * Menampilkan pesan flash (pemberitahuan sementara setelah aksi)
 * Simpan pesan ke session, tampilkan sekali lalu hapus
 * 
 * @param string $type     Tipe: 'success', 'error', 'warning', 'info'
 * @param string $message  Isi pesan
 */
function set_flash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Menampilkan pesan flash sebagai HTML lalu menghapusnya
 * 
 * @return string  HTML pesan flash, atau string kosong jika tidak ada
 */
function show_flash() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['flash'])) return '';
    
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    
    $colors = [
        'success' => '#27ae60',
        'error'   => '#e74c3c',
        'warning' => '#f39c12',
        'info'    => '#3498db',
    ];
    $color = $colors[$flash['type']] ?? '#3498db';
    
    return '<div style="padding:12px 20px; margin:10px 0; border-left:4px solid ' . $color 
         . '; background:#f8f9fa; border-radius:4px; font-family:Arial;">'
         . htmlspecialchars($flash['message']) . '</div>';
}
