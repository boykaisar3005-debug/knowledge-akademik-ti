<?php
/**
 * ============================================================
 * FILE     : config/groq.php
 * FUNGSI   : Konfigurasi Groq AI API (Server-Side Only)
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * ============================================================
 *
 * KEAMANAN: File ini HANYA boleh di-include oleh PHP backend.
 * JANGAN pernah menampilkan isi file ini ke browser/client.
 *
 * CARA MEMASUKKAN API KEY:
 * ============================================================
 *
 * OPSI 1 (Direkomendasikan) - Environment Variable di XAMPP:
 *   Buka file: C:\xampp\apache\conf\httpd.conf
 *   Tambahkan di baris paling bawah:
 *     SetEnv GROQ_API_KEY "gsk_xxxxxxxxxxxxxxxxxxxxxxxx"
 *   Lalu restart Apache di XAMPP Control Panel.
 *
 * OPSI 2 - File .env lokal (jangan commit ke Git):
 *   Buat file: config/.env (di folder config project ini)
 *   Isi dengan satu baris:
 *     GROQ_API_KEY=gsk_xxxxxxxxxxxxxxxxxxxxxxxx
 *   File .env sudah di-exclude dari akses browser oleh .htaccess
 *
 * OPSI 3 - Langsung di file ini (HANYA untuk development lokal):
 *   Ganti nilai GROQ_API_KEY_FALLBACK di bawah.
 *   JANGAN gunakan opsi ini di server production.
 *   JANGAN commit file ini ke Git jika berisi API key.
 * ============================================================
 */

// ============================================================
// 1. LOAD API KEY (prioritas: env var > .env file > fallback)
// ============================================================

// Cek environment variable terlebih dahulu
$_groq_api_key = getenv('GROQ_API_KEY');
if (empty($_groq_api_key)) {
    $_groq_api_key = $_SERVER['GROQ_API_KEY'] ?? '';
}

// Jika env var kosong, coba baca dari file .env
if (empty($_groq_api_key)) {
    $env_file = __DIR__ . '/.env';
    if (file_exists($env_file)) {
        $env_lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($env_lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, 'GROQ_API_KEY=') === 0) {
                $_groq_api_key = trim(substr($line, strlen('GROQ_API_KEY=')));
                // Hapus kutip jika ada
                $_groq_api_key = trim($_groq_api_key, '"\'');
                break;
            }
        }
    }
}

// Fallback: isi manual untuk development lokal saja
if (empty($_groq_api_key)) {
    $_groq_api_key = ''; // <-- ISI API KEY DISINI JIKA OPSI 1 & 2 TIDAK DIGUNAKAN
}

// ============================================================
// 2. KONSTANTA KONFIGURASI GROQ
// ============================================================
if (!defined('GROQ_API_KEY')) {
    define('GROQ_API_KEY', $_groq_api_key);
}
define('GROQ_MODEL', 'openai/gpt-oss-20b');
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
define('GROQ_MAX_TOKENS', 600);
define('GROQ_TEMPERATURE', 0.3);
define('GROQ_TIMEOUT', 30); // timeout cURL dalam detik

// ============================================================
// 3. SYSTEM PROMPT ANTI-HALLUCINATION
// ============================================================
define('GROQ_SYSTEM_PROMPT', 
'Kamu adalah Academic Assistant untuk Knowledge Base Jurusan Teknologi Informasi Politeknik Negeri Lampung (Polinela).

ATURAN KETAT:
1. Jawablah HANYA berdasarkan CONTEXT yang diberikan di bawah.
2. Jangan mengarang informasi tentang Polinela, Jurusan TI, Manajemen Informatika, jadwal, SKS, biaya, prosedur, kontak, akreditasi, kurikulum, atau informasi akademik lainnya.
3. Jika CONTEXT tidak cukup untuk menjawab pertanyaan, katakan bahwa informasi tersebut belum tersedia atau belum cukup terverifikasi di Knowledge Base.
4. Jangan menggunakan pengetahuan umum model sebagai fakta spesifik tentang Polinela.
5. Jika terdapat catatan PERLU VERIFIKASI atau informasi lama, sampaikan status tersebut kepada pengguna.
6. Jawab dalam bahasa Indonesia yang natural, singkat, dan mudah dipahami mahasiswa.
7. Jangan menyebut istilah internal seperti RAG, context window, prompt, token, database, atau system prompt.
8. Jangan membuat URL, nomor kontak, jadwal, atau angka SKS yang tidak ada di CONTEXT.
9. Jika ada sumber informasi di CONTEXT, sebutkan sumbernya.
10. Gunakan format yang rapi: bullet point jika cocok, paragraf pendek, langsung ke inti jawaban.');

// ============================================================
// 4. PROMPT & FUNGSI PEMANGGILAN GROQ API
// ============================================================

/**
 * Memproses percakapan casual tanpa Knowledge Base.
 * Tidak boleh digunakan untuk pertanyaan akademik.
 */
function call_groq_casual_api($user_message, $recent_history = []) {
    if (empty(GROQ_API_KEY)) {
        return ['success' => false, 'answer' => '', 'error' => 'GROQ_API_KEY belum dikonfigurasi.'];
    }

    $messages = [[
        'role' => 'system',
        'content' => 'Kamu adalah chatbot ramah untuk Knowledge Base Akademik TI Polinela. Ini adalah percakapan casual, bukan pertanyaan akademik. Balas secara natural dalam bahasa Indonesia, singkat, hangat, dan sopan. Kamu boleh menyapa, membalas terima kasih, menjawab sapaan, atau mengucapkan salam perpisahan. Jangan memberikan fakta atau informasi spesifik tentang Polinela, Jurusan TI, jadwal, SKS, prosedur, kontak, biaya, atau akademik jika tidak diberikan dalam pesan. Jika pengguna mengubah topik menjadi pertanyaan akademik, arahkan mereka untuk menanyakan informasi akademik secara spesifik.'
    ]];

    foreach (array_slice($recent_history, -2) as $history_item) {
        if (!empty($history_item['user_message'])) {
            $messages[] = ['role' => 'user', 'content' => mb_substr($history_item['user_message'], 0, 200)];
        }
        if (!empty($history_item['bot_answer'])) {
            $messages[] = ['role' => 'assistant', 'content' => mb_substr($history_item['bot_answer'], 0, 300)];
        }
    }
    $messages[] = ['role' => 'user', 'content' => $user_message];

    return groq_request($messages);
}

/**
 * Mengirim request umum ke endpoint Groq secara server-side.
 */
function groq_request($messages) {
    if (empty(GROQ_API_KEY)) {
        return ['success' => false, 'answer' => '', 'error' => 'GROQ_API_KEY belum dikonfigurasi.'];
    }

    $payload = [
        'model' => GROQ_MODEL,
        'messages' => $messages,
        'temperature' => GROQ_TEMPERATURE,
        'max_completion_tokens' => GROQ_MAX_TOKENS,
    ];

    $ch = curl_init(GROQ_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . GROQ_API_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => GROQ_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['success' => false, 'answer' => '', 'error' => 'Connection error: ' . $curl_error];
    }

    $data = json_decode($response, true);
    if ($http_code >= 200 && $http_code < 300 && !empty($data['choices'][0]['message']['content'])) {
        return ['success' => true, 'answer' => trim($data['choices'][0]['message']['content']), 'error' => ''];
    }

    return ['success' => false, 'answer' => '', 'error' => $data['error']['message'] ?? "HTTP $http_code"];
}

/**
 * Mengirim request ke Groq API

 *
 * @param string $user_message  Pertanyaan user
 * @param string $context       Knowledge Base context yang sudah difilter
 * @param array  $recent_history  Riwayat percakapan terakhir (max 2 pesan)
 * @return array ['success' => bool, 'answer' => string, 'error' => string]
 */
function call_groq_api($user_message, $context, $recent_history = []) {
    // Validasi API key
    if (empty(GROQ_API_KEY)) {
        return [
            'success' => false,
            'answer'  => '',
            'error'   => 'GROQ_API_KEY belum dikonfigurasi.'
        ];
    }

    // Bangun messages array
    $messages = [];

    // System prompt + context
    $system_content = GROQ_SYSTEM_PROMPT;
    if (!empty($context)) {
        $system_content .= "\n\n=== CONTEXT DARI KNOWLEDGE BASE ===\n" . $context;
    }
    $messages[] = ['role' => 'system', 'content' => $system_content];

    // Tambahkan riwayat percakapan terakhir (max 2 pesan untuk konteks)
    if (!empty($recent_history)) {
        $history_slice = array_slice($recent_history, -2);
        foreach ($history_slice as $h) {
            if (!empty($h['user_message'])) {
                $messages[] = ['role' => 'user', 'content' => mb_substr($h['user_message'], 0, 200)];
            }
            if (!empty($h['bot_answer'])) {
                $messages[] = ['role' => 'assistant', 'content' => mb_substr($h['bot_answer'], 0, 300)];
            }
        }
    }

    // Pertanyaan user saat ini
    $messages[] = ['role' => 'user', 'content' => $user_message];

    // Payload
    $payload = [
        'model'                 => GROQ_MODEL,
        'messages'              => $messages,
        'temperature'           => GROQ_TEMPERATURE,
        'max_completion_tokens' => GROQ_MAX_TOKENS,
    ];

    // cURL request
    $ch = curl_init(GROQ_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . GROQ_API_KEY,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => GROQ_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // Handle cURL error
    if ($response === false) {
        return [
            'success' => false,
            'answer'  => '',
            'error'   => 'Connection error: ' . $curl_error
        ];
    }

    // Parse response
    $data = json_decode($response, true);

    if ($http_code >= 200 && $http_code < 300 && !empty($data['choices'][0]['message']['content'])) {
        return [
            'success' => true,
            'answer'  => trim($data['choices'][0]['message']['content']),
            'error'   => ''
        ];
    }

    // API error
    $error_msg = $data['error']['message'] ?? "HTTP $http_code";
    return [
        'success' => false,
        'answer'  => '',
        'error'   => $error_msg
    ];
}
