-- Seed data uji chatbot. Tidak mengubah/menghapus data yang sudah ada.
-- Jalankan setelah database.sql di-import.
USE db_akademik_chatbot;

INSERT INTO knowledge (category_id, question, answer, created_by, is_active)
SELECT 1, 'Apa akreditasi prodi D3 Manajemen Informatika Polinela?', 'Peringkat "Baik Sekali" dari LAM INFOKOM (SK 023/SK/LAM-INFOKOM/Ak/D3/III/2025), berlaku 13 Maret 2025–13 Maret 2030.\n\nSumber: Berita resmi Polinela\nhttps://polinela.ac.id/program-studi-manajemen-informatika-polinela-raih-akreditasi-baik-sekali-dari-lam-infokom/\nTerakhir diverifikasi: 03-10-2026.', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM knowledge WHERE question = 'Apa akreditasi prodi D3 Manajemen Informatika Polinela?');
SET @k1 = (SELECT id FROM knowledge WHERE question = 'Apa akreditasi prodi D3 Manajemen Informatika Polinela?' LIMIT 1);
INSERT INTO keywords (knowledge_id, keyword) SELECT @k1, 'akreditasi' WHERE @k1 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k1 AND keyword='akreditasi');
INSERT INTO keywords (knowledge_id, keyword) SELECT @k1, 'manajemen informatika' WHERE @k1 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k1 AND keyword='manajemen informatika');
INSERT INTO keywords (knowledge_id, keyword) SELECT @k1, 'mi' WHERE @k1 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k1 AND keyword='mi');

INSERT INTO knowledge (category_id, question, answer, created_by, is_active)
SELECT 1, 'Berapa lama kuliah D3 MI dan apa gelarnya?', '3 tahun (6 semester), gelar Ahli Madya (A.Md.). Total SKS tercatat 113–114 tergantung halaman; konfirmasi ke prodi untuk kurikulum yang berlaku.\n\nSumber: Halaman prodi MI Polinela\nhttps://polinela.ac.id/program-studi-manajemen-informatika/\nTerakhir diverifikasi: 03-10-2026.', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM knowledge WHERE question = 'Berapa lama kuliah D3 MI dan apa gelarnya?');
SET @k2 = (SELECT id FROM knowledge WHERE question = 'Berapa lama kuliah D3 MI dan apa gelarnya?' LIMIT 1);
INSERT INTO keywords (knowledge_id, keyword) SELECT @k2, 'masa studi' WHERE @k2 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k2 AND keyword='masa studi');
INSERT INTO keywords (knowledge_id, keyword) SELECT @k2, 'gelar' WHERE @k2 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k2 AND keyword='gelar');
INSERT INTO keywords (knowledge_id, keyword) SELECT @k2, 'd3 mi' WHERE @k2 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k2 AND keyword='d3 mi');

INSERT INTO knowledge (category_id, question, answer, created_by, is_active)
SELECT 1, 'Bagaimana menghubungi Jurusan TI?', 'Telepon (0721) 703995, email jtiofficial@polinela.ac.id, Gedung Prof. R.A. Bustomi Rosadi.\n\nSumber: Situs JTI\nhttps://jti.polinela.ac.id/\nTerakhir diverifikasi: 03-10-2026.', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM knowledge WHERE question = 'Bagaimana menghubungi Jurusan TI?');
SET @k3 = (SELECT id FROM knowledge WHERE question = 'Bagaimana menghubungi Jurusan TI?' LIMIT 1);
INSERT INTO keywords (knowledge_id, keyword) SELECT @k3, 'kontak' WHERE @k3 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k3 AND keyword='kontak');
INSERT INTO keywords (knowledge_id, keyword) SELECT @k3, 'jurusan ti' WHERE @k3 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k3 AND keyword='jurusan ti');

INSERT INTO knowledge (category_id, question, answer, created_by, is_active)
SELECT 1, 'Prodi apa saja di Jurusan TI?', 'D3 Manajemen Informatika; S1 Terapan Teknologi Rekayasa Internet, Rekayasa Perangkat Lunak, Rekayasa Elektronika, dan Sains Data Terapan.\n\nSumber: Halaman Jurusan TI Polinela\nhttps://polinela.ac.id/jurusan/teknologi-informasi/\nTerakhir diverifikasi: 03-10-2026.', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM knowledge WHERE question = 'Prodi apa saja di Jurusan TI?');
SET @k4 = (SELECT id FROM knowledge WHERE question = 'Prodi apa saja di Jurusan TI?' LIMIT 1);
INSERT INTO keywords (knowledge_id, keyword) SELECT @k4, 'prodi' WHERE @k4 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k4 AND keyword='prodi');
INSERT INTO keywords (knowledge_id, keyword) SELECT @k4, 'jurusan ti' WHERE @k4 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k4 AND keyword='jurusan ti');

INSERT INTO knowledge (category_id, question, answer, created_by, is_active)
SELECT 10, 'Berapa SKS PKL dan Tugas Akhir di MI?', 'Menurut kurikulum lama: PKL 6 SKS dan Tugas Akhir 4 SKS di semester VI. Cek kurikulum terbaru ke Prodi MI.\n\nSumber: Kurikulum MI Polinela\nhttps://mi.polinela.ac.id/profil/kurikulum/\nTerakhir diverifikasi: 03-10-2026.\n\n[PERLU VERIFIKASI]', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM knowledge WHERE question = 'Berapa SKS PKL dan Tugas Akhir di MI?');
SET @k5 = (SELECT id FROM knowledge WHERE question = 'Berapa SKS PKL dan Tugas Akhir di MI?' LIMIT 1);
INSERT INTO keywords (knowledge_id, keyword) SELECT @k5, 'pkl' WHERE @k5 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k5 AND keyword='pkl');
INSERT INTO keywords (knowledge_id, keyword) SELECT @k5, 'sks' WHERE @k5 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k5 AND keyword='sks');
INSERT INTO keywords (knowledge_id, keyword) SELECT @k5, 'tugas akhir' WHERE @k5 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM keywords WHERE knowledge_id=@k5 AND keyword='tugas akhir');
