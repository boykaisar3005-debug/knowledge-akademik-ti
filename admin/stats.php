<?php
/**
 * ============================================================
 * FILE     : admin/stats.php
 * FUNGSI   : Statistik Kebutuhan Informasi Mahasiswa & Feedback
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * TAHAP    : 5 - Statistik Kebutuhan Informasi Mahasiswa
 * ============================================================
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/auth.php';
require_admin();

// 1. Artikel Knowledge Paling Sering Dibaca (Top Read)
$stmt_top_read = $pdo->query("
    SELECT k.question, k.view_count, c.name as category_name
    FROM knowledge k
    JOIN categories c ON k.category_id = c.id
    WHERE k.is_active = 1
    ORDER BY k.view_count DESC
    LIMIT 5
");
$top_read = $stmt_top_read->fetchAll();

// 2. Kategori Paling Banyak Diakses
$stmt_cat_stats = $pdo->query("
    SELECT c.name, c.icon, COUNT(k.id) as total_knowledge, SUM(k.view_count) as total_views
    FROM categories c
    LEFT JOIN knowledge k ON c.id = k.category_id AND k.is_active = 1
    GROUP BY c.id
    ORDER BY total_views DESC, total_knowledge DESC
");
$cat_stats = $stmt_cat_stats->fetchAll();

// 3. Statistik Chatbot Performa
$stmt_chat_perf = $pdo->query("
    SELECT 
        COUNT(*) as total_messages,
        SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END) as answered_count,
        SUM(CASE WHEN status = 'unanswered' THEN 1 ELSE 0 END) as unanswered_count,
        AVG(confidence) as avg_confidence
    FROM chat_history
");
$chat_perf = $stmt_chat_perf->fetch();

$total_messages  = (int)($chat_perf['total_messages'] ?? 0);
$answered_count  = (int)($chat_perf['answered_count'] ?? 0);
$unanswered_count= (int)($chat_perf['unanswered_count'] ?? 0);
$avg_confidence  = round((float)($chat_perf['avg_confidence'] ?? 0), 2);
$success_rate    = $total_messages > 0 ? round(($answered_count / $total_messages) * 100, 1) : 0;

// 4. Rekap Feedback Mahasiswa
$stmt_fb_summary = $pdo->query("
    SELECT 
        COUNT(*) as total_feedback,
        SUM(CASE WHEN rating = 'like' THEN 1 ELSE 0 END) as total_likes,
        SUM(CASE WHEN rating = 'dislike' THEN 1 ELSE 0 END) as total_dislikes
    FROM feedback
");
$fb_summary = $stmt_fb_summary->fetch();

$total_feedback = (int)($fb_summary['total_feedback'] ?? 0);
$total_likes    = (int)($fb_summary['total_likes'] ?? 0);
$total_dislikes = (int)($fb_summary['total_dislikes'] ?? 0);
$like_rate      = $total_feedback > 0 ? round(($total_likes / $total_feedback) * 100, 1) : 0;

// 5. Daftar Feedback Terbaru
$stmt_recent_fb = $pdo->query("
    SELECT f.rating, f.comment, f.created_at, h.user_message, h.bot_answer
    FROM feedback f
    JOIN chat_history h ON f.chat_id = h.id
    ORDER BY f.id DESC
    LIMIT 10
");
$recent_feedback = $stmt_recent_fb->fetchAll();

$page_title = 'Statistik & Feedback Mahasiswa';
require_once __DIR__ . '/header.php';
?>

<section class="page-content" style="padding-top:24px;">
    <div class="container">

        <!-- HEADER BANNER -->
        <div style="margin-bottom:24px;">
            <h1 style="color:var(--brown); font-size:1.5rem; font-weight:700;">
                <i class="fas fa-chart-pie" style="color:var(--brown-warm);"></i> Statistik & Analisis Kebutuhan Informasi
            </h1>
            <p style="color:var(--text-light); font-size:0.88rem;">
                Analisis data pencarian, performa chatbot, serta feedback dari Mahasiswa TI Polinela
            </p>
        </div>

        <!-- ROW 1: PERFORMA CHATBOT & FEEDBACK -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; margin-bottom:24px;" class="admin-grid-2col">
            
            <!-- Chatbot Accuracy Rate -->
            <div class="card" style="margin-bottom:0;">
                <h3 style="color:var(--brown); font-size:1.05rem; font-weight:700; margin-bottom:16px;">
                    <i class="fas fa-robot" style="color:var(--brown-warm);"></i> Performa Chatbot JTI
                </h3>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; text-align:center;">
                    <div style="padding:16px; background:var(--beige); border-radius:var(--radius-sm);">
                        <span style="font-size:1.8rem; font-weight:700; color:#16a34a; display:block;"><?php echo $success_rate; ?>%</span>
                        <span style="font-size:0.8rem; color:var(--text-light);">Tingkat Keberhasilan Jawaban</span>
                    </div>
                    <div style="padding:16px; background:var(--beige); border-radius:var(--radius-sm);">
                        <span style="font-size:1.8rem; font-weight:700; color:var(--brown); display:block;"><?php echo $avg_confidence; ?>%</span>
                        <span style="font-size:0.8rem; color:var(--text-light);">Rata-rata Skor Confidence</span>
                    </div>
                </div>

                <div style="margin-top:16px; font-size:0.85rem; color:var(--text-light); line-height:1.6;">
                    Total Pertanyaan Masuk: <strong><?php echo $total_messages; ?></strong> pesan<br>
                    <span style="color:#16a34a;"><i class="fas fa-check"></i> Terjawab: <?php echo $answered_count; ?></span> | 
                    <span style="color:#dc2626;"><i class="fas fa-times"></i> Belum Terjawab: <?php echo $unanswered_count; ?></span>
                </div>
            </div>

            <!-- Feedback Summary Card -->
            <div class="card" style="margin-bottom:0;">
                <h3 style="color:var(--brown); font-size:1.05rem; font-weight:700; margin-bottom:16px;">
                    <i class="fas fa-smile" style="color:var(--brown-warm);"></i> Kepuasan Mahasiswa (Feedback)
                </h3>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; text-align:center;">
                    <div style="padding:16px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--radius-sm);">
                        <span style="font-size:1.8rem; font-weight:700; color:#15803d; display:block;"><?php echo $total_likes; ?></span>
                        <span style="font-size:0.8rem; color:#166534;"><i class="fas fa-thumbs-up"></i> Menyukai Jawaban</span>
                    </div>
                    <div style="padding:16px; background:#fef2f2; border:1px solid #fecaca; border-radius:var(--radius-sm);">
                        <span style="font-size:1.8rem; font-weight:700; color:#b91c1c; display:block;"><?php echo $total_dislikes; ?></span>
                        <span style="font-size:0.8rem; color:#991b1b;"><i class="fas fa-thumbs-down"></i> Kurang Membantu</span>
                    </div>
                </div>

                <div style="margin-top:16px; font-size:0.85rem; color:var(--text-light);">
                    Tingkat Kepuasan Mahasiswa: <strong style="color:var(--brown);"><?php echo $like_rate; ?>%</strong> dari total <?php echo $total_feedback; ?> feedback.
                </div>
            </div>

        </div>

        <!-- ROW 2: TOP KNOWLEDGE & KATEGORI STATS -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; margin-bottom:24px;" class="admin-grid-2col">
            
            <!-- Top Knowledge -->
            <div class="card" style="margin-bottom:0;">
                <h3 style="color:var(--brown); font-size:1.05rem; font-weight:700; margin-bottom:16px;">
                    <i class="fas fa-fire" style="color:#e65100;"></i> Top 5 Informasi Paling Sering Dibaca
                </h3>
                
                <?php if (count($top_read) > 0): ?>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <?php $rank = 1; foreach ($top_read as $tr): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:var(--cream); border-radius:var(--radius-sm); border:1px solid var(--beige-dark);">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="width:24px; height:24px; border-radius:50%; background:var(--brown-warm); color:#fff; font-size:0.75rem; font-weight:700; display:flex; align-items:center; justify-content:center;">
                                        <?php echo $rank++; ?>
                                    </span>
                                    <div>
                                        <strong style="font-size:0.88rem; color:var(--brown); display:block;"><?php echo htmlspecialchars($tr['question']); ?></strong>
                                        <span style="font-size:0.75rem; color:var(--text-light);"><?php echo htmlspecialchars($tr['category_name']); ?></span>
                                    </div>
                                </div>
                                <span style="font-size:0.82rem; font-weight:700; color:var(--brown-warm);">
                                    <?php echo $tr['view_count']; ?>x dibaca
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--text-light); text-align:center; padding:20px;">Belum ada data tayangan artikel.</p>
                <?php endif; ?>
            </div>

            <!-- Kategori Stats -->
            <div class="card" style="margin-bottom:0;">
                <h3 style="color:var(--brown); font-size:1.05rem; font-weight:700; margin-bottom:16px;">
                    <i class="fas fa-th-large" style="color:var(--brown-warm);"></i> Popularitas Kategori Akademik
                </h3>

                <div style="display:flex; flex-direction:column; gap:8px; max-height:280px; overflow-y:auto; padding-right:6px;">
                    <?php foreach ($cat_stats as $cs): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:var(--beige); border-radius:6px;">
                            <span style="font-size:0.85rem; font-weight:600; color:var(--brown);">
                                <i class="fas <?php echo htmlspecialchars($cs['icon']); ?>" style="margin-right:6px; color:var(--brown-warm);"></i>
                                <?php echo htmlspecialchars($cs['name']); ?>
                            </span>
                            <div style="font-size:0.78rem; color:var(--text-light);">
                                <strong style="color:var(--brown);"><?php echo (int)$cs['total_views']; ?></strong> views 
                                (<?php echo $cs['total_knowledge']; ?> artikel)
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- ROW 3: RIWAYAT FEEDBACK DARI MAHASISWA -->
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="padding:16px 20px; background:var(--beige); border-bottom:1px solid var(--beige-dark);">
                <h3 style="color:var(--brown); font-size:1rem; font-weight:700; margin:0;">
                    <i class="fas fa-comments"></i> Riwayat Feedback Mahasiswa Terbaru
                </h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:90px; text-align:center;">Rating</th>
                        <th>Pertanyaan Mahasiswa</th>
                        <th>Jawaban Chatbot</th>
                        <th style="width:140px;">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent_feedback) > 0): ?>
                        <?php foreach ($recent_feedback as $rf): ?>
                            <tr>
                                <td style="text-align:center;">
                                    <?php if ($rf['rating'] === 'like'): ?>
                                        <span class="status-pill status-success"><i class="fas fa-thumbs-up"></i> Suka</span>
                                    <?php else: ?>
                                        <span class="status-pill status-danger"><i class="fas fa-thumbs-down"></i> Kurang</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:0.88rem; font-weight:600; color:var(--brown);">
                                    "<?php echo htmlspecialchars($rf['user_message']); ?>"
                                </td>
                                <td style="font-size:0.83rem; color:var(--text-light);">
                                    <?php echo htmlspecialchars(mb_strimwidth($rf['bot_answer'], 0, 90, "...")); ?>
                                </td>
                                <td style="font-size:0.82rem; color:var(--text-light);">
                                    <?php echo date('d M Y, H:i', strtotime($rf['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding:30px; color:var(--text-light);">
                                Belum ada feedback tersimpan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
