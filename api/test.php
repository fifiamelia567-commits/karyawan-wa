<?php
require_once __DIR__ . '/../config/helpers.php';
require_admin();

$result = null;
if (is_post()) {
    $target = normalize_phone($_POST['target'] ?? '');
    $message = trim($_POST['message'] ?? 'Test API WhatsApp dari Sistem Karyawan.');
    $result = kirimWhatsApp($target, $message);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Pengujian API Fonnte</h1>
        <p class="page-subtitle">Gunakan halaman ini untuk menguji pengiriman pesan WhatsApp via API</p>
    </div>
</div>

<div class="panel" style="max-width: 680px;">
    <div class="panel-head">
        <h2>Kirim Pesan Uji Coba</h2>
        <p style="color: var(--text-secondary); font-size: 13.5px;">Pastikan token dan perangkat Fonnte Anda sudah terhubung.</p>
    </div>

    <form method="post">
        <div class="form-group">
            <label for="target">Nomor WhatsApp Tujuan</label>
            <input type="text" id="target" name="target" class="input-field" placeholder="08xxxxxxxxxx" value="<?= e($_POST['target'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="message">Pesan WhatsApp</label>
            <textarea id="message" name="message" class="input-field textarea-field" rows="5" required><?= e($_POST['message'] ?? 'Test API WhatsApp dari Sistem Karyawan.') ?></textarea>
        </div>

        <button class="btn btn-burgundy-main" type="submit">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            Kirim Test Pesan
        </button>
    </form>

    <?php if ($result): ?>
        <hr class="divider">
        
        <div class="api-result">
            <div class="result-status">
                <span>Status Pengiriman:</span>
                <?php if ($result['success']): ?>
                    <span class="badge-status badge-success">Berhasil</span>
                <?php else: ?>
                    <span class="badge-status badge-warning">Gagal</span>
                <?php endif; ?>
            </div>

            <label style="margin-top: 14px;">Response JSON API:</label>
            <pre class="json-preview"><?= e(json_encode($result['response'] ?? ['error' => $result['error']], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>