<?php
/**
 * ============================================================
 * FILE     : admin/knowledge_index.php
 * FUNGSI   : Daftar & Kelola Data Knowledge Base (CRUD)
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * TAHAP    : 4 - Knowledge Base Management
 * ============================================================
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$page_title = 'Kelola Knowledge Base';
require_once __DIR__ . '/header.php';

// Filter Pencarian & Kategori
$search   = clean_input($_GET['q'] ?? '');
$cat_id   = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Ambil semua kategori untuk filter dropdown
$stmt_cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt_cats->fetchAll();

// Query mengambil data knowledge beserta kategori & keywords
$query = "
    SELECT k.*, c.name as category_name,
           GROUP_CONCAT(kw.keyword SEPARATOR ', ') as keyword_list
    FROM knowledge k
    JOIN categories c ON k.category_id = c.id
    LEFT JOIN keywords kw ON k.id = kw.knowledge_id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (k.question LIKE :q1 OR k.answer LIKE :q2 OR kw.keyword LIKE :q3)";
    $params['q1'] = '%' . $search . '%';
    $params['q2'] = '%' . $search . '%';
    $params['q3'] = '%' . $search . '%';
}

if ($cat_id > 0) {
    $query .= " AND k.category_id = :cat_id";
    $params['cat_id'] = $cat_id;
}

$query .= " GROUP BY k.id ORDER BY k.id DESC";

$stmt_know = $pdo->prepare($query);
$stmt_know->execute($params);
$knowledges = $stmt_know->fetchAll();
?>

<section class="page-content" style="padding-top:24px;">
    <div class="container">

        <?php echo show_flash(); ?>

        <!-- HEADER BANNER & ACTION -->
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
            <div>
                <h1 style="color:var(--brown); font-size:1.5rem; font-weight:700;">
                    <i class="fas fa-book" style="color:var(--brown-warm);"></i> Kelola Knowledge Base
                </h1>
                <p style="color:var(--text-light); font-size:0.88rem;">
                    Tambah, ubah, dan hapus pertanyaan & jawaban akademik Polinela
                </p>
            </div>
            <div>
                <a href="knowledge_create.php" class="btn-primary">
                    <i class="fas fa-plus-circle"></i> Tambah Knowledge Baru
                </a>
            </div>
        </div>

        <!-- FILTER & PENCARIAN -->
        <div class="card" style="padding:18px; margin-bottom:24px;">
            <form action="knowledge_index.php" method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <div style="flex:1; min-width:240px;">
                    <input type="text" name="q" placeholder="Cari pertanyaan, jawaban, atau kata kunci..." 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           style="width:100%; padding:10px 14px; border:1.5px solid var(--beige-dark); border-radius:var(--radius-sm); font-size:0.9rem;">
                </div>
                <div style="width:200px;">
                    <select name="category_id" style="width:100%; padding:10px 14px; border:1.5px solid var(--beige-dark); border-radius:var(--radius-sm); font-size:0.9rem;">
                        <option value="0">-- Semua Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $cat_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="padding:10px 20px;">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <?php if (!empty($search) || $cat_id > 0): ?>
                    <a href="knowledge_index.php" class="btn-secondary" style="padding:10px 16px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- TABEL DATA KNOWLEDGE -->
        <div class="card" style="padding:0; overflow:hidden;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center;">No</th>
                        <th style="width:140px;">Kategori</th>
                        <th>Pertanyaan</th>
                        <th style="width:220px;">Kata Kunci (Keywords)</th>
                        <th style="width:80px; text-align:center;">Dilihat</th>
                        <th style="width:80px; text-align:center;">Status</th>
                        <th style="width:120px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($knowledges) > 0): ?>
                        <?php $no = 1; foreach ($knowledges as $k): ?>
                            <tr>
                                <td style="text-align:center; font-weight:600; color:var(--brown);"><?php echo $no++; ?></td>
                                <td>
                                    <span style="background:var(--beige); color:var(--brown-warm); padding:3px 10px; border-radius:12px; font-size:0.78rem; font-weight:600; display:inline-block;">
                                        <?php echo htmlspecialchars($k['category_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color:var(--brown); font-size:0.92rem; display:block; margin-bottom:4px;">
                                        <?php echo htmlspecialchars($k['question']); ?>
                                    </strong>
                                    <p style="color:var(--text-light); font-size:0.83rem; line-height:1.4;">
                                        <?php echo htmlspecialchars(mb_strimwidth($k['answer'], 0, 100, "...")); ?>
                                    </p>
                                </td>
                                <td>
                                    <span style="font-size:0.78rem; color:var(--text-light); background:#f5f5f5; padding:4px 8px; border-radius:6px; display:block; line-height:1.3;">
                                        <?php echo htmlspecialchars($k['keyword_list'] ?? 'Belum ada'); ?>
                                    </span>
                                </td>
                                <td style="text-align:center; font-size:0.85rem; color:var(--text-light);">
                                    <i class="fas fa-eye"></i> <?php echo $k['view_count']; ?>
                                </td>
                                <td style="text-align:center;">
                                    <?php if ($k['is_active']): ?>
                                        <span class="status-pill status-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="status-pill status-danger">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;">
                                    <div style="display:flex; justify-content:center; gap:6px;">
                                        <a href="knowledge_edit.php?id=<?php echo $k['id']; ?>" class="btn-secondary" 
                                           style="padding:6px 10px; font-size:0.8rem; background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe;" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="knowledge_delete.php?id=<?php echo $k['id']; ?>" class="btn-secondary" 
                                           style="padding:6px 10px; font-size:0.8rem; background:#fef2f2; color:#b91c1c; border-color:#fca5a5;" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus knowledge ini?');" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px; color:var(--text-light);">
                                <i class="fas fa-search" style="font-size:2rem; color:var(--beige-dark); margin-bottom:10px; display:block;"></i>
                                Tidak ada data Knowledge Base yang ditemukan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
