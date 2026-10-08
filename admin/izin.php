<?php
require_once __DIR__ . '/../config/helpers.php';
require_admin();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Proses Persetujuan / Penolakan
if (is_post()) {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die('CSRF token tidak valid.');
    }

    $id_absensi = (int)($_POST['id_absensi'] ?? 0);
    $aksi = $_POST['aksi'] ?? ''; // 'setujui' atau 'tolak'

    if ($id_absensi > 0 && in_array($aksi, ['setujui', 'tolak'])) {
        // Ambil data absensi & karyawan
        $stmt = $koneksi->prepare("SELECT a.id, a.tanggal, a.status, a.keterangan, k.nama, k.no_hp FROM absensi a JOIN karyawan k ON k.id = a.id_karyawan WHERE a.id = ? LIMIT 1");
        $stmt->bind_param('i', $id_absensi);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            if ($aksi === 'setujui') {
                // Notifikasi WhatsApp disetujui
                $pesan = "PERSETUJUAN PENGAJUAN IZIN\n\n"
                    . "Halo {$row['nama']},\n\n"
                    . "Pengajuan " . strtoupper($row['status']) . " Anda untuk tanggal " . format_tanggal_panjang($row['tanggal']) . " telah DISETUJUI oleh administrator.\n\n"
                    . "Terima kasih.";

                kirim_dan_log_wa($koneksi, $row['no_hp'], $pesan, 'persetujuan_izin');
                flash('success', 'Pengajuan izin berhasil disetujui.');
            } else {
                // Jika ditolak, hapus record absensi izin agar karyawan bisa absen ulang
                $stmtDel = $koneksi->prepare("DELETE FROM absensi WHERE id = ?");
                $stmtDel->bind_param('i', $id_absensi);
                $stmtDel->execute();
                $stmtDel->close();

                // Notifikasi WhatsApp ditolak
                $pesan = "PERSETUJUAN PENGAJUAN IZIN\n\n"
                    . "Halo {$row['nama']},\n\n"
                    . "Mohon maaf, pengajuan " . strtoupper($row['status']) . " Anda untuk tanggal " . format_tanggal_panjang($row['tanggal']) . " DITOLAK oleh administrator.\n\n"
                    . "Silakan hubungi admin atau lakukan presensi secara normal.";

                kirim_dan_log_wa($koneksi, $row['no_hp'], $pesan, 'penolakan_izin');
                flash('success', 'Pengajuan izin berhasil ditolak.');
            }
        }
    }
    redirect(BASE_URL . '/admin/izin.php');
}

// Query mengambil data pengajuan izin/sakit/cuti
$stmt = $koneksi->prepare("SELECT a.id, a.tanggal, a.status, a.keterangan, a.created_at, k.nama, k.nik, k.jabatan 
          FROM absensi a 
          JOIN karyawan k ON k.id = a.id_karyawan 
          WHERE a.status IN ('Izin', 'Sakit', 'Cuti') 
          ORDER BY a.tanggal DESC, a.id DESC");
$stmt->execute();
$data = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Persetujuan Izin / Sakit</h1>
        <p class="page-subtitle">Kelola pengajuan izin, sakit, dan cuti karyawan</p>
    </div>
</div>

<div class="panel">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Nama Karyawan</th>
                    <th>NIK / Jabatan</th>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Alasan / Keterangan</th>
                    <th width="160" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($r = $data->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= e($r['nama']) ?></strong></td>
                    <td><?= e($r['nik']) ?><br><small style="color: var(--text-secondary);"><?= e($r['jabatan']) ?></small></td>
                    <td><?= e(format_tanggal($r['tanggal'])) ?></td>
                    <td><span class="badge-status badge-secondary"><?= e($r['status']) ?></span></td>
                    <td><?= e($r['keterangan']) ?></td>
                    <td class="text-center">
                        <div class="actions-group">
                            <form method="post" style="display:inline;" onsubmit="return confirm('Setujui pengajuan ini?')">
                                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                                <input type="hidden" name="id_absensi" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="aksi" value="setujui">
                                <button class="btn-action edit" type="submit" style="background-color: #e6f4ea; color: #137333;">Setujui</button>
                            </form>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Tolak pengajuan ini?')">
                                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                                <input type="hidden" name="id_absensi" value="<?= (int)$r['id'] ?>">
                                <input type="hidden" name="aksi" value="tolak">
                                <button class="btn-action delete" type="submit">Tolak</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php if ($data->num_rows === 0): ?>
                <tr>
                    <td colspan="7" class="text-center empty-state">Tidak ada pengajuan izin/sakit yang perlu ditinjau.</td>
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