<?php
require_once __DIR__ . '/../config/helpers.php';
require_admin();

$today = date('Y-m-d');

$total = (int)$koneksi->query("SELECT COUNT(*) c FROM karyawan")->fetch_assoc()['c'];
$sudahAbsen = (int)$koneksi->query("SELECT COUNT(*) c FROM absensi WHERE tanggal='{$today}' AND jam_masuk IS NOT NULL")->fetch_assoc()['c'];
$sudahPulang = (int)$koneksi->query("SELECT COUNT(*) c FROM absensi WHERE tanggal='{$today}' AND jam_pulang IS NOT NULL")->fetch_assoc()['c'];
$belumAbsen = max(0, $total - $sudahAbsen);

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard Admin</h1>
        <p class="page-subtitle">Ringkasan absensi tanggal <strong><?= e(format_tanggal_panjang($today)) ?></strong></p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon burgundy">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <div class="stat-info">
            <span>Jumlah Karyawan</span>
            <strong><?= $total ?></strong>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <div class="stat-info">
            <span>Sudah Absen</span>
            <strong><?= $sudahAbsen ?></strong>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 16 14"></polyline></svg>
        </div>
        <div class="stat-info">
            <span>Belum Absen</span>
            <strong><?= $belumAbsen ?></strong>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        </div>
        <div class="stat-info">
            <span>Sudah Pulang</span>
            <strong><?= $sudahPulang ?></strong>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Menu Utama</h2>
        <p style="color: var(--text-secondary); font-size: 13.5px;">Akses cepat manajemen data & laporan</p>
    </div>
    <div class="quick-links">
        <a class="btn btn-burgundy-main" href="<?= BASE_URL ?>/karyawan/index.php">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
            Kelola Karyawan
        </a>
        <a class="btn btn-outline" href="<?= BASE_URL ?>/admin/laporan.php">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            Laporan Absensi
        </a>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>