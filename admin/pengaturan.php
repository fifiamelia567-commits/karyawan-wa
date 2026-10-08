<?php
require_once __DIR__ . '/../config/helpers.php';
require_login();

if (($_SESSION['role'] ?? '') !== 'admin') {
    redirect(BASE_URL . '/absensi/index.php');
}

// Proses Simpan Pengaturan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jam_masuk = trim($_POST['jam_masuk'] ?? '08:00');
    $jam_pulang = trim($_POST['jam_pulang'] ?? '17:00');
    $toleransi = (int)($_POST['toleransi_terlambat'] ?? 0);

    $stmt = $koneksi->prepare("UPDATE pengaturan SET jam_masuk = ?, jam_pulang = ?, toleransi_terlambat = ? WHERE id = 1");
    $stmt->bind_param('ssi', $jam_masuk, $jam_pulang, $toleransi);

    if ($stmt->execute()) {
        flash('success', 'Pengaturan jam kerja berhasil diperbarui.');
    } else {
        flash('danger', 'Gagal memperbarui pengaturan.');
    }
    $stmt->close();

    redirect(BASE_URL . '/admin/pengaturan.php');
}

// Ambil Data Pengaturan
$query = $koneksi->query("SELECT * FROM pengaturan WHERE id = 1");
$setting = $query->fetch_assoc();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">Pengaturan Jam Kerja</h1>
    <p class="page-subtitle">Atur jam masuk, jam pulang, dan toleransi keterlambatan sistem</p>
</div>

<div class="panel style-form">
    <form method="post" action="">
        <div class="form-group">
            <label class="form-label">Jam Masuk Resmi</label>
            <input type="time" name="jam_masuk" class="input-field" value="<?= e($setting['jam_masuk'] ?? '08:00') ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Jam Pulang Resmi</label>
            <input type="time" name="jam_pulang" class="input-field" value="<?= e($setting['jam_pulang'] ?? '17:00') ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Toleransi Keterlambatan (Menit)</label>
            <input type="number" name="toleransi_terlambat" class="input-field" min="0" value="<?= e($setting['toleransi_terlambat'] ?? 15) ?>" required>
            <small class="form-text">Contoh: Jika jam masuk 08:00 dan toleransi 15 menit, maka absensi hingga 08:15 dianggap <strong>Hadir</strong>, lewat dari itu dianggap <strong>Terlambat</strong>.</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-burgundy-main">Simpan Pengaturan</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>