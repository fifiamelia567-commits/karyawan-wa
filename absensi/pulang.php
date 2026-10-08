<?php
require_once __DIR__ . '/../config/helpers.php';
require_login();

$id=(int)$_SESSION['user_id']; $today=date('Y-m-d'); $now=date('H:i:s');
$stmt=$koneksi->prepare("SELECT k.nama,k.nik,k.no_hp,a.id,a.jam_masuk,a.jam_pulang,a.status FROM karyawan k LEFT JOIN absensi a ON a.id_karyawan=k.id AND a.tanggal=? WHERE k.id=? LIMIT 1");
$stmt->bind_param('si',$today,$id); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc(); $stmt->close();

if(!$row || !$row['id'] || !$row['jam_masuk']) { flash('error','Anda belum melakukan absensi masuk hari ini.'); redirect(BASE_URL.'/absensi/index.php'); }
if($row['jam_pulang']) { flash('error','Anda sudah melakukan absensi pulang hari ini.'); redirect(BASE_URL.'/absensi/index.php'); }

$stmt=$koneksi->prepare("UPDATE absensi SET jam_pulang=?,status='Pulang' WHERE id=?");
$stmt->bind_param('si',$now,$row['id']);
if($stmt->execute()){
    $stmt->close();
    $pesan="ABSENSI PULANG\n\nNama       : {$row['nama']}\nNIK        : {$row['nik']}\nTanggal    : ".format_tanggal_panjang($today)."\nJam Masuk  : ".date('H:i',strtotime($row['jam_masuk']))."\nJam Pulang : ".date('H:i',strtotime($now))."\nStatus     : PULANG\n\nTerima kasih.\nAbsensi pulang Anda telah berhasil dicatat.";
    kirim_dan_log_wa($koneksi,$row['no_hp'],$pesan,'absensi_pulang');
    flash('success',"Absensi pulang berhasil dicatat pukul ".date('H:i',strtotime($now)).".");
}else{ $stmt->close(); flash('error','Absensi pulang gagal disimpan.'); }
redirect(BASE_URL.'/absensi/index.php');
