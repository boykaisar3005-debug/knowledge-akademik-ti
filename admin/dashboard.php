<?php
/**
 * ============================================================
 * FILE     : admin/dashboard.php
 * FUNGSI   : Dashboard Utama Admin (Statistik & Aktivitas Terbaru)
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * TAHAP    : 3 - Dashboard Admin
 * ============================================================
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$page_title = 'Dashboard Admin';
require_once __DIR__ . '/header.php';

// ============================================================
// HIMPUN DATA STATISTIK UNTUK DASHBOARD
// ============================================================

// 1. Total Artikel Knowledge Base
$stmt_k = $pdo->query("SELECT COUNT(*) as total FROM knowledge WHERE is_active = 1");
$total_knowledge = $stmt_k->fetch()['total'];

// 2. Total Kategori
$stmt_c = $pdo->query("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
$total_categories = $stmt_c->fetch()['total'];

// 3. Total Sesi Chat Mahasiswa
$stmt_s = $pdo->query("SELECT COUNT(*) as total FROM chat_sessions");
$total_sessions = $stmt_s->fetch()['total'];

// 4. Total Pertanyaan Belum Terjawab (Unresolved)
$stmt_u = $pdo->query("SELECT COUNT(*) as total FROM unanswered_questions WHERE is_resolved = 0");
$total_unanswered = $stmt_u->fetch()['total'];

// 5. Total Feedback Likes & Dislikes
$stmt_fb_like = $pdo->query("SELECT COUNT(*) as total FROM feedback WHERE rating = 'like'");
$total_likes = $stmt_fb_like->fetch()['total'];

$stmt_fb_dislike = $pdo->query("SELECT COUNT(*) as total FROM feedback WHERE rating = 'dislike'");
$total_dislikes = $stmt_fb_dislike->fetch()['total'];


// ============================================================
// HIMPUN AKTIVITAS TERBARU
// ============================================================

// 1. 5 Chat Mahasiswa Terbaru
$stmt_recent_chat = $pdo->query("
    SELECT h.id, h.user_message, h.bot_answer, h.status, h.confidence, h.created_at,
           s.student_name
    FROM chat_history h
    JOIN chat_sessions s ON h.session_id = s.id
    ORDER BY h.id DESC
    LIMIT 5
");
$recent_chats = $stmt_recent_chat->fetchAll();

// 2. 5 Pertanyaan Belum Terjawab Paling Sering Ditanyakan
$stmt_top_unanswered = $pdo->query("
    SELECT id, question_text, frequency, created_at
    FROM unanswered_questions
    WHERE is_resolved = 0
    ORDER BY frequency DESC, id DESC
    LIMIT 5
");
$top_unanswered = $stmt_top_unanswered->fetchAll();
?>

<section class="page-content" style="padding-top: 24px;">
    <div class="container">

        <?php echo show_flash(); ?>

        <!-- JUDUL SECTION -->
        <div style="margin-bottom: 24px;">
            <h1 style="color:var(--brown); font-size:1.6rem; font-weight:700;">
                <i class="fas fa-tachometer-alt" style="color:var(--brown-warm);"></i> Ringkasan Sistem
            </h1>
            <p style="color:var(--text-light); font-size:0.9rem;">
                Tinjauan umum performa Knowledge Base dan kebutuhan informasi mahasiswa TI
            </p>
        </div>

        <!-- STATISTIK CARDS GRID -->
        <div class="admin-stats-grid">
            
            <!-- Card 1: Knowledge -->
            <div class="admin-stat-card">
                <div class="stat-icon" style="background:#eef2ff; color:#4f46e5;">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <span class="num"><?php echo $total_knowledge; ?></span>
                    <span class="label">Artikel Knowledge</span>
                </div>
            </div>

            <!-- Card 2: Kategori -->
            <div class="admin-stat-card">
                <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;">
                    <i class="fas fa-folder"></i>
                </div>
                <div class="stat-info">
                    <span class="num"><?php echo $total_categories; ?></span>
                    <span class="label">Kategori Aktif</span>
                </div>
            </div>

            <!-- Card 3: Sesi Chat -->
            <div class="admin-stat-card">
                <div class="stat-icon" style="background:#fff7ed; color:#ea580c;">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-info">
                    <span class="num"><?php echo $total_sessions; ?></span>
                    <span class="label">Sesi Percakapan</span>
                </div>
            </div>

            <!-- Card 4: Belum Terjawab -->
            <div class="admin-stat-card">
                <div class="stat-icon" style="background:#fef2f2; color:#dc2626;">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="stat-info">
                    <span class="num"><?php echo $total_unanswered; ?></span>
                    <span class="label">Pertanyaan Belum Terjawab</span>
                </div>
            </div>

            <!-- Card 5: Feedback -->
            <div class="admin-stat-card">
                <div class="stat-icon" style="background:#faf5ff; color:#9333ea;">
                    <i class="fas fa-thumbs-up"></i>
                </div>
                <div class="stat-info">
                    <span class="num"><?php echo $total_likes; ?> / <?php echo $total_dislikes; ?></span>
                    <span class="label">Feedback (Suka / Tidak)</span>
                </div>
            </div>

        </div>

        <!-- DUA KOLOM AKTIVITAS TERBARU -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; margin-top:24px;" class="admin-grid-2col">
            
            <!-- Kolom Kiri: Riwayat Chat Mahasiswa Terbaru -->
            <div class="card" style="margin-bottom:0;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="color:var(--brown); font-size:1.05rem; font-weight:700;">
                        <i class="fas fa-history" style="color:var(--brown-warm);"></i> Pertanyaan Mahasiswa Terbaru
                    </h3>
                </div>

                <?php if (count($recent_chats) > 0): ?>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <?php foreach ($recent_chats as $chat): ?>
                            <div style="padding:12px 14px; background:var(--cream); border-radius:var(--radius-sm); border:1px solid var(--beige-dark);">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                    <span style="font-size:0.8rem; font-weight:600; color:var(--brown);">
                                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($chat['student_name']); ?>
                                    </span>
                                    <span style="font-size:0.75rem; color:var(--text-light);">
                                        <?php echo date('H:i, d M', strtotime($chat['created_at'])); ?>
                                    </span>
                                </div>
                                <p style="font-size:0.88rem; font-weight:600; color:var(--text); margin-bottom:4px;">
                                    "<?php echo htmlspecialchars($chat['user_message']); ?>"
                                </p>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <?php if ($chat['status'] === 'answered'): ?>
                                        <span class="status-pill status-success"><i class="fas fa-check-circle"></i> Terjawab (<?php echo $chat['confidence']; ?>%)</span>
                                    <?php else: ?>
                                        <span class="status-pill status-danger"><i class="fas fa-times-circle"></i> Belum Terjawab</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--text-light); text-align:center; padding:20px;">Belum ada riwayat percakapan.</p>
                <?php endif; ?>
            </div>

            <!-- Kolom Kanan: Top Pertanyaan Belum Terjawab -->
            <div class="card" style="margin-bottom:0;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="color:var(--brown); font-size:1.05rem; font-weight:700;">
                        <i class="fas fa-exclamation-triangle" style="color:#e74c3c;"></i> Butuh Ditambahkan ke KB
                    </h3>
                </div>

                <?php if (count($top_unanswered) > 0): ?>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <?php foreach ($top_unanswered as $un): ?>
                            <div style="padding:12px 14px; background:#fef2f2; border-radius:var(--radius-sm); border:1px solid #fca5a5;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                    <span style="font-size:0.78rem; font-weight:700; color:#dc2626; background:#fee2e2; padding:2px 8px; border-radius:12px;">
                                        Ditanyakan <?php echo $un['frequency']; ?>x
                                    </span>
                                    <span style="font-size:0.75rem; color:var(--text-light);">
                                        <?php echo date('d M Y', strtotime($un['created_at'])); ?>
                                    </span>
                                </div>
                                <p style="font-size:0.88rem; font-weight:600; color:#991b1b; margin-top:4px;">
                                    "<?php echo htmlspecialchars($un['question_text']); ?>"
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align:center; padding:30px; color:var(--text-light);">
                        <i class="fas fa-check-circle" style="font-size:2.5rem; color:#22c55e; margin-bottom:10px; display:block;"></i>
                        <p>Semua pertanyaan mahasiswa sejauh ini berhasil dijawab oleh chatbot!</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
