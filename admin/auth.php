<?php
/**
 * ============================================================
 * FILE     : admin/auth.php
 * FUNGSI   : Helper Autentikasi & Proteksi Halaman Admin
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * TAHAP    : 3 - Autentikasi Admin
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/helpers.php';

/**
 * Cek apakah user admin sudah login
 * 
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Proteksi halaman khusus admin.
 * Jika belum login, redirect ke halaman login admin.
 */
function require_admin() {
    if (!is_logged_in()) {
        set_flash('error', 'Akses ditolak. Silakan login sebagai admin terlebih dahulu.');
        redirect('/admin/login.php');
    }
}

/**
 * Mengambil data info admin yang sedang login
 * 
 * @return array
 */
function get_admin_user() {
    if (!is_logged_in()) return null;
    return [
        'id'        => $_SESSION['admin_id'] ?? 0,
        'username'  => $_SESSION['admin_username'] ?? '',
        'full_name' => $_SESSION['admin_name'] ?? 'Admin',
        'role'      => $_SESSION['admin_role'] ?? 'admin'
    ];
}
