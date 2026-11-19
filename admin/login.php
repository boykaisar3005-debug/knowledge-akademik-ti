<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/auth.php';
if (is_logged_in()) redirect('/admin/dashboard.php');
$error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $error_msg = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];
            set_flash('success', 'Selamat datang kembali, ' . htmlspecialchars($user['full_name']) . '!');
            redirect('/admin/dashboard.php');
        }
        $error_msg = 'Username atau password yang Anda masukkan salah.';
    }
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Login Admin | KB Akademik</title><link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"></head><body class="animate-fade-in"><div class="login-wrapper"><section class="login-brand"><div class="login-brand-content"><a href="../index.php" class="logo" style="color:#fff"><span class="logo-mark"><i class="fas fa-graduation-cap"></i></span><span>KB Akademik <small style="color:rgba(255,255,255,.6)">JTI Polinela</small></span></a><h1>Kelola pengetahuan akademik dengan lebih terarah.</h1><p>Panel administrasi untuk menjaga informasi akademik JTI tetap relevan, rapi, dan mudah ditemukan mahasiswa.</p><ul class="login-benefits"><li>Kelola artikel knowledge base</li><li>Pantau pertanyaan yang belum terjawab</li><li>Evaluasi performa chatbot dan feedback</li></ul></div></section><section class="login-form-side"><div class="login-card"><div class="icon-circle"><i class="fas fa-user-shield"></i></div><h2>Masuk ke panel admin</h2><p class="subtitle">Gunakan akun administrator untuk melanjutkan.</p><?php echo show_flash(); ?><?php if ($error_msg): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><span><?php echo htmlspecialchars($error_msg); ?></span></div><?php endif; ?><form action="login.php" method="POST"><div class="form-group"><label for="username">Username</label><input type="text" id="username" name="username" placeholder="Masukkan username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus autocomplete="username"></div><div class="form-group"><label for="password">Password</label><div class="password-wrap"><input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password"><button type="button" class="password-toggle" data-target="password" aria-label="Tampilkan password"><i class="fas fa-eye"></i></button></div></div><button type="submit" class="btn-primary btn-block" style="margin-top:1rem"><i class="fas fa-arrow-right"></i> Masuk ke dashboard</button></form><a href="../index.php" class="back-link" style="margin-top:1.5rem;margin-bottom:0"><i class="fas fa-arrow-left"></i> Kembali ke website</a></div></section></div><script src="../assets/js/main.js"></script></body></html>