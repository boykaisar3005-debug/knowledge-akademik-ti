<?php
/**
 * ============================================================
 * FILE     : admin/logout.php
 * FUNGSI   : Proses Logout Admin
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * TAHAP    : 3 - Autentikasi Admin
 * ============================================================
 */

require_once __DIR__ . '/auth.php';

// Hapus variabel session admin
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);

// Set pesan flash
set_flash('info', 'Anda telah berhasil keluar (logout).');

// Redirect ke login
redirect('/admin/login.php');
