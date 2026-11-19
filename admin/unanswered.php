<?php
/**
 * ============================================================
 * FILE     : admin/unanswered.php
 * FUNGSI   : Kelola Pertanyaan Belum Terjawab (Unanswered Questions)
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * TAHAP    : 5 - Manajemen Pertanyaan Belum Terjawab
 * ============================================================
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/auth.php';
require_admin();

$admin_user = get_admin_user();

// PROSES AKSI: Resolve atau Hapus
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = clean_input($_GET['action']);
    $un_id  = (int)$_GET['id'];

    if ($un_id > 0) {
        if ($action === 'resolve') {
            // Tandai sebagai resolved
            $stmt_res = $pdo->prepare("
                UPDATE unanswered_questions 
                SET is_resolved = 1, resolved_by = :admin_id, updated_at = NOW() 
                WHERE id = :id
            ");
            $stmt_res->execute(['admin_id' => $admin_user['id'], 'id' => $un_id]);
            set_flash('success', 'Pertanyaan berhasil ditandai Selesai (Resolved).');
        } elseif ($action === 'delete') {
            // Hapus record
            $stmt_del = $pdo->prepare("DELETE FROM unanswered_questions WHERE id = :id");
            $stmt_del->execute(['id' => $un_id]);
            set_flash('success', 'Pertanyaan berhasil dihapus dari daftar.');
        }
    }
    redirect('/admin/unanswered.php');
}

// Ambil semua pertanyaan yang belum resolved (is_resolved = 0)
$stmt_un = $pdo->query("
    SELECT u.*, h.created_at as asked_at, s.student_name
    FROM unanswered_questions u
    LEFT JOIN chat_history h ON u.chat_id = h.id
    LEFT JOIN chat_sessions s ON h.session_id = s.id
    WHERE u.is_resolved = 0
    ORDER BY u.frequency DESC, u.id DESC
");
$unanswered_list = $stmt_un->fetchAll();

// Ambil data pertanyaan yang sudah resolved (is_resolved = 1) untuk arsip
$stmt_res_list = $pdo->query("
    SELECT u.*, adm.full_name as admin_name
    FROM unanswered_questions u
    LEFT JOIN users adm ON u.resolved_by = adm.id
    WHERE u.is_resolved = 1
    ORDER BY u.updated_at DESC
    LIMIT 10
");
$resolved_list = $stmt_res_list->fetchAll();

$page_title = 'Pertanyaan Belum Terjawab';
require_once __DIR__ . '/header.php';
?>

<section class="page-content" style="padding-top:24px;">
    <div class="container">

        <?php echo show_flash(); ?>

        <!-- HEADER SECTION -->
        <div style="margin-bottom:24px;">
            <h1 style="color:var(--brown); font-size:1.5rem; font-weight:700;">
                <i class="fas fa-question-circle" style="color:#e74c3c;"></i> Pertanyaan Belum Terjawab
            </h1>
            <p style="color:var(--text-light); font-size:0.88rem;">
                Daftar pertanyaan mahasiswa yang belum berhasil dijawab oleh Chatbot JTI Polinela.
                Tambahkan ke Knowledge Base agar chatbot dapat menjawabnya di masa depan.
            </p>
        </div>

        <!-- TABEL UNANSWERED QUESTIONS -->
        <div class="card" style="padding:0; overflow:hidden; margin-bottom:32px;">
            <div style="padding:16px 20px; background:var(--beige); border-bottom:1px solid var(--beige-dark); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="color:var(--brown); font-size:1rem; font-weight:700; margin:0;">
                    <i class="fas fa-list-ol"></i> Menunggu Penanganan Admin (<?php echo count($unanswered_list); ?>)
                </h3>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center;">No</th>
                        <th>Pertanyaan Mahasiswa</th>
                        <th style="width:110px; text-align:center;">Frekuensi</th>
                        <th style="width:140px;">Tanggal Masuk</th>
                        <th style="width:240px; text-align:center;">Aksi Penanganan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($unanswered_list) > 0): ?>
                        <?php $no = 1; foreach ($unanswered_list as $un): ?>
                            <tr>
                                <td style="text-align:center; font-weight:600; color:var(--brown);"><?php echo $no++; ?></td>
                                <td>
                                    <strong style="color:#991b1b; font-size:0.95rem; display:block;">
                                        "<?php echo htmlspecialchars($un['question_text']); ?>"
                                    </strong>
                                    <?php if (!empty($un['student_name'])): ?>
                                        <span style="font-size:0.78rem; color:var(--text-light);">
                                            <i class="fas fa-user"></i> Penanya: <?php echo htmlspecialchars($un['student_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;">
                                    <span style="display:inline-block; background:#fee2e2; color:#b91c1c; font-weight:700; padding:4px 12px; border-radius:12px; font-size:0.85rem;">
                                        <?php echo $un['frequency']; ?>x ditanyakan
                                    </span>
                                </td>
                                <td style="font-size:0.83rem; color:var(--text-light);">
                                    <?php echo date('d M Y, H:i', strtotime($un['created_at'])); ?>
                                </td>
                                <td style="text-align:center;">
                                    <div style="display:flex; justify-content:center; gap:8px;">
                                        <!-- Tombol Tambah ke KB -->
                                        <a href="knowledge_create.php?q=<?php echo urlencode($un['question_text']); ?>" 
                                           class="btn-primary" style="padding:6px 12px; font-size:0.8rem; background:#16a34a; border:none;" title="Jadikan artikel KB">
                                            <i class="fas fa-plus-circle"></i> + Knowledge
                                        </a>

                                        <!-- Tombol Tandai Selesai -->
                                        <a href="unanswered.php?action=resolve&id=<?php echo $un['id']; ?>" 
                                           class="btn-secondary" style="padding:6px 12px; font-size:0.8rem; background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;" title="Tandai Selesai">
                                            <i class="fas fa-check"></i> Selesai
                                        </a>

                                        <!-- Tombol Hapus -->
                                        <a href="unanswered.php?action=delete&id=<?php echo $un['id']; ?>" 
                                           class="btn-secondary" style="padding:6px 10px; font-size:0.8rem; background:#fef2f2; color:#b91c1c; border-color:#fca5a5;" 
                                           onclick="return confirm('Hapus pertanyaan ini dari daftar?');" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:var(--text-light);">
                                <i class="fas fa-check-circle" style="font-size:2.5rem; color:#22c55e; margin-bottom:10px; display:block;"></i>
                                Selamat! Saat ini tidak ada pertanyaan mahasiswa yang belum terjawab.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ARSIP PERTANYAAN RESORTED / TERJAWAB -->
        <?php if (count($resolved_list) > 0): ?>
            <div class="card" style="padding:0; overflow:hidden;">
                <div style="padding:14px 20px; background:var(--cream); border-bottom:1px solid var(--beige-dark);">
                    <h3 style="color:var(--brown); font-size:0.95rem; font-weight:700; margin:0;">
                        <i class="fas fa-archive"></i> Arsip Pertanyaan Yang Sudah Ditangani (Resolved)
                    </h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Pertanyaan</th>
                            <th style="width:160px;">Ditangani Oleh</th>
                            <th style="width:160px;">Waktu Penanganan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resolved_list as $res): ?>
                            <tr>
                                <td style="color:var(--text); font-size:0.88rem;">
                                    "<?php echo htmlspecialchars($res['question_text']); ?>"
                                </td>
                                <td style="font-size:0.83rem; color:var(--brown-warm); font-weight:600;">
                                    <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($res['admin_name'] ?? 'Admin'); ?>
                                </td>
                                <td style="font-size:0.82rem; color:var(--text-light);">
                                    <?php echo date('d M Y, H:i', strtotime($res['updated_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
