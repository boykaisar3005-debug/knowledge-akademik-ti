<?php
/**
 * ============================================================
 * FILE     : knowledge/list.php
 * FUNGSI   : Menampilkan daftar knowledge per kategori (Cozy Beige Theme)
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * ============================================================
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$category_slug = isset($_GET['category']) ? clean_input($_GET['category']) : '';
$category = null;
$knowledges = [];

if ($category_slug !== '') {
    // Ambil data kategori
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = :slug AND is_active = 1");
    $stmt->execute(['slug' => $category_slug]);
    $category = $stmt->fetch();
    
    if ($category) {
        // Ambil semua knowledge dalam kategori ini
        $stmt2 = $pdo->prepare("
            SELECT * FROM knowledge 
            WHERE category_id = :cat_id AND is_active = 1
            ORDER BY question ASC
        ");
        $stmt2->execute(['cat_id' => $category['id']]);
        $knowledges = $stmt2->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category ? htmlspecialchars($category['name']) : 'Knowledge Base'; ?> - KB Akademik TI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="animate-fade-in">

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="container">
            <a href="../index.php" class="logo">
                <i class="fas fa-graduation-cap"></i> KB Akademik TI
            </a>
            <button class="nav-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-links">
                <li><a href="../index.php"><i class="fas fa-home"></i> Beranda</a></li>
                <li><a href="../pages/categories.php"><i class="fas fa-folder"></i> Kategori</a></li>
                <li><a href="../pages/search.php"><i class="fas fa-search"></i> Cari</a></li>
                <li><a href="../chatbot/index.php"><i class="fas fa-robot"></i> Chatbot</a></li>
                <li><button class="theme-toggle btn-icon" type="button" aria-label="Ganti tema"><i class="fas fa-moon"></i></button></li>
                <li><a href="../admin/login.php" class="btn-admin"><i class="fas fa-lock"></i> Admin</a></li>
            </ul>
        </div>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <?php if ($category): ?>
                <h1><i class="fas <?php echo htmlspecialchars($category['icon']); ?>"></i> <?php echo htmlspecialchars($category['name']); ?></h1>
                <p><?php echo htmlspecialchars($category['description']); ?></p>
            <?php else: ?>
                <h1><i class="fas fa-exclamation-triangle"></i> Kategori Tidak Ditemukan</h1>
                <p>Kategori yang kamu cari tidak tersedia</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- PAGE CONTENT -->
    <section class="page-content">
        <div class="container">
            
            <a href="../pages/categories.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Kategori
            </a>

            <?php if ($category && count($knowledges) > 0): ?>
                <?php 
                $stagger = 1;
                foreach ($knowledges as $item): 
                    $delay_class = "stagger-" . min($stagger, 6);
                    $stagger++;
                ?>
                    <div class="knowledge-card animate-fade-in-up <?php echo $delay_class; ?>">
                        <h3><i class="fas fa-question-circle"></i> <?php echo htmlspecialchars($item['question']); ?></h3>
                        <div class="answer"><?php echo nl2br(htmlspecialchars($item['answer'])); ?></div>
                        <div class="meta">
                            <i class="fas fa-eye"></i> Dilihat <?php echo $item['view_count']; ?> kali
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif ($category): ?>
                <div class="card empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>Belum Ada Informasi</h3>
                    <p>Kategori ini belum memiliki informasi. Silakan cek kembali nanti atau gunakan chatbot.</p>
                </div>
            <?php else: ?>
                <div class="card empty-state">
                    <i class="fas fa-exclamation-triangle" style="color:var(--brown-warm);"></i>
                    <h3>Kategori Tidak Ditemukan</h3>
                    <p><a href="../pages/categories.php">Lihat semua kategori yang tersedia</a></p>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Knowledge Base Akademik — Jurusan TI Polinela</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
