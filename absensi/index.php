<?php
require_once __DIR__ . '/../config/helpers.php';
require_login();

$today = date('Y-m-d'); 
$id = (int)$_SESSION['user_id'];

$stmt = $koneksi->prepare("SELECT k.nik, k.nama, k.jabatan, k.no_hp, a.tanggal, a.jam_masuk, a.jam_pulang, a.status, a.keterangan FROM karyawan k LEFT JOIN absensi a ON a.id_karyawan = k.id AND a.tanggal = ? WHERE k.id = ? LIMIT 1");
$stmt->bind_param('si', $today, $id); 
$stmt->execute(); 
$data = $stmt->get_result()->fetch_assoc(); 
$stmt->close();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Absensi Karyawan</h1>
        <p class="page-subtitle">Silakan lakukan presensi masuk, pulang, atau pengajuan izin/sakit hari ini</p>
    </div>
</div>

<div class="panel" style="max-width: 600px;">
    <div class="panel-head" style="border-bottom: 1px dashed var(--cream-border); padding-bottom: 16px; margin-bottom: 20px;">
        <h2 style="font-size: 18px; color: var(--burgundy-dark);"><?= e($data['nama']) ?></h2>
        <p style="color: var(--text-secondary); font-size: 13.5px;">NIK: <strong><?= e($data['nik']) ?></strong> &bull; <?= e($data['jabatan']) ?></p>
    </div>

    <div class="form-grid-2" style="margin-bottom: 20px;">
        <div style="background-color: var(--cream-input); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--cream-border);">
            <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Tanggal Hari Ini</span>
            <strong style="font-size: 14.5px; color: var(--text-primary);"><?= e(format_tanggal_panjang($today)) ?></strong>
        </div>

        <div style="background-color: var(--cream-input); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--cream-border);">
            <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Status Presensi</span>
            <?php 
                $statusClass = 'badge-secondary';
                $statusText = $data['status'] ?: 'Belum Absen';
                $statusLower = strtolower($data['status'] ?? '');
                
                if ($statusLower === 'hadir') $statusClass = 'badge-success';
                elseif ($statusLower === 'terlambat') $statusClass = 'badge-warning';
                elseif (in_array($statusLower, ['izin', 'sakit', 'cuti'])) $statusClass = 'badge-secondary';
            ?>
            <span class="badge-status <?= $statusClass ?>"><?= e(ucfirst($statusText)) ?></span>
        </div>
    </div>

    <?php if ($data['keterangan']): ?>
        <div style="background-color: var(--cream-input); padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--cream-border); margin-bottom: 20px;">
            <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Alasan / Keterangan</span>
            <p style="margin: 0; font-size: 14px; color: var(--text-primary);"><?= e($data['keterangan']) ?></p>
        </div>
    <?php endif; ?>

    <div class="form-grid-2" style="margin-bottom: 24px;">
        <div style="background-color: #ffffff; padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--cream-border); text-align: center;">
            <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Jam Masuk</span>
            <strong style="font-size: 18px; color: var(--burgundy-main);"><?= e($data['jam_masuk'] ?: '-- : --') ?></strong>
        </div>

        <div style="background-color: #ffffff; padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--cream-border); text-align: center;">
            <span style="font-size: 12px; color: var(--text-secondary); display: block; margin-bottom: 4px;">Jam Pulang</span>
            <strong style="font-size: 18px; color: var(--burgundy-main);"><?= e($data['jam_pulang'] ?: '-- : --') ?></strong>
        </div>
    </div>

    <div class="actions" style="display: flex; flex-direction: column; gap: 12px;">
        <?php if (!$data['status']): ?>
            <div style="display: flex; gap: 12px; width: 100%;">
                <a class="btn btn-burgundy-main" href="<?= BASE_URL ?>/absensi/masuk.php" style="flex: 1; justify-content: center; padding: 12px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                    ABSEN MASUK
                </a>
                <a class="btn btn-outline" href="<?= BASE_URL ?>/absensi/izin.php" style="flex: 1; justify-content: center; padding: 12px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    AJUKAN IZIN / SAKIT
                </a>
            </div>
        <?php elseif (in_array(strtolower($data['status']), ['hadir', 'terlambat']) && !$data['jam_pulang']): ?>
            <a class="btn btn-burgundy-main" href="<?= BASE_URL ?>/absensi/pulang.php" style="width: 100%; justify-content: center; padding: 12px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                ABSEN PULANG
            </a>
        <?php else: ?>
            <div class="alert alert-success" style="margin-bottom: 0; text-align: center; font-weight: 600;">
                Status absensi Anda hari ini: <?= e(ucfirst($data['status'])) ?>.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>