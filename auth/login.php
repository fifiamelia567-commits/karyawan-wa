<?php
require_once __DIR__ . '/../config/helpers.php';

if (!empty($_SESSION['login'])) {
    redirect(BASE_URL . '/');
}

$error = '';

if (is_post()) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $koneksi->prepare("SELECT id, nama, email, password FROM admins WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['login'] = true;
            $_SESSION['role'] = 'admin';
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['nama'] = $admin['nama'];
            $_SESSION['email'] = $admin['email'];
            redirect(BASE_URL . '/admin/index.php');
        }

        $stmt = $koneksi->prepare("SELECT id, nama, nik, email, no_hp, password FROM karyawan WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $karyawan = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($karyawan && password_verify($password, $karyawan['password'])) {
            session_regenerate_id(true);
            $_SESSION['login'] = true;
            $_SESSION['role'] = 'karyawan';
            $_SESSION['user_id'] = $karyawan['id'];
            $_SESSION['nama'] = $karyawan['nama'];
            $_SESSION['email'] = $karyawan['email'];
            $_SESSION['nik'] = $karyawan['nik'];
            $_SESSION['no_hp'] = $karyawan['no_hp'];
            redirect(BASE_URL . '/absensi/index.php');
        }

        $error = 'Email atau password salah.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistem Karyawan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body class="auth-page">

<div class="auth-wrapper">
    <div class="auth-brand">
        <div class="logo-icon">SK</div>
        <h1>Sistem Karyawan</h1>
        <p>Portal Absensi & Manajemen Karyawan</p>
    </div>

    <div class="auth-card">
        <div class="auth-card-head">
            <h2>Selamat Datang Kembali</h2>
            <p>Silakan masuk dengan akun Anda</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="input-field" placeholder="nama@perusahaan.com" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="pw-wrapper">
                    <input type="password" id="password" name="password" class="input-field" placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="pw-toggle-btn" onclick="togglePassword('password', this)">Lihat</button>
                </div>
            </div>

            <button class="btn-submit" type="submit">Masuk</button>
        </form>

        <div class="auth-footer">
            Belum punya akun karyawan? <a href="<?= BASE_URL ?>/auth/register.php">Daftar Sekarang</a>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Sembunyi';
    } else {
        input.type = 'password';
        btn.textContent = 'Lihat';
    }
}
</script>

</body>
</html>