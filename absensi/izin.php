<?php
require_once __DIR__ . '/../config/helpers.php';
require_login();

$id_karyawan = (int)$_SESSION['user_id'];
$today = date('Y-m-d');

// Cek apakah sudah ada absensi/izin hari ini
$stmt = $koneksi->prepare("SELECT id FROM absensi WHERE id_karyawan = ? AND tanggal = ? LIMIT 1");
$stmt->bind_param('is', $id_karyawan, $today);
$stmt->execute();
$exist = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($exist) {
    flash('error', 'Anda sudah melakukan presensi/izin untuk hari ini.');
    redirect(BASE_URL . '/absensi/index.php');
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error = '';

if (is_post()) {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die('CSRF token tidak valid.');
    }

    $status = trim($_POST['status'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (!in_array($status, ['Izin', 'Sakit', 'Cuti'])) {
        $error = 'Pilih kategori izin yang valid.';
    } elseif ($keterangan === '') {
        $error = 'Alasan / keterangan wajib diisi.';
    } else {
        $stmt = $koneksi->prepare("INSERT INTO absensi (id_karyawan, tanggal, jam_masuk, jam_pulang, status, keterangan, created_at) VALUES (?, ?, NULL, NULL, ?, ?, NOW())");
        $stmt->bind_param('isss', $id_karyawan, $today, $status, $keterangan);

        if ($stmt->execute()) {
            $stmt->close();

            // Kirim Notifikasi WhatsApp ke Karyawan
            $stmtUser = $koneksi->prepare("SELECT nama, no_hp FROM karyawan WHERE id = ? LIMIT 1");
            $stmtUser->bind_param('i', $id_karyawan);
            $stmtUser->execute();
            $user = $stmtUser->get_result()->fetch_assoc();
            $stmtUser->close();

            if ($user && $user['no_hp']) {
                $pesan = "NOTIFIKASI PENGAJUAN " . strtoupper($status) . "\n\n"
                    . "Halo {$user['nama']},\n\n"
                    . "Pengajuan {$status} Anda untuk tanggal " . format_tanggal_panjang($today) . " telah tercatat di sistem.\n\n"
                    . "Keterangan: {$keterangan}\n\n"
                    . "Semoga sehat selalu dan terima kasih.";

                kirim_dan_log_wa($koneksi, $user['no_hp'], $pesan, 'pengajuan_izin');
            }

            flash('success', "Pengajuan {$status} berhasil disimpan.");
            redirect(BASE_URL . '/absensi/index.php');
        }

        $error = 'Gagal menyimpan data pengajuan izin.';
        $stmt->close();
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Form Pengajuan Izin / Sakit</h1>
        <p class="page-subtitle">Isi keterangan jika Anda berhalangan hadir hari ini</p>
    </div>
</div>

<div class="panel" style="max-width: 600px;">
    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

        <div class="form-group">
            <label for="status">Kategori Halangan</label>
            <select id="status" name="status" class="input-field" required>
                <option value="">-- Pilih Jenis Izin --</option>
                <option value="Izin" <?= ($_POST['status'] ?? '') === 'Izin' ? 'selected' : '' ?>>Izin Ada Keperluan</option>
                <option value="Sakit" <?= ($_POST['status'] ?? '') === 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                <option value="Cuti" <?= ($_POST['status'] ?? '') === 'Cuti' ? 'selected' : '' ?>>Cuti Karyawan</option>
            </select>
        </div>

        <div class="form-group">
            <label for="keterangan">Alasan / Keterangan Lengkap</label>
            <textarea id="keterangan" name="keterangan" class="input-field textarea-field" rows="4" placeholder="Tuliskan alasan berhalangan hadir secara jelas..." required><?= e($_POST['keterangan'] ?? '') ?></textarea>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button class="btn btn-burgundy-main" type="submit">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                Kirim Pengajuan
            </button>
            <a class="btn btn-outline" href="<?= BASE_URL ?>/absensi/index.php">Batal</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>