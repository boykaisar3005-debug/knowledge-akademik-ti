<?php
/**
 * ============================================================
 * FILE     : admin/knowledge_delete.php
 * FUNGSI   : Menghapus Data Knowledge & Keywords
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * TAHAP    : 4 - Knowledge Base Management
 * ============================================================
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/auth.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    set_flash('error', 'ID Knowledge tidak valid.');
    redirect('/admin/knowledge_index.php');
}

// Cek apakah data knowledge ada
$stmt_check = $pdo->prepare("SELECT id, question FROM knowledge WHERE id = :id LIMIT 1");
$stmt_check->execute(['id' => $id]);
$knowledge = $stmt_check->fetch();

if (!$knowledge) {
    set_flash('error', 'Data Knowledge Base tidak ditemukan.');
    redirect('/admin/knowledge_index.php');
}

try {
    // Hapus data (Tabel keywords berelasi ON DELETE CASCADE, sehingga otomatis terhapus)
    $stmt_del = $pdo->prepare("DELETE FROM knowledge WHERE id = :id");
    $stmt_del->execute(['id' => $id]);

    set_flash('success', 'Data Knowledge Base "' . htmlspecialchars($knowledge['question']) . '" berhasil dihapus.');
} catch (Exception $e) {
    set_flash('error', 'Gagal menghapus data: ' . $e->getMessage());
}

redirect('/admin/knowledge_index.php');
