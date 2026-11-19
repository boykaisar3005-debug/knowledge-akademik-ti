# Knowledge Akademik TI

Sistem knowledge base dan chatbot informasi akademik untuk mahasiswa Jurusan Teknologi Informasi, Politeknik Negeri Lampung (Polinela). Project ini dikembangkan sebagai portofolio aplikasi web PHP native dengan pengelolaan konten akademik melalui dashboard admin.

## Tujuan Pengembangan

- Memusatkan informasi akademik yang sering dicari mahasiswa.
- Menyediakan pencarian dan chatbot berbasis knowledge base.
- Mendemonstrasikan autentikasi admin, CRUD, statistik, dan integrasi database.
- Menjadi project portofolio untuk kebutuhan PKL, pekerjaan, CV, dan LinkedIn.

## Fitur Utama

- Beranda dengan statistik dan kategori informasi akademik.
- Eksplorasi artikel berdasarkan kategori.
- Pencarian pertanyaan dan jawaban.
- Chatbot akademik berbasis keyword matching dan tokenization.
- Riwayat sesi chat, feedback like/dislike, dan pencatatan pertanyaan yang belum terjawab.
- Login admin dengan `password_verify()` dan proteksi session.
- Dashboard statistik.
- CRUD knowledge base dan keywords.
- Integrasi opsional Groq API melalui environment variable atau file `.env` lokal.

## Teknologi yang Digunakan

- PHP 8.0+ native, PDO, prepared statements
- MySQL 8.0+ / MariaDB
- HTML5, CSS3, JavaScript vanilla, Fetch API/AJAX
- XAMPP (Apache dan MySQL)
- phpMyAdmin
- Groq API (opsional untuk fitur AI)

## Struktur Folder

```text
knowledge-akademik-ti/
├── admin/                 Dashboard, login, dan CRUD knowledge base
├── assets/                CSS, JavaScript, dan gambar
├── chatbot/               UI chatbot dan endpoint AJAX
├── config/                Koneksi database, helper, dan konfigurasi API
├── database/              Seed data tambahan untuk demo
├── knowledge/             Daftar artikel knowledge base
├── pages/                 Kategori dan pencarian
├── database.sql           Struktur database dan data demo
├── index.php              Halaman beranda
└── README.md              Dokumentasi project
```

## Persyaratan Sistem

- Windows dengan XAMPP
- Apache aktif
- MySQL atau MariaDB aktif
- PHP 8.0 atau lebih baru
- Browser modern
- Groq API key hanya jika fitur AI digunakan

## Instalasi Menggunakan XAMPP

1. Salin folder project ke `C:\xampp\htdocs\knowledge-akademik-ti\`.
2. Jalankan Apache dan MySQL dari XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`.
4. Import `database.sql`. Script akan membuat database `db_akademik_chatbot` beserta tabel dan data demo.
5. Pastikan konfigurasi pada `config/database.php` sesuai dengan MySQL lokal. Konfigurasi default XAMPP menggunakan host `localhost`, user `root`, password kosong, port `3306`.
6. Jika menggunakan fitur Groq, buat file `config/.env` secara lokal dan isi `GROQ_API_KEY=...`. File tersebut tidak boleh di-commit.

## Menjalankan Aplikasi

- Beranda: `http://localhost/knowledge-akademik-ti/`
- Chatbot: `http://localhost/knowledge-akademik-ti/chatbot/`
- Dashboard admin: `http://localhost/knowledge-akademik-ti/admin/login.php`

Database demo menyediakan data akun admin lokal setelah import SQL. Ubah password akun tersebut sebelum penggunaan di server produksi. Jangan menyimpan password produksi di repository.

## Konfigurasi Database

Konfigurasi default berada di `config/database.php`. Untuk XAMPP standar:

```text
Database : db_akademik_chatbot
Host     : localhost
Port     : 3306
Username : root
Password : (kosong, default XAMPP)
```

`database.sql` dan `database/seed_test_data.sql` berisi struktur serta data demo akademik, bukan backup database produksi.

## Screenshot

Belum ada screenshot yang disertakan dalam repository. Screenshot dapat ditambahkan ke `assets/img/` setelah tersedia.

## Catatan Keamanan

- File `config/.env` sengaja tidak disertakan dalam repository.
- Jangan commit API key, password, token, backup database produksi, atau data pribadi mahasiswa/dosen.
- Hash akun admin pada SQL hanya untuk instalasi demo lokal; ganti password sebelum deployment.

## Status Pengembangan

Project mencakup beranda, kategori, pencarian, chatbot, autentikasi admin, dashboard statistik, CRUD knowledge base, pertanyaan belum terjawab, dan feedback.
