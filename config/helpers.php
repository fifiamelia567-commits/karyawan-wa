<?php
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../api/fonnte.php';

function format_tanggal($tanggal) {
    if (!$tanggal) return '-';
    return date('d/m/Y', strtotime($tanggal));
}

function format_tanggal_panjang($tanggal) {
    if (!$tanggal) return '-';
    $bulan = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];
    $ts = strtotime($tanggal);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function normalize_phone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', trim($phone));
    if (str_starts_with($phone, '+62')) {
        return '0' . substr($phone, 3);
    }
    if (str_starts_with($phone, '62')) {
        return '0' . substr($phone, 2);
    }
    return $phone;
}

function status_absensi($jam_masuk) {
    if (!$jam_masuk) return 'Belum Absen';
    return ($jam_masuk <= '07:00:00') ? 'Hadir' : 'Terlambat';
}

function catat_log_wa($koneksi, $target, $jenis, $response) {
    $json = is_string($response) ? $response : json_encode($response, JSON_UNESCAPED_UNICODE);
    $stmt = $koneksi->prepare("INSERT INTO log_whatsapp (target, jenis, response, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param('sss', $target, $jenis, $json);
    $stmt->execute();
    $stmt->close();
}

function kirim_dan_log_wa($koneksi, $target, $message, $jenis) {
    $hasil = kirimWhatsApp($target, $message);
    catat_log_wa($koneksi, $target, $jenis, $hasil['response'] ?? ['error' => $hasil['error']]);
    return $hasil;
}
