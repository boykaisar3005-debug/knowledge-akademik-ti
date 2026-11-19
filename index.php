<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
$stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY id ASC");
$categories = $stmt->fetchAll();
$stmt_total = $pdo->query("SELECT COUNT(*) as total FROM knowledge WHERE is_active = 1");
$total_knowledge = $stmt_total->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Knowledge Base Akademik Jurusan Teknologi Informasi Politeknik Negeri Lampung.">
    <title>Knowledge Base Akademik | JTI Polinela</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="animate-fade-in">
<nav class="navbar">
    <div class="container">
        <a href="index.php" class="logo"><span class="logo-mark"><i class="fas fa-graduation-cap"></i></span><span>KB Akademik <small>JTI Polinela</small></span></a>
        <button class="nav-toggle" aria-label="Buka navigasi"><i class="fas fa-bars"></i></button>
        <ul class="nav-links">
            <li><a href="index.php" class="active"><i class="fas fa-home"></i> Beranda</a></li>
            <li><a href="pages/categories.php"><i class="fas fa-layer-group"></i> Kategori</a></li>
            <li><a href="pages/search.php"><i class="fas fa-search"></i> Cari</a></li>
            <li><a href="chatbot/index.php"><i class="fas fa-robot"></i> Chatbot</a></li>
            <li><button class="theme-toggle btn-icon" type="button" aria-label="Ganti tema"><i class="fas fa-moon"></i></button></li>
            <li><a href="admin/login.php" class="btn-admin"><i class="fas fa-lock"></i> Admin</a></li>
        </ul>
    </div>
</nav>
<main>
<section class="hero">
    <div class="hero-orb hero-orb-one"></div><div class="hero-orb hero-orb-two"></div>
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="hero-badge"><i class="fas fa-sparkles"></i> Knowledge Base Akademik JTI</span>
            <h1>Temukan informasi akademik <span>tanpa ribet.</span></h1>
            <p class="hero-lead">Pusat informasi terstruktur untuk mahasiswa Teknologi Informasi Politeknik Negeri Lampung — dari KRS hingga tugas akhir.</p>
            <form action="pages/search.php" method="GET" class="hero-search">
                <i class="fas fa-search"></i><input type="text" name="q" placeholder="Cari KRS, PKL, jadwal, tugas akhir..." required autocomplete="off"><button type="submit">Cari informasi <i class="fas fa-arrow-right"></i></button>
            </form>
            <div class="hero-actions"><a href="chatbot/index.php" class="btn-primary btn-lg"><i class="fas fa-robot"></i> Mulai bertanya</a><a href="pages/categories.php" class="btn-secondary btn-lg"><i class="fas fa-compass"></i> Jelajahi knowledge</a></div>
            <div class="quick-stats"><div class="stat-item"><span class="stat-number"><?php echo $total_knowledge; ?><small>+</small></span><span class="stat-label">Informasi akademik</span></div><div class="stat-item"><span class="stat-number"><?php echo count($categories); ?></span><span class="stat-label">Kategori terstruktur</span></div><div class="stat-item"><span class="stat-number"><i class="fas fa-bolt"></i></span><span class="stat-label">Asisten siap 24/7</span></div></div>
        </div>
        <div class="hero-visual" aria-label="Pratinjau asisten akademik">
            <div class="visual-grid"></div><div class="assistant-window">
                <div class="assistant-top"><span class="assistant-avatar"><i class="fas fa-robot"></i></span><div><strong>KB Assistant</strong><small><span class="online-dot"></span> Online sekarang</small></div><i class="fas fa-ellipsis-h visual-more"></i></div>
                <div class="assistant-body"><div class="mini-message bot">Halo! Saya siap membantu informasi akademik JTI Polinela.</div><div class="mini-message user">Bagaimana cara mengisi KRS?</div><div class="mini-message bot">Saya menemukan panduan KRS untukmu. <i class="fas fa-check-circle"></i></div></div>
                <div class="assistant-input"><span>Tanyakan sesuatu...</span><i class="fas fa-paper-plane"></i></div>
            </div><div class="floating-card floating-card-top"><i class="fas fa-check-circle"></i><span><strong>Jawaban terverifikasi</strong><small>Informasi terstruktur</small></span></div><div class="floating-card floating-card-bottom"><i class="fas fa-layer-group"></i><span><strong><?php echo count($categories); ?> kategori</strong><small>Siap dijelajahi</small></span></div>
        </div>
    </div>
</section>
<section class="feature-section"><div class="container"><div class="section-heading"><span class="eyebrow">Dibuat untuk kebutuhanmu</span><h2>Satu ruang untuk semua informasi akademik</h2><p>Temukan jawaban lebih cepat dengan pengalaman yang sederhana dan terarah.</p></div><div class="feature-grid"><div class="feature-card"><div class="feature-icon icon-indigo"><i class="fas fa-search"></i></div><h3>Smart Search</h3><p>Cari informasi menggunakan kata kunci yang familiar dan temukan jawaban relevan dengan cepat.</p><a href="pages/search.php">Mulai mencari <i class="fas fa-arrow-right"></i></a></div><div class="feature-card featured"><div class="feature-icon icon-warm"><i class="fas fa-robot"></i></div><h3>Academic Assistant</h3><p>Ajukan pertanyaan dengan bahasa sehari-hari dan dapatkan bantuan dari chatbot akademik.</p><a href="chatbot/index.php">Tanya sekarang <i class="fas fa-arrow-right"></i></a></div><div class="feature-card"><div class="feature-icon icon-green"><i class="fas fa-book-open"></i></div><h3>Knowledge terstruktur</h3><p>Panduan akademik dikelompokkan berdasarkan kategori agar lebih mudah dipelajari.</p><a href="pages/categories.php">Lihat kategori <i class="fas fa-arrow-right"></i></a></div></div></div></section>
<section class="categories-section"><div class="container"><div class="section-heading section-heading-row"><div><span class="eyebrow">Jelajahi topik</span><h2>Kategori populer</h2><p>Mulai dari topik yang paling sering dicari mahasiswa.</p></div><a href="pages/categories.php" class="text-link">Lihat semua <i class="fas fa-arrow-right"></i></a></div><div class="category-grid"><?php if ($categories): foreach ($categories as $cat): ?><a href="knowledge/list.php?category=<?php echo urlencode($cat['slug']); ?>" class="category-card animate-fade-in-up"><div class="card-icon"><i class="fas <?php echo htmlspecialchars($cat['icon']); ?>"></i></div><div class="category-card-copy"><h3><?php echo htmlspecialchars($cat['name']); ?></h3><p><?php echo htmlspecialchars($cat['description']); ?></p></div><span class="category-arrow"><i class="fas fa-arrow-up-right-from-square"></i></span></a><?php endforeach; else: ?><div class="empty-state"><i class="fas fa-folder-open"></i><h3>Belum ada kategori</h3><p>Informasi akan segera tersedia.</p></div><?php endif; ?></div></div></section>
<section class="cta-section"><div class="container cta-inner"><div><span class="eyebrow eyebrow-light">Butuh jawaban cepat?</span><h2>Tanyakan pada Academic Assistant.</h2><p>Ajukan pertanyaan seputar KRS, KHS, PKL, perkuliahan, atau tugas akhir.</p></div><a href="chatbot/index.php" class="btn-cta">Buka chatbot <i class="fas fa-arrow-right"></i></a></div></section>
</main>
<footer class="footer"><div class="container footer-inner"><div><a href="index.php" class="logo footer-logo"><span class="logo-mark"><i class="fas fa-graduation-cap"></i></span><span>KB Akademik <small>JTI Polinela</small></span></a><p>Pusat informasi akademik Jurusan Teknologi Informasi.</p></div><div class="footer-meta"><p>&copy; <?php echo date('Y'); ?> Politeknik Negeri Lampung</p><p>Dibuat untuk pengalaman akademik yang lebih mudah.</p></div></div></footer>
<script src="assets/js/main.js"></script>
</body>
</html>