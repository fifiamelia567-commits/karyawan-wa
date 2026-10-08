<?php
require_once __DIR__ . '/../config/helpers.php';
require_admin();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error = '';

if (is_post()) {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die('CSRF token tidak valid.');
    }

    $nik      = trim($_POST['nik'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $jabatan  = trim($_POST['jabatan'] ?? '');
    $no_hp    = normalize_phone($_POST['no_hp'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nik === '' || $nama === '' || $jabatan === '' || $no_hp === '' || $email === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $stmt = $koneksi->prepare("SELECT id FROM karyawan WHERE nik = ? OR email = ? LIMIT 1");
        $stmt->bind_param('ss', $nik, $email);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($exists) {
            $error = 'NIK atau email sudah digunakan oleh karyawan lain.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare("INSERT INTO karyawan (nik, nama, jabatan, no_hp, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param('ssssss', $nik, $nama, $jabatan, $no_hp, $email, $hash);

            if ($stmt->execute()) {
                $stmt->close();

                $pesan = "NOTIFIKASI REGISTRASI\n\n"
                    . "Halo {$nama},\n\n"
                    . "Akun karyawan Anda telah berhasil dibuat oleh administrator.\n\n"
                    . "NIK: {$nik}\n"
                    . "Jabatan: {$jabatan}\n\n"
                    . "Silakan login ke sistem menggunakan email dan password Anda.";

                kirim_dan_log_wa($koneksi, $no_hp, $pesan, 'tambah_karyawan');

                flash('success', 'Karyawan berhasil ditambahkan dan notifikasi WhatsApp diproses.');
                redirect(BASE_URL . '/karyawan/index.php');
            }

            $error = 'Gagal menambah data karyawan.';
            $stmt->close();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Karyawan</h1>
        <p class="page-subtitle">Daftarkan karyawan baru ke dalam sistem</p>
    </div>
</div>

<div class="panel" style="max-width: 680px;">
    <div class="panel-head">
        <h2>Formulir Registrasi Karyawan</h2>
        <p style="color: var(--text-secondary); font-size: 13.5px;">Isi kelengkapan data karyawan di bawah ini.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

        <div class="form-grid-2">
            <div class="form-group">
                <label for="nik">NIK</label>
                <input type="text" id="nik" name="nik" class="input-field" value="<?= e($_POST['nik'] ?? '') ?>" placeholder="Contoh: 350200100" maxlength="30" required>
            </div>

            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="input-field" value="<?= e($_POST['nama'] ?? '') ?>" placeholder="Nama lengkap karyawan" maxlength="100" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="jabatan">Jabatan</label>
                <input type="text" id="jabatan" name="jabatan" class="input-field" value="<?= e($_POST['jabatan'] ?? '') ?>" placeholder="Contoh: Staff IT / Admin" maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="no_hp">Nomor WhatsApp</label>
                <input type="text" id="no_hp" name="no_hp" class="input-field" value="<?= e($_POST['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="input-field" value="<?= e($_POST['email'] ?? '') ?>" placeholder="alamat@email.com" maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="input-field" placeholder="Minimal 6 karakter" minlength="6" required>
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button class="btn btn-burgundy-main" type="submit">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Simpan Data
            </button>
            <a class="btn btn-outline" href="<?= BASE_URL ?>/karyawan/index.php">Batal</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>