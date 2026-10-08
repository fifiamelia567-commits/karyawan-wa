<?php
require_once __DIR__ . '/../config/helpers.php';
require_admin();

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$stmt = $koneksi->prepare("SELECT k.nama, k.nik, a.tanggal, a.jam_masuk, a.jam_pulang, a.status FROM absensi a INNER JOIN karyawan k ON k.id = a.id_karyawan WHERE a.tanggal = ? ORDER BY a.jam_masuk ASC, k.nama ASC");
$stmt->bind_param('s', $tanggal);
$stmt->execute();
$data = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header flex-between">
    <div>
        <h1 class="page-title">Laporan Absensi</h1>
        <p class="page-subtitle">Rekap data absensi karyawan tanggal <strong><?= e(format_tanggal_panjang($tanggal)) ?></strong></p>
    </div>
    <button class="btn btn-burgundy-main no-print" onclick="window.print()">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Cetak Laporan
    </button>
</div>

<div class="panel">
    <form class="search-box no-print" method="get">
        <div class="form-group" style="margin-bottom: 0; flex: 1;">
            <input type="date" name="tanggal" class="input-field" value="<?= e($tanggal) ?>">
        </div>
        <button class="btn btn-burgundy-main" type="submit">Tampilkan</button>
        <a class="btn btn-outline" href="<?= BASE_URL ?>/admin/laporan.php">Hari Ini</a>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Nama Karyawan</th>
                    <th>NIK</th>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th width="120" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($r = $data->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= e($r['nama']) ?></strong></td>
                    <td><?= e($r['nik']) ?></td>
                    <td><?= e(format_tanggal($r['tanggal'])) ?></td>
                    <td><?= e($r['jam_masuk'] ?: '-') ?></td>
                    <td><?= e($r['jam_pulang'] ?: '-') ?></td>
                    <td class="text-center">
                        <?php 
                            $statusClass = 'badge-secondary';
                            if (strtolower($r['status']) === 'hadir') $statusClass = 'badge-success';
                            if (strtolower($r['status']) === 'terlambat') $statusClass = 'badge-warning';
                        ?>
                        <span class="badge-status <?= $statusClass ?>"><?= e(ucfirst($r['status'])) ?></span>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php if ($data->num_rows === 0): ?>
                <tr>
                    <td colspan="7" class="text-center empty-state">Belum ada data absensi pada tanggal ini.</td>
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