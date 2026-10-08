<?php
require_once __DIR__ . '/../config/helpers.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $koneksi->prepare("SELECT * FROM karyawan WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    flash('error', 'Data karyawan tidak ditemukan.');
    redirect(BASE_URL . '/karyawan/index.php');
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error = '';

if (is_post()) {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die('CSRF token tidak valid.');
    }

    $nik = trim($_POST['nik'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $no_hp = normalize_phone($_POST['no_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nik === '' || $nama === '' || $jabatan === '' || $no_hp === '' || $email === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $stmt = $koneksi->prepare("SELECT id FROM karyawan WHERE (nik = ? OR email = ?) AND id <> ? LIMIT 1");
        $stmt->bind_param('ssi', $nik, $email, $id);
        $stmt->execute();
        $dup = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($dup) {
            $error = 'NIK atau email sudah digunakan oleh karyawan lain.';
        } else {
            $oldPhone = $row['no_hp'];
            $stmt = $koneksi->prepare("UPDATE karyawan SET nik = ?, nama = ?, jabatan = ?, no_hp = ?, email = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param('sssssi', $nik, $nama, $jabatan, $no_hp, $email, $id);

            if ($stmt->execute()) {
                $stmt->close();

                $pesan = "PEMBARUAN DATA\n\n"
                    . "Halo {$nama},\n\n"
                    . "Data karyawan Anda telah diperbarui oleh administrator.\n\n"
                    . "Data terbaru:\n"
                    . "Jabatan : {$jabatan}\n"
                    . "No. HP  : {$no_hp}\n"
                    . "Email   : {$email}\n\n"
                    . "Jika perubahan ini tidak Anda lakukan, silakan hubungi administrator.";

                $target = $no_hp ?: $oldPhone;
                kirim_dan_log_wa($koneksi, $target, $pesan, 'edit_karyawan');

                flash('success', 'Data karyawan berhasil diperbarui dan notifikasi WhatsApp diproses.');
                redirect(BASE_URL . '/karyawan/index.php');
            }

            $error = 'Gagal memperbarui data.';
            $stmt->close();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Data Karyawan</h1>
        <p class="page-subtitle">Ubah informasi data karyawan <strong><?= e($row['nama']) ?></strong></p>
    </div>
</div>

<div class="panel" style="max-width: 680px;">
    <div class="panel-head">
        <h2>Formulir Perubahan Data</h2>
        <p style="color: var(--text-secondary); font-size: 13.5px;">Pastikan data yang dimasukkan sudah benar sebelum menyimpan.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

        <div class="form-grid-2">
            <div class="form-group">
                <label for="nik">NIK</label>
                <input type="text" id="nik" name="nik" class="input-field" value="<?= e($_POST['nik'] ?? $row['nik']) ?>" maxlength="30" required>
            </div>

            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="input-field" value="<?= e($_POST['nama'] ?? $row['nama']) ?>" maxlength="100" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="jabatan">Jabatan</label>
                <input type="text" id="jabatan" name="jabatan" class="input-field" value="<?= e($_POST['jabatan'] ?? $row['jabatan']) ?>" maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="no_hp">Nomor WhatsApp</label>
                <input type="text" id="no_hp" name="no_hp" class="input-field" value="<?= e($_POST['no_hp'] ?? $row['no_hp']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="input-field" value="<?= e($_POST['email'] ?? $row['email']) ?>" maxlength="100" required>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button class="btn btn-burgundy-main" type="submit">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Simpan Perubahan
            </button>
            <a class="btn btn-outline" href="<?= BASE_URL ?>/karyawan/index.php">Batal</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>