<?php
/**
 * ============================================================
 * FILE     : pages/search.php
 * FUNGSI   : Halaman pencarian informasi (Cozy Beige Academic)
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * ============================================================
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$query = '';
$results = [];

if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $query = clean_input($_GET['q']);
    
    // Pencarian di tabel knowledge berdasarkan pertanyaan dan jawaban
    $stmt = $pdo->prepare("
        SELECT k.*, c.name as category_name, c.slug as category_slug
        FROM knowledge k
        JOIN categories c ON k.category_id = c.id
        WHERE k.is_active = 1
          AND (k.question LIKE :q1 OR k.answer LIKE :q2)
        ORDER BY k.question ASC
        LIMIT 20
    ");
    $search_term = '%' . $query . '%';
    $stmt->execute(['q1' => $search_term, 'q2' => $search_term]);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian - KB Akademik TI</title>
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
                <li><a href="categories.php"><i class="fas fa-folder"></i> Kategori</a></li>
                <li><a href="search.php" class="active"><i class="fas fa-search"></i> Cari</a></li>
                <li><a href="../chatbot/index.php"><i class="fas fa-robot"></i> Chatbot</a></li>
                <li><button class="theme-toggle btn-icon" type="button" aria-label="Ganti tema"><i class="fas fa-moon"></i></button></li>
                <li><a href="../admin/login.php" class="btn-admin"><i class="fas fa-lock"></i> Admin</a></li>
            </ul>
        </div>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-search"></i> Pencarian Informasi</h1>
            <p>Temukan jawaban untuk pertanyaan akademikmu di sini</p>
        </div>
    </div>

    <!-- PAGE CONTENT -->
    <section class="page-content">
        <div class="container">
            
            <!-- Form Pencarian -->
            <div class="search-card">
                <form action="search.php" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Ketik kata kunci pencarian..." 
                           value="<?php echo htmlspecialchars($query); ?>" required autocomplete="off">
                    <button type="submit"><i class="fas fa-search"></i> Cari</button>
                </form>
            </div>

            <!-- Hasil Pencarian -->
            <?php if ($query !== ''): ?>
                <p class="search-results-heading">
                    Hasil pencarian untuk "<em><?php echo htmlspecialchars($query); ?></em>" — 
                    <strong><?php echo count($results); ?></strong> informasi ditemukan
                </p>

                <?php if (count($results) > 0): ?>
                    <?php 
                    $stagger = 1;
                    foreach ($results as $item): 
                        $delay_class = "stagger-" . min($stagger, 6);
                        $stagger++;
                    ?>
                        <div class="search-result-card animate-fade-in-up <?php echo $delay_class; ?>">
                            <a href="../knowledge/list.php?category=<?php echo urlencode($item['category_slug']); ?>" 
                               class="category-badge">
                                <i class="fas fa-folder"></i> <?php echo htmlspecialchars($item['category_name']); ?>
                            </a>
                            <h3><i class="fas fa-question-circle"></i> <?php echo htmlspecialchars($item['question']); ?></h3>
                            <div class="answer-text"><?php echo nl2br(htmlspecialchars($item['answer'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card empty-state">
                        <i class="fas fa-search"></i>
                        <h3>Informasi Tidak Ditemukan</h3>
                        <p>Coba gunakan kata kunci yang lebih umum, atau tanyakan langsung ke 
                           <a href="../chatbot/index.php">Chatbot Akademik</a></p>
                    </div>
                <?php endif; ?>
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
