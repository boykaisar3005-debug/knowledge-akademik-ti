<?php
/**
 * Tes manual Groq API (server-side only).
 * Jalankan dari localhost setelah GROQ_API_KEY dikonfigurasi.
 */
require_once __DIR__ . '/config/groq.php';
header('Content-Type: text/plain; charset=utf-8');

if (GROQ_API_KEY === '') {
    http_response_code(503);
    exit("GROQ_API_KEY belum dikonfigurasi.\n");
}

$result = call_groq_api(
    'Jelaskan apa itu KRS dalam konteks perkuliahan, maksimal 3 kalimat.',
    "[KNOWLEDGE 1]\nKategori: KRS\nPertanyaan: Bagaimana alur pengisian KRS di SIAKAD Polinela?\nJawaban: Pengisian KRS dilakukan melalui SIAKAD Polinela sesuai panduan akademik.",
    []
);

if ($result['success']) {
    echo $result['answer'];
} else {
    http_response_code(502);
    echo "Tes Groq gagal. Periksa konfigurasi server dan log Apache.\n";
}
