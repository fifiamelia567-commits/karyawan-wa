<?php
$flash = get_flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Karyawan</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body class="app-body">

<header class="navbar">
    <div class="container nav-inner">
        <a class="logo" href="<?= BASE_URL ?>/">
            <span class="logo-badge">SK</span>
            <span>Sistem Karyawan</span>
        </a>
        <nav class="nav-menu">
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a class="nav-link" href="<?= BASE_URL ?>/admin/index.php">Dashboard</a>
                <a class="nav-link" href="<?= BASE_URL ?>/karyawan/index.php">Karyawan</a>
                <a class="nav-link" href="<?= BASE_URL ?>/admin/izin.php">Persetujuan Izin</a>
                <a class="nav-link" href="<?= BASE_URL ?>/admin/laporan.php">Laporan</a>
                <a class="nav-link" href="<?= BASE_URL ?>/admin/pengaturan.php">Pengaturan</a>
                <a class="nav-link" href="<?= BASE_URL ?>/api/test.php">Test API</a>
            <?php elseif (!empty($_SESSION['login'])): ?>
                <a class="nav-link" href="<?= BASE_URL ?>/absensi/index.php">Absensi</a>
                <a class="nav-link" href="<?= BASE_URL ?>/absensi/riwayat.php">Riwayat</a>
            <?php endif; ?>
            
            <?php if (!empty($_SESSION['login'])): ?>
                <a class="nav-link btn-logout" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
            <?php else: ?>
                <a class="nav-link btn-login-nav" href="<?= BASE_URL ?>/auth/login.php">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container main-content">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>