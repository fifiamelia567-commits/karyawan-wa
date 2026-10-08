<?php
require_once __DIR__ . '/config/helpers.php';

if (!empty($_SESSION['login'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        redirect(BASE_URL . '/admin/index.php');
    }
    redirect(BASE_URL . '/absensi/index.php');
}
redirect(BASE_URL . '/auth/login.php');
