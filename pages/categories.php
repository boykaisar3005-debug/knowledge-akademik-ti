<?php
/**
 * ============================================================
 * FILE     : pages/categories.php
 * FUNGSI   : Halaman daftar kategori (Cozy Beige Academic)
 * PROJECT  : Knowledge Base Akademik TI - Polinela
 * ============================================================
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Ambil semua kategori aktif beserta jumlah knowledge per kategori
$stmt = $pdo->query("
    SELECT c.*, COUNT(k.id) as total_knowledge
    FROM categories c
    LEFT JOIN knowledge k ON c.id = k.category_id AND k.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Informasi - KB Akademik TI</title>
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
                <li><a href="categories.php" class="active"><i class="fas fa-folder"></i> Kategori</a></li>
                <li><a href="search.php"><i class="fas fa-search"></i> Cari</a></li>
                <li><a href="../chatbot/index.php"><i class="fas fa-robot"></i> Chatbot</a></li>
                <li><button class="theme-toggle btn-icon" type="button" aria-label="Ganti tema"><i class="fas fa-moon"></i></button></li>
                <li><a href="../admin/login.php" class="btn-admin"><i class="fas fa-lock"></i> Admin</a></li>
            </ul>
        </div>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-folder-open"></i> Kategori Informasi Akademik</h1>
            <p>Pilih kategori di bawah untuk menjelajahi informasi yang kamu butuhkan</p>
        </div>
    </div>

    <!-- PAGE CONTENT -->
    <section class="page-content">
        <div class="container">
            <div class="category-grid">
                <?php 
                $stagger = 1;
                foreach ($categories as $cat): 
                    $delay_class = "stagger-" . min($stagger, 6);
                    $stagger++;
                ?>
                    <a href="../knowledge/list.php?category=<?php echo urlencode($cat['slug']); ?>" 
                       class="category-card animate-fade-in-up <?php echo $delay_class; ?>">
                        <div class="card-icon">
                            <i class="fas <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                        <p><?php echo htmlspecialchars($cat['description']); ?></p>
                        <span class="category-count">
                            <i class="fas fa-file-alt"></i> <?php echo $cat['total_knowledge']; ?> Info
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
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
