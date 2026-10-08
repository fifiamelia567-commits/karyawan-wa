<?php
require_once __DIR__ . '/../config/helpers.php';
require_login();

$id_karyawan = (int)$_SESSION['user_id'];
$today = date('Y-m-d');
$now = date('H:i:s');

// 1. Ambil Pengaturan Jam Masuk & Toleransi
$qSetting = $koneksi->query("SELECT jam_masuk, toleransi_terlambat FROM pengaturan WHERE id = 1 LIMIT 1");
$setting = $qSetting->fetch_assoc();

$jam_masuk_resmi = $setting['jam_masuk'] ?? '08:00:00';
$toleransi = (int)($setting['toleransi_terlambat'] ?? 15);

// Hitung Batas Akhir Jam Tepat Waktu (Jam Masuk + Toleransi)
$batas_masuk = date('H:i:s', strtotime("+$toleransi minutes", strtotime($jam_masuk_resmi)));

// Menentukan Status Hadir atau Terlambat
$status = ($now <= $batas_masuk) ? 'Hadir' : 'Terlambat';

// 2. Simpan atau Update Absensi
$stmt = $koneksi->prepare("INSERT INTO absensi (id_karyawan, tanggal, jam_masuk, status, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param('isss', $id_karyawan, $today, $now, $status);

if ($stmt->execute()) {
    $stmt->close();
    flash('success', "Absen masuk berhasil tercatat. Status: {$status}");
} else {
    flash('error', "Gagal melakukan absen masuk.");
}

redirect(BASE_URL . '/absensi/index.php');