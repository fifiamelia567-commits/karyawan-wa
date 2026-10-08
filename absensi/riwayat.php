<?php
require_once __DIR__ . '/../config/helpers.php';
require_login();

$id_karyawan = (int)$_SESSION['user_id'];
$bulan = $_GET['bulan'] ?? date('Y-m');

// Query mengambil data absensi karyawan berdasarkan bulan yang dipilih
$query = "SELECT tanggal, jam_masuk, jam_pulang, status, keterangan 
          FROM absensi 
          WHERE id_karyawan = ? AND DATE_FORMAT(tanggal, '%Y-%m') = ? 
          ORDER BY tanggal DESC";

$stmt = $koneksi->prepare($query);
$stmt->bind_param('is', $id_karyawan, $bulan);
$stmt->execute();
$data = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header flex-between">
    <div>
        <h1 class="page-title">Riwayat Absensi Saya</h1>
        <p class="page-subtitle">Rekap data presensi bulan <strong><?= e(date('F Y', strtotime($bulan . '-01'))) ?></strong></p>
    </div>
    <a class="btn btn-outline" href="<?= BASE_URL ?>/absensi/index.php">
        &larr; Absensi Hari Ini
    </a>
</div>

<div class="panel">
    <form class="search-box" method="get">
        <div class="form-group" style="margin-bottom: 0; flex: 1;">
            <input type="month" name="bulan" class="input-field" value="<?= e($bulan) ?>">
        </div>
        <button class="btn btn-burgundy-main" type="submit">Tampilkan</button>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th width="120" class="text-center">Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($r = $data->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= e(format_tanggal($r['tanggal'])) ?></strong></td>
                    <td><?= e($r['jam_masuk'] ?: '-') ?></td>
                    <td><?= e($r['jam_pulang'] ?: '-') ?></td>
                    <td class="text-center">
                        <?php 
                            $statusClass = 'badge-secondary';
                            $statusLower = strtolower($r['status'] ?? '');
                            if ($statusLower === 'hadir') $statusClass = 'badge-success';
                            if ($statusLower === 'terlambat') $statusClass = 'badge-warning';
                        ?>
                        <span class="badge-status <?= $statusClass ?>"><?= e(ucfirst($r['status'])) ?></span>
                    </td>
                    <td><?= e($r['keterangan'] ?: '-') ?></td>
                </tr>
                <?php endwhile; ?>

                <?php if ($data->num_rows === 0): ?>
                <tr>
                    <td colspan="6" class="text-center empty-state">Belum ada riwayat absensi pada bulan ini.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$stmt->close(); 
include __DIR__ . '/../includes/footer.php'; 
?>