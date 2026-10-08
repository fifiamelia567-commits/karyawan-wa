<?php
require_once __DIR__ . '/../config/helpers.php';
require_admin();

$keyword = trim($_GET['q'] ?? '');
$like = "%{$keyword}%";

$stmt = $koneksi->prepare("SELECT id, nik, nama, jabatan, no_hp, email, created_at FROM karyawan WHERE nik LIKE ? OR nama LIKE ? OR jabatan LIKE ? OR no_hp LIKE ? ORDER BY id DESC");
$stmt->bind_param('ssss', $like, $like, $like, $like);
$stmt->execute();
$data = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header flex-between">
    <div>
        <h1 class="page-title">Data Karyawan</h1>
        <p class="page-subtitle">Kelola dan atur informasi seluruh karyawan</p>
    </div>
    <a class="btn btn-burgundy-main" href="<?= BASE_URL ?>/karyawan/tambah.php">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"></path></svg>
        Tambah Karyawan
    </a>
</div>

<div class="panel">
    <form class="search-box" method="get">
        <input type="text" name="q" class="input-field" value="<?= e($keyword) ?>" placeholder="Cari NIK, nama, jabatan, atau No. WhatsApp...">
        <button class="btn btn-burgundy-main" type="submit">Cari</button>
        <a class="btn btn-outline" href="<?= BASE_URL ?>/karyawan/index.php">Reset</a>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>NIK</th>
                    <th>Nama Lengkap</th>
                    <th>Jabatan</th>
                    <th>No. WhatsApp</th>
                    <th>Email</th>
                    <th width="150" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($row = $data->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= e($row['nik']) ?></strong></td>
                    <td><?= e($row['nama']) ?></td>
                    <td><span class="badge-jabatan"><?= e($row['jabatan']) ?></span></td>
                    <td><?= e($row['no_hp']) ?></td>
                    <td><?= e($row['email']) ?></td>
                    <td>
                        <div class="actions-group">
                            <a class="btn-action edit" href="<?= BASE_URL ?>/karyawan/edit.php?id=<?= (int)$row['id'] ?>">Edit</a>
                            <form method="post" action="<?= BASE_URL ?>/karyawan/hapus.php" onsubmit="return confirm('Yakin ingin menghapus karyawan ini?')" style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf'] ?? '') ?>">
                                <button class="btn-action delete" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if ($data->num_rows === 0): ?>
                <tr>
                    <td colspan="7" class="text-center empty-state">Data karyawan tidak ditemukan.</td>
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