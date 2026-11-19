<?php
/**
 * ============================================================
 * FILE     : chatbot/feedback.php
 * FUNGSI   : API Feedback Chatbot (Like/Dislike & Komentar)
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * TAHAP    : 2 - Chatbot Engine
 * ============================================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}

$json_data = json_decode(file_get_contents('php://input'), true);

$chat_id = 0;
$rating  = '';
$comment = null;

if (!empty($json_data['chat_id'])) {
    $chat_id = (int)$json_data['chat_id'];
    $rating  = clean_input($json_data['rating'] ?? '');
    $comment = !empty($json_data['comment']) ? clean_input($json_data['comment']) : null;
} elseif (isset($_POST['chat_id'])) {
    $chat_id = (int)$_POST['chat_id'];
    $rating  = clean_input($_POST['rating'] ?? '');
    $comment = !empty($_POST['comment']) ? clean_input($_POST['comment']) : null;
}

if ($chat_id <= 0 || !in_array($rating, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'message' => 'Data feedback tidak valid.']);
    exit;
}

// Cek apakah chat_id ada di chat_history
$stmt_check = $pdo->prepare("SELECT id FROM chat_history WHERE id = :id LIMIT 1");
$stmt_check->execute(['id' => $chat_id]);
if (!$stmt_check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Chat ID tidak ditemukan.']);
    exit;
}

// Cek apakah feedback untuk chat_id ini sudah ada
$stmt_fb = $pdo->prepare("SELECT id FROM feedback WHERE chat_id = :chat_id LIMIT 1");
$stmt_fb->execute(['chat_id' => $chat_id]);
$existing_fb = $stmt_fb->fetch();

if ($existing_fb) {
    // Update feedback yang sudah ada
    $stmt_upd = $pdo->prepare("UPDATE feedback SET rating = :rating, comment = :comment WHERE id = :id");
    $stmt_upd->execute([
        'rating'  => $rating,
        'comment' => $comment,
        'id'      => $existing_fb['id']
    ]);
} else {
    // Insert feedback baru
    $stmt_ins = $pdo->prepare("INSERT INTO feedback (chat_id, rating, comment) VALUES (:chat_id, :rating, :comment)");
    $stmt_ins->execute([
        'chat_id' => $chat_id,
        'rating'  => $rating,
        'comment' => $comment
    ]);
}

echo json_encode([
    'success' => true,
    'message' => 'Terima kasih atas feedback yang kamu berikan!'
]);
