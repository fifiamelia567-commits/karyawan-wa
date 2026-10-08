<?php
require_once __DIR__ . '/../config/helpers.php';

$error = '';
$success = '';

if (is_post()) {
    $nik = trim($_POST['nik'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $no_hp = normalize_phone($_POST['no_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if ($nik === '' || $nama === '' || $jabatan === '' || $no_hp === '' || $email === '' || $password === '' || $konfirmasi === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (!preg_match('/^(?:\+62|62|0)8[0-9]{7,13}$/', str_replace([' ', '-'], '', $_POST['no_hp'] ?? ''))) {
        $error = 'Nomor WhatsApp harus berupa nomor Indonesia yang valid.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Password dan konfirmasi password tidak sama.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $stmt = $koneksi->prepare("SELECT id FROM karyawan WHERE nik = ? OR email = ? LIMIT 1");
        $stmt->bind_param('ss', $nik, $email);
        $stmt->execute();
        $cek = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($cek) {
            $error = 'NIK atau email sudah terdaftar.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare("INSERT INTO karyawan (nik,nama,jabatan,no_hp,email,password,created_at,updated_at) VALUES (?,?,?,?,?,?,NOW(),NOW())");
            $stmt->bind_param('ssssss', $nik, $nama, $jabatan, $no_hp, $email, $hash);

            if ($stmt->execute()) {
                $stmt->close();

                $pesanKaryawan = "REGISTRASI BERHASIL\n\n"
                    . "Halo {$nama},\n\n"
                    . "Data Anda telah berhasil terdaftar sebagai karyawan.\n\n"
                    . "NIK      : {$nik}\n"
                    . "Jabatan  : {$jabatan}\n\n"
                    . "Silakan login menggunakan akun yang telah dibuat.\n\n"
                    . "Terima kasih.";

                $pesanAdmin = "NOTIFIKASI KARYAWAN BARU\n\n"
                    . "Telah terdaftar karyawan baru:\n\n"
                    . "Nama     : {$nama}\n"
                    . "NIK      : {$nik}\n"
                    . "Jabatan  : {$jabatan}\n"
                    . "No. HP   : {$no_hp}\n\n"
                    . "Silakan cek sistem untuk melihat data lengkap.";

                kirim_dan_log_wa($koneksi, $no_hp, $pesanKaryawan, 'register_karyawan');
                kirim_dan_log_wa($koneksi, FONNTE_ADMIN_NUMBER, $pesanAdmin, 'register_admin');

                $success = 'Registrasi berhasil. Notifikasi WhatsApp diproses.';
            } else {
                $error = 'Registrasi gagal: ' . $koneksi->error;
                $stmt->close();
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register Karyawan - Sistem Karyawan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body class="auth-page">

<div class="auth-wrapper" style="max-width: 580px;">
    <div class="auth-brand">
        <div class="logo-icon">SK</div>
        <h1>Sistem Karyawan</h1>
        <p>Portal Absensi & Manajemen Karyawan</p>
    </div>

    <div class="auth-card">
        <div class="auth-card-head">
            <h2>Pendaftaran Karyawan</h2>
            <p>Isi data diri Anda dengan lengkap dan benar</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-grid-2">
                <div class="form-group">
                    <label for="nik">NIK</label>
                    <input type="text" id="nik" name="nik" class="input-field" placeholder="3515xxxxxxxx" maxlength="30" required>
                </div>

                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="input-field" placeholder="Nama Lengkap" maxlength="100" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="jabatan">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" class="input-field" placeholder="Contoh: Staff IT" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="no_hp">Nomor WhatsApp</label>
                    <input type="text" id="no_hp" name="no_hp" class="input-field" placeholder="08xxxxxxxxxx" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="input-field" placeholder="nama@email.com" maxlength="100" required>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="pw-wrapper">
                        <input type="password" id="password" name="password" class="input-field" placeholder="Min. 6 Karakter" minlength="6" required>
                        <button type="button" class="pw-toggle-btn" onclick="togglePassword('password', this)">Lihat</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="konfirmasi_password">Konfirmasi Password</label>
                    <div class="pw-wrapper">
                        <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="input-field" placeholder="Ulangi Password" minlength="6" required>
                        <button type="button" class="pw-toggle-btn" onclick="togglePassword('konfirmasi_password', this)">Lihat</button>
                    </div>
                </div>
            </div>

            <button class="btn-submit" type="submit">Daftar Sekarang</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="<?= BASE_URL ?>/auth/login.php">Login di sini</a>
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