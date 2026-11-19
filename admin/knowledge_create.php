<?php
/**
 * ============================================================
 * FILE     : admin/knowledge_create.php
 * FUNGSI   : Form Tambah Data Knowledge & Keywords Baru
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * TAHAP    : 4 - Knowledge Base Management
 * ============================================================
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/auth.php';
require_admin();

$admin_user = get_admin_user();
$error_msg = '';

// Ambil semua kategori aktif untuk dropdown
$stmt_cats = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
$categories = $stmt_cats->fetchAll();

// Jika form dikirim (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)($_POST['category_id'] ?? 0);
    $question    = clean_input($_POST['question'] ?? '');
    $answer      = trim($_POST['answer'] ?? '');
    $keywords_raw= clean_input($_POST['keywords'] ?? '');
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    if ($category_id <= 0 || empty($question) || empty($answer)) {
        $error_msg = 'Kategori, Pertanyaan, dan Jawaban wajib diisi!';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Simpan ke tabel knowledge
            $stmt_ins = $pdo->prepare("
                INSERT INTO knowledge (category_id, question, answer, created_by, is_active)
                VALUES (:cat_id, :question, :answer, :created_by, :is_active)
            ");
            $stmt_ins->execute([
                'cat_id'     => $category_id,
                'question'   => $question,
                'answer'     => $answer,
                'created_by' => $admin_user['id'],
                'is_active'  => $is_active
            ]);
            $knowledge_id = $pdo->lastInsertId();

            // 2. Simpan kata kunci ke tabel keywords jika ada
            if (!empty($keywords_raw)) {
                // Dipisahkan oleh koma atau baris baru
                $kw_list = preg_split('/[,;\n]+/', $keywords_raw);
                $stmt_kw = $pdo->prepare("INSERT INTO keywords (knowledge_id, keyword) VALUES (:k_id, :kw)");
                
                foreach ($kw_list as $kw) {
                    $clean_kw = trim(mb_strtolower($kw, 'UTF-8'));
                    if (!empty($clean_kw)) {
                        $stmt_kw->execute([
                            'k_id' => $knowledge_id,
                            'kw'   => $clean_kw
                        ]);
                    }
                }
            }

            $pdo->commit();

            set_flash('success', 'Informasi Knowledge Base baru berhasil ditambahkan!');
            redirect('/admin/knowledge_index.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}

$page_title = 'Tambah Knowledge Baru';
require_once __DIR__ . '/header.php';
?>

<section class="page-content" style="padding-top:24px;">
    <div class="container" style="max-width:800px;">

        <a href="knowledge_index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Knowledge
        </a>

        <div class="card">
            <h2 style="color:var(--brown); font-size:1.4rem; font-weight:700; margin-bottom:6px;">
                <i class="fas fa-plus-circle" style="color:var(--brown-warm);"></i> Tambah Informasi Knowledge Base
            </h2>
            <p style="color:var(--text-light); font-size:0.88rem; margin-bottom:24px;">
                Masukkan pertanyaan dan jawaban resmi akademik Jurusan TI Polinela
            </p>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <form action="knowledge_create.php" method="POST">
                
                <!-- Kategori -->
                <div class="form-group">
                    <label for="category_id">Kategori Informasi akademik <span style="color:#e74c3c;">*</span></label>
                    <select name="category_id" id="category_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?> — <?php echo htmlspecialchars($cat['description']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Pertanyaan -->
                <div class="form-group">
                    <label for="question">Pertanyaan Mahasiswa <span style="color:#e74c3c;">*</span></label>
                    <input type="text" id="question" name="question" placeholder="Contoh: Kapan pendaftaran Magang/PKL Polinela dibuka?" 
                           value="<?php echo isset($_POST['question']) ? htmlspecialchars($_POST['question']) : ''; ?>" required>
                    <span style="font-size:0.78rem; color:var(--text-light); display:block; margin-top:4px;">
                        Tuliskan bentuk pertanyaan yang biasa ditanyakan mahasiswa.
                    </span>
                </div>

                <!-- Jawaban -->
                <div class="form-group">
                    <label for="answer">Jawaban Informasi Akademik <span style="color:#e74c3c;">*</span></label>
                    <textarea id="answer" name="answer" rows="6" placeholder="Tuliskan jawaban yang lengkap, jelas, dan akurat mengenai informasi akademik Polinela..." required><?php echo isset($_POST['answer']) ? htmlspecialchars($_POST['answer']) : ''; ?></textarea>
                </div>

                <!-- Kata Kunci (Keywords) -->
                <div class="form-group">
                    <label for="keywords"><i class="fas fa-tags"></i> Kata Kunci (Keywords) untuk Chatbot</label>
                    <textarea id="keywords" name="keywords" rows="3" placeholder="Masukkan kata kunci yang relevan dipisahkan dengan koma. Contoh: pkl, magang, pendaftaran pkl, prakerin, surat pkl"><?php echo isset($_POST['keywords']) ? htmlspecialchars($_POST['keywords']) : ''; ?></textarea>
                    <span style="font-size:0.78rem; color:var(--text-light); display:block; margin-top:4px;">
                        Kata kunci ini membantu chatbot mencocokkan pertanyaan variatif mahasiswa. Pisahkan tiap kata kunci dengan koma (`,`).
                    </span>
                </div>

                <!-- Status Aktif -->
                <div class="form-group" style="display:flex; align-items:center; gap:10px; margin-top:10px;">
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked style="width:auto;">
                    <label for="is_active" style="margin-bottom:0; cursor:pointer; font-weight:normal;">
                        Aktifkan artikel informasi ini (Dapat dibaca mahasiswa & chatbot)
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div style="display:flex; gap:12px; margin-top:28px;">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Simpan Knowledge
                    </button>
                    <a href="knowledge_index.php" class="btn-secondary">Batal</a>
                </div>

            </form>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
