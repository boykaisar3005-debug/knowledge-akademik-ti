<?php
/**
 * ============================================================
 * FILE     : chatbot/get_history.php
 * FUNGSI   : Memuat riwayat pesan dalam sesi chat
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * TAHAP    : 2 - Chatbot Engine
 * ============================================================
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$session_code = isset($_GET['session_code']) ? clean_input($_GET['session_code']) : '';

if (empty($session_code)) {
    echo json_encode(['success' => false, 'history' => []]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT h.id as chat_id, h.user_message, h.bot_answer, h.status, h.confidence, h.created_at,
           f.rating as feedback_rating
    FROM chat_history h
    JOIN chat_sessions s ON h.session_id = s.id
    LEFT JOIN feedback f ON h.id = f.chat_id
    WHERE s.session_code = :code
    ORDER BY h.id ASC
");
$stmt->execute(['code' => $session_code]);
$history = $stmt->fetchAll();

$formatted_history = [];
foreach ($history as $item) {
    $history_sources = [];
    if (preg_match_all('~https?://[^\s]+~', (string)$item['bot_answer'], $url_matches)) {
        foreach ($url_matches[0] as $url) $history_sources[] = ['category' => '', 'question' => 'Sumber jawaban', 'source_url' => rtrim($url, '.,)')];
    }
    $formatted_history[] = [
        'chat_id'         => (int)$item['chat_id'],
        'user_message'    => $item['user_message'],
        'bot_answer'      => $item['bot_answer'],
        'sources'         => $history_sources,
        'status'          => $item['status'],
        'confidence'      => (float)$item['confidence'],
        'feedback_rating' => $item['feedback_rating'],
        'time'            => date('H:i', strtotime($item['created_at']))
    ];
}

echo json_encode([
    'success' => true,
    'history' => $formatted_history
]);
