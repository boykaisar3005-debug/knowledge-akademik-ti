-- ============================================================
-- DATABASE: db_akademik_chatbot
-- PROJECT : Knowledge Base Akademik Mahasiswa TI - Polinela
-- INSTITUSI: Politeknik Negeri Lampung (Polinela)
-- JURUSAN  : Teknologi Informasi (JTI)
-- FOKUS PRODI: D3 Manajemen Informatika (berdiri 2006, 6 Semester/114 SKS, KKNI Level 5)
-- TAHAP   : 4 & 5 - Seed Data Resmi Polinela JTI & Manajemen Informasi
-- ============================================================

CREATE DATABASE IF NOT EXISTS db_akademik_chatbot
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_akademik_chatbot;

-- ============================================================
-- TABEL 1: users
-- Menyimpan akun admin yang bisa login ke dashboard
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- disimpan dengan password_hash()
    full_name   VARCHAR(100) NOT NULL,
    role        ENUM('admin', 'superadmin') NOT NULL DEFAULT 'admin',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 2: categories
-- Menyimpan kategori informasi akademik Polinela JTI
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,   -- versi URL-friendly dari name
    description TEXT,
    icon        VARCHAR(50)  DEFAULT 'fa-folder', -- nama ikon FontAwesome
    is_active   TINYINT(1)   DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 3: knowledge
-- Menyimpan pasangan pertanyaan & jawaban dalam Knowledge Base
-- ============================================================
CREATE TABLE IF NOT EXISTS knowledge (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    category_id  INT          NOT NULL,
    question     TEXT         NOT NULL,          -- pertanyaan yang dikenali chatbot
    answer       TEXT         NOT NULL,          -- jawaban yang diberikan chatbot
    created_by   INT          NOT NULL,          -- id admin yang membuat
    is_active    TINYINT(1)   DEFAULT 1,
    view_count   INT          DEFAULT 0,         -- berapa kali jawaban ini ditampilkan
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by)  REFERENCES users(id)      ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 4: keywords
-- Setiap knowledge bisa memiliki banyak keyword (one-to-many)
-- Dipakai untuk pencocokan pertanyaan chatbot
-- ============================================================
CREATE TABLE IF NOT EXISTS keywords (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    knowledge_id INT         NOT NULL,
    keyword      VARCHAR(100) NOT NULL,
    FOREIGN KEY (knowledge_id) REFERENCES knowledge(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 5: chat_sessions
-- Setiap kali mahasiswa membuka chatbot, dibuat satu sesi baru
-- ============================================================
CREATE TABLE IF NOT EXISTS chat_sessions (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    session_code VARCHAR(64)  NOT NULL UNIQUE,  -- kode unik sesi (uniqid / random)
    student_name VARCHAR(100) DEFAULT 'Anonim',
    nim          VARCHAR(20)  DEFAULT NULL,
    started_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at     TIMESTAMP NULL DEFAULT NULL,
    ip_address   VARCHAR(45)  DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 6: chat_history
-- Menyimpan setiap pesan & balasan dalam sebuah sesi
-- ============================================================
CREATE TABLE IF NOT EXISTS chat_history (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    session_id     INT          NOT NULL,
    user_message   TEXT         NOT NULL,        -- pertanyaan mahasiswa
    bot_answer     TEXT         DEFAULT NULL,     -- jawaban chatbot
    knowledge_id   INT          DEFAULT NULL,     -- knowledge yang cocok (bisa NULL)
    status         ENUM('answered','unanswered') DEFAULT 'unanswered',
    confidence     DECIMAL(5,2) DEFAULT 0.00,    -- skor kecocokan (0-100)
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id)   REFERENCES chat_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (knowledge_id) REFERENCES knowledge(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 7: unanswered_questions
-- Pertanyaan yang tidak berhasil dijawab chatbot, untuk dikaji admin
-- ============================================================
CREATE TABLE IF NOT EXISTS unanswered_questions (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    chat_id       INT          DEFAULT NULL,     -- referensi ke chat_history
    question_text TEXT         NOT NULL,
    frequency     INT          DEFAULT 1,        -- berapa kali pertanyaan ini muncul
    is_resolved   TINYINT(1)   DEFAULT 0,        -- sudah dijawabkan ke KB atau belum
    resolved_by   INT          DEFAULT NULL,     -- admin yang menyelesaikan
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id)     REFERENCES chat_history(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id)        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL 8: feedback
-- Like/dislike dan komentar mahasiswa terhadap jawaban chatbot
-- ============================================================
CREATE TABLE IF NOT EXISTS feedback (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    chat_id      INT          NOT NULL,
    rating       ENUM('like','dislike') NOT NULL,
    comment      TEXT         DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chat_history(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- DATA AWAL: users
-- Password admin demo dibuat acak dan tidak diketahui; set password lokal sendiri.
-- Jangan memakai akun ini pada server produksi.
-- ============================================================
INSERT INTO users (id, username, password, full_name, role) VALUES
(
    1,
    'admin',
    '$2y$10$NU.91VYtB0f1oDVOMPGGme6I4XBpYkLJcL/GlkC0m6lcKjq7n8BKO',
    'Admin JTI Polinela',
    'superadmin'
) ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);


-- ============================================================
-- DATA AWAL: categories (13 Kategori Informasi Akademik JTI Polinela)
-- ============================================================
INSERT INTO categories (id, name, slug, description, icon) VALUES
(1,  'Informasi Jurusan/Prodi',  'prodi',        'Profil JTI Polinela, D3 Manajemen Informatika, profil lulusan, & 5 prodi JTI', 'fa-university'),
(2,  'KRS',                      'krs',          'Kartu Rencana Studi - pengisian dan pengajuan KRS di SIAKAD Polinela',         'fa-file-alt'),
(3,  'KHS',                      'khs',          'Kartu Hasil Studi - nilai semester dan akumulasi IPK Polinela',                'fa-chart-bar'),
(4,  'Perkuliahan',              'perkuliahan',  'Informasi umum perkuliahan vokasi, VClass Polinela, & presensi',               'fa-chalkboard-teacher'),
(5,  'Jadwal Kuliah',            'jadwal',       'Jadwal perkuliahan semester ganjil/genap JTI Polinela',                        'fa-calendar-alt'),
(6,  'Ujian',                    'ujian',        'Pelaksanaan UTS dan UAS terintegrasi di Jurusan TI',                           'fa-pencil-alt'),
(7,  'Praktikum',                'praktikum',    'Kegiatan praktikum terapan di Laboratorium Komputer JTI Polinela',            'fa-flask'),
(8,  'Tugas',                    'tugas',        'Informasi pengerjaan tugas perkuliahan terapan & penyetoran tugas',            'fa-tasks'),
(9,  'Magang/PKL',               'pkl',          'Program Magang & Praktik Kerja Lapangan Mahasiswa JTI Polinela',                'fa-briefcase'),
(10, 'Tugas Akhir',              'tugas-akhir',  'Proyek Akhir/Skripsi vokasi, bimbingan, & sidang kelulusan',                  'fa-graduation-cap'),
(11, 'Administrasi Akademik',    'administrasi', 'Surat Keterangan Aktif, Izin Penelitian, & Pembayaran UKT Polinela',           'fa-stamp'),
(12, 'Beasiswa',                 'beasiswa',     'Beasiswa KIP-Kuliah, UKT, BI, dan Pemda untuk Mahasiswa Polinela',            'fa-hand-holding-usd'),
(13, 'Organisasi/Kegiatan Mahasiswa', 'organisasi', 'HMJ TI Polinela & Study Club (Coding, Cyber, Network, Multimedia)',        'fa-users')
ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), icon=VALUES(icon);


-- ============================================================
-- DATA RESMI POLINELA (JTI & D3 MANAJEMEN INFORMATIKA): knowledge
-- ============================================================
INSERT INTO knowledge (id, category_id, question, answer, created_by) VALUES
(
    1,
    1,
    'Apa saja Program Studi di Jurusan Teknologi Informasi (JTI) Polinela?',
    'Jurusan Teknologi Informasi (JTI) Politeknik Negeri Lampung saat ini mencantumkan 5 Program Studi unggulan:\n\n1. D3 Manajemen Informatika (Fokus utama vokasi sistem informasi terapan)\n2. S1 Terapan Teknologi Rekayasa Perangkat Lunak (TRPL)\n3. S1 Terapan Teknologi Rekayasa Internet (TRI)\n4. S1 Terapan Teknologi Rekayasa Elektronika\n5. S1 Terapan Sains Data Terapan',
    1
),
(
    2,
    1,
    'Bagaimana profil dan kompetensi lulusan D3 Manajemen Informatika Polinela?',
    'Program Studi D3 Manajemen Informatika Polinela berdiri sejak tahun 2006 dengan jenjang Diploma III vokasi. Masa studi adalah 3 tahun / 6 semester dengan total 114 SKS.\n\nKompetensi lulusan mengacu pada kajian ACM-IEEE, KKNI Level 5, serta rekomendasi APTIKOM Level 5.\n\nProfil Lulusan D3 MI meliputi:\n1. Programmer Sistem Informasi\n2. Administrator Web\n3. IT Support\n4. Wirausaha Bidang TI (Technopreneur)',
    1
),
(
    3,
    2,
    'Bagaimana alur pengisian KRS di SIAKAD Polinela?',
    'Pengisian Kartu Rencana Studi (KRS) di Politeknik Negeri Lampung dilakukan secara online melalui portal SIAKAD Polinela (siakad.polinela.ac.id).\n\nAlur Pengisian:\n1. Pelunasan UKT semester berjalan.\n2. Login ke SIAKAD Polinela dengan NIM dan password.\n3. Masuk ke menu Akademik -> Pengisian KRS.\n4. Memilih paket mata kuliah semester berjalan.\n5. Berkonsultasi dengan Dosen Pembimbing Akademik (DPA) untuk mendapatkan pengesahan/ACC KRS di SIAKAD.',
    1
),
(
    4,
    3,
    'Bagaimana cara mengecek KHS dan IPK di SIAKAD Polinela?',
    'Kartu Hasil Studi (KHS) dan Indeks Prestasi Kumulatif (IPK) mahasiswa dapat diakses di SIAKAD Polinela (siakad.polinela.ac.id).\n\nCaranya:\n1. Login akun mahasiswa di SIAKAD Polinela.\n2. Pilih menu KHS / Laporan Nilai.\n3. Pilih Tahun Akademik dan Semester yang sesuai.\n4. KHS menampilkan daftar nilai huruf, bobot mutu, IP Semester, dan akumulasi IPK. Anda juga dapat mengunduh berkas KHS digital.',
    1
),
(
    5,
    4,
    'Apa itu VClass Polinela dan bagaimana penggunaannya?',
    'VClass Polinela (vclass.polinela.ac.id) adalah Learning Management System (LMS) resmi Polinela untuk mendukung perkuliahan vokasi terapan secara daring.\n\nFungsi VClass:\n- Mengunduh materi kuliah dan modul praktikum.\n- Mengunggah tugas perkuliahan harian/mingguan.\n- Mengikuti kuis daring dan diskusi forum dengan dosen pengampu.',
    1
),
(
    6,
    5,
    'Dimana mahasiswa dapat melihat Jadwal Perkuliahan JTI Polinela?',
    'Jadwal perkuliahan Jurusan Teknologi Informasi Polinela dapat diakses melalui:\n1. Akun SIAKAD Polinela masing-masing mahasiswa.\n2. Papan Pengumuman Resmi Gedung JTI Polinela.\n3. Pengumuman resmi dari Sekretariat Jurusan TI Polinela.',
    1
),
(
    7,
    6,
    'Bagaimana ketentuan dan jadwal pelaksanaan UTS/UAS di JTI Polinela?',
    'Pelaksanaan UTS berlangsung pada pertengahan semester (minggu ke-8/9) dan UAS di akhir semester (minggu ke-16/17) sesuai Kalender Akademik Polinela.\n\nKetentuan Ujian:\n1. Syarat presensi kehadiran minimal 80%.\n2. Wajib membawa Kartu Ujian Resmi dari SIAKAD.\n3. Menggunakan pakaian rapi (kemeja putih, celana/rok gelap) dan bersepatu.',
    1
),
(
    8,
    7,
    'Apa saja aturan praktikum di Laboratorium Komputer JTI Polinela?',
    'Aturan Praktikum Laboratorium Komputer JTI Polinela:\n1. Hadir tepat waktu sesuai jadwal praktikum.\n2. Menggunakan pakaian rapi (kemeja/kaos berkerah) dan sepatu tertutup.\n3. Dilarang membawa makanan dan minuman ke area komputer lab.\n4. Mengisi lembar logbook penggunaan unit komputer sebelum dan sesudah praktikum.',
    1
),
(
    9,
    8,
    'Bagaimana pengumpulan tugas perkuliahan mahasiswa JTI?',
    'Pengumpulan tugas proyek atau latihan praktikum mahasiswa JTI Polinela dilakukan melalui:\n1. LMS VClass Polinela (vclass.polinela.ac.id) sesuai tenggat waktu (*deadline*) yang ditetapkan dosen.\n2. Repository / Git submission untuk tugas akhir/pemrograman (sesuai instruksi dosen pengampu).',
    1
),
(
    10,
    9,
    'Apa saja syarat dan alur pengajuan Magang/PKL bagi Mahasiswa D3 Manajemen Informatika?',
    'Magang / Praktik Kerja Lapangan (PKL) D3 Manajemen Informatika JTI Polinela:\n\nSyarat:\n- Mahasiswa aktif semester 5/6 yang telah menempuh minimal SKS prasyarat tanpa nilai E.\n\nProsedur:\n1. Mengajukan surat permohonan PKL ke Bagian Administrasi JTI Polinela.\n2. Mengirimkan surat resmi ke perusahaan/instansi target.\n3. Setelah dikonfirmasi diterima, melaporkan ke Koordinator PKL JTI untuk penetapan Dosen Pembimbing PKL.',
    1
),
(
    11,
    10,
    'Apa saja persyaratan Tugas Akhir (TA) untuk mahasiswa D3 Manajemen Informatika?',
    'Tugas Akhir (TA) D3 Manajemen Informatika Polinela:\n\nSyarat & Ketentuan:\n- Telah menempuh minimal 100 SKS dengan IPK >= 2.50.\n- Menghasilkan karya/sistem informasi terapan sesuai standar kompetensi lulusan (Programmer, Web Admin, IT Support).\n- Mengikuti Seminar Proposal TA, bimbingan minimal 8x per pembimbing, dan Sidang Akhir Tugas Akhir.',
    1
),
(
    12,
    11,
    'Bagaimana prosedur pembuatan Surat Keterangan Mahasiswa Aktif di JTI Polinela?',
    'Prosedur Permohonan Surat Keterangan Mahasiswa Aktif:\n1. Datang ke Sekretariat Administrasi JTI Polinela.\n2. Melampirkan fotokopi Kartu Tanda Mahasiswa (KTM) dan bukti lunas UKT semester berjalan.\n3. Petugas administrasi JTI akan memverifikasi data dan memproses pengesahan surat.',
    1
),
(
    13,
    12,
    'Apa saja program beasiswa yang bisa diajukan oleh mahasiswa Polinela?',
    'Program Beasiswa Polinela dikelola oleh Bagian Kemahasiswaan Polinela, meliputi:\n1. Beasiswa KIP-Kuliah\n2. Beasiswa Bank Indonesia (BI)\n3. Beasiswa Bantuan UKT / Kemendikbudristek\n4. Beasiswa Pemerintah Daerah Lampung\nInformasi pembukaan berkala diumumkan di papan pengumuman Kampus Polinela.',
    1
),
(
    14,
    13,
    'Apa saja himpunan mahasiswa dan komunitas study club di JTI Polinela?',
    'Organisasi & Komunitas di JTI Polinela:\n1. HMJ TI Polinela (Himpunan Mahasiswa Jurusan Teknologi Informasi)\n2. Polinela Developer Club (Web & Mobile Programming)\n3. Polinela Cyber Security (Keamanan Informasi & CTF)\n4. Network Study Club (Jaringan Komputer & Server)\n5. Multimedia & Game Dev Club',
    1
)
ON DUPLICATE KEY UPDATE question=VALUES(question), answer=VALUES(answer);


-- ============================================================
-- DATA KEYWORDS
-- ============================================================
INSERT INTO keywords (knowledge_id, keyword) VALUES
-- Knowledge 1 (Prodi JTI)
(1, 'prodi'), (1, 'jurusan ti'), (1, 'jti'), (1, 'jti polinela'), (1, 'd3 manajemen informatika'), (1, 'trpl'), (1, 'tri'), (1, 'sains data'), (1, 'elektronika'),
-- Knowledge 2 (D3 MI Detail)
(2, 'd3 mi'), (2, 'manajemen informatika'), (2, 'profil lulusan'), (2, 'sks'), (2, 'kkni'), (2, 'programmer'), (2, 'web admin'), (2, 'it support'), (2, 'aptikom'),
-- Knowledge 3 (KRS SIAKAD)
(3, 'krs'), (3, 'siakad'), (3, 'siakad polinela'), (3, 'isi krs'), (3, 'pengisian krs'), (3, 'dpa'), (3, 'pembimbing akademik'),
-- Knowledge 4 (KHS & IPK)
(4, 'khs'), (4, 'nilai'), (4, 'ipk'), (4, 'lihat nilai'), (4, 'khs polinela'), (4, 'cetak khs'),
-- Knowledge 5 (VClass)
(5, 'vclass'), (5, 'vclass polinela'), (5, 'lms'), (5, 'elearning'), (5, 'daring'),
-- Knowledge 6 (Jadwal Kuliah)
(6, 'jadwal'), (6, 'jadwal kuliah'), (6, 'jadwal matkul'), (6, 'ruang kuliah'),
-- Knowledge 7 (UTS & UAS)
(7, 'uts'), (7, 'uas'), (7, 'ujian'), (7, 'kartu ujian'), (7, 'jadwal ujian'), (7, 'syarat ujian'),
-- Knowledge 8 (Praktikum Lab)
(8, 'praktikum'), (8, 'lab'), (8, 'laboratorium'), (8, 'lab komputer'), (8, 'tata tertib lab'),
-- Knowledge 9 (Tugas Perkuliahan)
(9, 'tugas'), (9, 'kumpul tugas'), (9, 'tugas vclass'), (9, 'deadline tugas'), (9, 'proyek'),
-- Knowledge 10 (PKL / Magang)
(10, 'pkl'), (10, 'magang'), (10, 'praktik kerja lapangan'), (10, 'surat pkl'), (10, 'syarat pkl'),
-- Knowledge 11 (Tugas Akhir D3 MI)
(11, 'tugas akhir'), (11, 'ta'), (11, 'skripsi'), (11, 'sidang'), (11, 'sempro'), (11, 'proyek akhir'), (11, 'syarat ta d3 mi'),
-- Knowledge 12 (Surat Aktif & UKT)
(12, 'surat aktif'), (12, 'mahasiswa aktif'), (12, 'ukt'), (12, 'pembayaran ukt'), (12, 'administrasi jti'),
-- Knowledge 13 (Beasiswa Polinela)
(13, 'beasiswa'), (13, 'kip'), (13, 'kip kuliah'), (13, 'beasiswa bi'), (13, 'beasiswa ukt'),
-- Knowledge 14 (HMJ & Study Club)
(14, 'hmj'), (14, 'hmj ti'), (14, 'organisasi'), (14, 'cyber security'), (14, 'developer club'), (14, 'study club');
