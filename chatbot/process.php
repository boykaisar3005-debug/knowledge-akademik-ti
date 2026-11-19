<?php
/** Knowledge-first chatbot backend with optional Groq generation. */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/groq.php';

function chatbot_json($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function normalize_text($text) {
    $text = mb_strtolower((string)$text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
    return trim(preg_replace('/\s+/', ' ', $text));
}
function expand_query($text) {
    $text = normalize_text($text);
    $map = [
        'mi' => 'manajemen informatika', 'jti' => 'jurusan teknologi informasi',
        'gimana' => 'bagaimana', 'pkl' => 'praktik kerja lapangan',
        'ta' => 'tugas akhir', 'krs' => 'kartu rencana studi',
        'khs' => 'kartu hasil studi', 'ipk' => 'indeks prestasi kumulatif',
        'akreditasinya' => 'akreditasi', 'prodi' => 'program studi',
        'kuliahnya' => 'masa studi', 'gelarnya' => 'gelar', 'sks' => 'sks'
    ];
    foreach ($map as $from => $to) {
        $text = preg_replace('/\b' . preg_quote($from, '/') . '\b/u', $to, $text);
    }
    return $text;
}
function tokens($text) {
    $stop = ['yang','dan','di','ke','dari','ini','itu','pada','adalah','sebagai','dengan','untuk','atau','saja','juga','bisa','dapat','saya','aku','kami','kita','mau','tanya','bertanya','bagaimana','apa','apakah','kapan','dimana','siapa','mengapa','kenapa','tolong','mohon','info','informasi','soal','mengenai','tentang','dong','ya','ada'];
    $out = [];
    foreach (explode(' ', expand_query($text)) as $word) {
        if (mb_strlen($word, 'UTF-8') > 1 && !in_array($word, $stop, true)) $out[] = $word;
    }
    return array_values(array_unique($out));
}
function score_knowledge($query, $row) {
    $q = expand_query($query);
    $question = normalize_text($row['question']);
    $answer = normalize_text($row['answer']);
    $keywords = !empty($row['keyword_list']) ? explode('|||', $row['keyword_list']) : [];
    $score = 0.0;

    // Hindari match palsu dari kata terlalu umum seperti "jurusan" saja.
    $generic_terms = ['jurusan', 'prodi', 'akademik', 'kuliah', 'informasi', 'saat', 'ini'];
    $specific_query_tokens = array_values(array_diff(tokens($query), $generic_terms));
    $specific_match = false;
    foreach ($specific_query_tokens as $specific_word) {
        if (strpos($question, $specific_word) !== false || strpos($answer, $specific_word) !== false) {
            $specific_match = true;
            break;
        }
        foreach ($keywords as $keyword) {
            if (strpos(normalize_text($keyword), $specific_word) !== false) {
                $specific_match = true;
                break 2;
            }
        }
    }
    if (!$specific_match) return 0.0;

    if ($q === $question) $score += 100;
    elseif (strpos($question, $q) !== false || strpos($q, $question) !== false) $score += 70;
    foreach ($keywords as $kw) {
        $kw = normalize_text($kw);
        if ($kw === '') continue;
        if ($q === $kw) $score += 75;
        elseif (strpos($q, $kw) !== false) $score += 42;
        elseif (strpos($kw, $q) !== false) $score += 20;
    }
    $matched = 0;
    foreach (tokens($query) as $word) {
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/u', $question)) $matched += 3;
        foreach ($keywords as $kw) if (strpos(normalize_text($kw), $word) !== false) { $matched += 4; break; }
        if (strpos($answer, $word) !== false) $matched += 0.5;
    }
    $score += $matched * 8;
    return min(100.0, $score);
}
function create_or_find_session($pdo, $code) {
    if ($code !== '') {
        $s = $pdo->prepare('SELECT id FROM chat_sessions WHERE session_code = :code LIMIT 1');
        $s->execute(['code' => $code]);
        $found = $s->fetch();
        if ($found) return [(int)$found['id'], $code];
    }
    $new_code = 'cs_' . bin2hex(random_bytes(16));
    $s = $pdo->prepare('INSERT INTO chat_sessions (session_code, student_name, ip_address) VALUES (:code, :name, :ip)');
    $s->execute(['code' => $new_code, 'name' => 'Mahasiswa', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
    return [(int)$pdo->lastInsertId(), $new_code];
}
function save_unanswered($pdo, $chat_id, $message) {
    $s = $pdo->prepare('SELECT id, frequency FROM unanswered_questions WHERE is_resolved = 0 AND LOWER(question_text) = LOWER(:q) LIMIT 1');
    $s->execute(['q' => $message]);
    $old = $s->fetch();
    if ($old) {
        $u = $pdo->prepare('UPDATE unanswered_questions SET frequency = frequency + 1, chat_id = :chat, updated_at = NOW() WHERE id = :id');
        $u->execute(['chat' => $chat_id, 'id' => $old['id']]);
    } else {
        $u = $pdo->prepare('INSERT INTO unanswered_questions (chat_id, question_text, frequency) VALUES (:chat, :q, 1)');
        $u->execute(['chat' => $chat_id, 'q' => $message]);
    }
}

/**
 * Casual routing dibuat konservatif: pesan yang mengandung penanda
 * akademik selalu diproses sebagai akademik, meskipun pendek.
 */
function is_casual_message($message) {
    $normalized = normalize_text($message);
    $academic_markers = [
        'akreditasi', 'akademik', 'prodi', 'program studi', 'jurusan',
        'manajemen informatika', 'polinela', 'sks', 'semester', 'kuliah',
        'krs', 'khs', 'ipk', 'pkl', 'praktik kerja lapangan', 'tugas akhir',
        'skripsi', 'ujian', 'jadwal', 'dosen', 'mata kuliah', 'kontak',
        'siakad', 'vclass', 'biaya', 'ukt', 'beasiswa', 'ketua'
    ];
    foreach ($academic_markers as $marker) {
        if (strpos($normalized, $marker) !== false) return false;
    }

    $casual_patterns = [
        '/^(hai|halo|hello|hi|hei)([\s!.,]*)$/u',
        '/^(selamat pagi|selamat siang|selamat sore|selamat malam)([\s!.,]*)$/u',
        '/^(apa kabar|gimana kabarnya|kabarmu)([\s!?.,]*)$/u',
        '/^(makasih|terima kasih|thanks|thank you)( banyak)?([\s!.,]*)$/u',
        '/^(oke|ok|baik|siap|sip)([\s!.,]*)$/u',
        '/^(sampai jumpa|dadah|bye|selamat tinggal)([\s!.,]*)$/u'
    ];
    foreach ($casual_patterns as $pattern) {
        if (preg_match($pattern, $normalized)) return true;
    }
    return false;
}

function save_chat_history($pdo, $session_id, $message, $answer, $knowledge_id, $status, $confidence) {
    $save = $pdo->prepare('INSERT INTO chat_history (session_id, user_message, bot_answer, knowledge_id, status, confidence) VALUES (:sid,:msg,:ans,:kid,:status,:conf)');
    $save->execute([
        'sid' => $session_id,
        'msg' => $message,
        'ans' => $answer,
        'kid' => $knowledge_id,
        'status' => $status,
        'conf' => $confidence
    ]);
    return (int)$pdo->lastInsertId();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') chatbot_json(['success'=>false,'message'=>'Method request tidak diizinkan.'], 405);
$input = json_decode(file_get_contents('php://input'), true);
$message = trim((string)($input['message'] ?? $_POST['message'] ?? ''));
$session_code = trim((string)($input['session_code'] ?? $_POST['session_code'] ?? ''));
if ($message === '') chatbot_json(['success'=>false,'message'=>'Pesan tidak boleh kosong.'], 400);
if (mb_strlen($message, 'UTF-8') > 500) chatbot_json(['success'=>false,'message'=>'Pertanyaan maksimal 500 karakter.'], 400);
$message = strip_tags($message);

// Simple per-session/IP rate limit: 30 messages in 10 minutes.
if (session_status() === PHP_SESSION_NONE) session_start();
$rate_key = 'chat_rate_' . ($_SERVER['REMOTE_ADDR'] ?? 'local');
$now = time();
$_SESSION[$rate_key] = array_values(array_filter($_SESSION[$rate_key] ?? [], function($t) use ($now) { return $t > $now - 600; }));
if (count($_SESSION[$rate_key]) >= 30) chatbot_json(['success'=>false,'message'=>'Batas pertanyaan sementara tercapai. Silakan coba lagi beberapa menit lagi.'], 429);
$_SESSION[$rate_key][] = $now;

try {
    [$session_id, $session_code] = create_or_find_session($pdo, $session_code);

    // Ambil konteks percakapan singkat untuk casual maupun akademik.
    $recent_stmt = $pdo->prepare('SELECT user_message, bot_answer FROM chat_history WHERE session_id = :sid ORDER BY id DESC LIMIT 2');
    $recent_stmt->execute(['sid' => $session_id]);
    $recent_history = array_reverse($recent_stmt->fetchAll());

    // Casual tidak boleh masuk retrieval atau unanswered_questions.
    if (is_casual_message($message)) {
        $casual = call_groq_casual_api($message, $recent_history);
        if ($casual['success']) {
            $casual_answer = $casual['answer'];
        } else {
            // Fallback lokal jika Groq sedang tidak tersedia.
            $casual_answer = 'Halo! Saya siap membantu. Silakan tanyakan informasi akademik JTI Polinela jika diperlukan.';
        }
        $casual_chat_id = save_chat_history($pdo, $session_id, $message, $casual_answer, null, 'answered', 100.00);
        chatbot_json([
            'success' => true,
            'session_code' => $session_code,
            'chat_id' => $casual_chat_id,
            'user_message' => $message,
            'bot_answer' => $casual_answer,
            'status' => 'answered',
            'confidence' => 100,
            'knowledge_id' => null,
            'sources' => [],
            'created_at' => date('H:i')
        ]);
    }

$stmt = $pdo->query("
    SELECT
        k.id,
        k.question,
        k.answer,
        COALESCE(c.name, 'Informasi Akademik') AS category_name,
        GROUP_CONCAT(kw.keyword SEPARATOR '|||') AS keyword_list
    FROM knowledge k
    LEFT JOIN categories c
        ON c.id = k.category_id
    LEFT JOIN keywords kw
        ON kw.knowledge_id = k.id
    WHERE k.is_active = 1
    GROUP BY
        k.id,
        k.question,
        k.answer,
        c.name
");

$rows = $stmt->fetchAll();

$ranked = [];

foreach ($rows as $row) {
    $row['_score'] = score_knowledge($message, $row);

    /*
     * Knowledge yang memiliki kecocokan langsung dengan
     * pertanyaan/keyword tetap boleh masuk.
     */
    if ($row['_score'] >= 10) {
        $ranked[] = $row;
    }
}

usort($ranked, function ($a, $b) {
    return $b['_score'] <=> $a['_score'];
});

$ranked = array_slice($ranked, 0, 5);

    usort($ranked, function($a,$b) { return $b['_score'] <=> $a['_score']; });
    $ranked = array_slice($ranked, 0, 5);
    $best = $ranked[0] ?? null;
    $status = 'unanswered'; $answer = 'Maaf, informasi tersebut belum tersedia di Knowledge Base Akademik TI.'; $knowledge_id = null; $confidence = $best ? $best['_score'] : 0; $sources = [];

    if ($best) {
        foreach ($ranked as $i => $item) {
            $source_url = '';
            if (preg_match('~https?://[^\s]+~', $item['answer'], $url_match)) {
                $source_url = rtrim($url_match[0], '.,)');
            }
            $sources[] = ['category' => $item['category_name'], 'question' => $item['question'], 'source_url' => $source_url];
        }
        $context_parts = [];
        foreach ($ranked as $i => $item) {
            $context_parts[] = '[KNOWLEDGE ' . ($i+1) . "]\nKategori: " . $item['category_name'] . "\nPertanyaan: " . $item['question'] . "\nJawaban: " . $item['answer'];
        }
        $recent = [];
        $groq = call_groq_api($message, implode("\n\n", $context_parts), $recent_history);
        $answer = $groq['success'] ? $groq['answer'] : $best['answer'];
        $status = 'answered'; $knowledge_id = (int)$best['id'];
        $v = $pdo->prepare('UPDATE knowledge SET view_count = view_count + 1 WHERE id = :id'); $v->execute(['id' => $knowledge_id]);
    }
    $save = $pdo->prepare('INSERT INTO chat_history (session_id, user_message, bot_answer, knowledge_id, status, confidence) VALUES (:sid,:msg,:ans,:kid,:status,:conf)');
    $save->execute(['sid'=>$session_id,'msg'=>$message,'ans'=>$answer,'kid'=>$knowledge_id,'status'=>$status,'conf'=>round($confidence,2)]);
    $chat_id = (int)$pdo->lastInsertId();
    if ($status === 'unanswered') save_unanswered($pdo, $chat_id, $message);
    chatbot_json(['success'=>true,'session_code'=>$session_code,'chat_id'=>$chat_id,'user_message'=>$message,'bot_answer'=>$answer,'status'=>$status,'confidence'=>round($confidence,2),'knowledge_id'=>$knowledge_id,'sources'=>$sources,'created_at'=>date('H:i')]);
} catch (Throwable $e) {
    error_log('Chatbot processing error: ' . $e->getMessage());
    chatbot_json(['success'=>false,'message'=>'Maaf, layanan asisten sedang mengalami gangguan. Silakan coba lagi.'], 500);
}
