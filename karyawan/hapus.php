<?php
require_once __DIR__ . '/../config/helpers.php';
require_admin();

if (!is_post()) redirect(BASE_URL . '/karyawan/index.php');
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) die('CSRF tidak valid.');

$id=(int)($_POST['id']??0);
$stmt=$koneksi->prepare("SELECT id,nama,nik,no_hp FROM karyawan WHERE id=? LIMIT 1");
$stmt->bind_param('i',$id); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close();

if(!$row){ flash('error','Data karyawan tidak ditemukan.'); redirect(BASE_URL.'/karyawan/index.php'); }

$pesan="NOTIFIKASI DATA KARYAWAN\n\nHalo {$row['nama']},\n\nData Anda pada sistem karyawan telah dihapus oleh administrator.\n\nJika Anda merasa tidak melakukan permintaan tersebut, silakan hubungi administrator.";
kirim_dan_log_wa($koneksi,$row['no_hp'],$pesan,'hapus_karyawan');

$stmt=$koneksi->prepare("DELETE FROM karyawan WHERE id=?"); $stmt->bind_param('i',$id);
if($stmt->execute()) flash('success','Data karyawan berhasil dihapus. Notifikasi WhatsApp diproses.');
else flash('error','Data gagal dihapus.');
$stmt->close();
redirect(BASE_URL.'/karyawan/index.php');
