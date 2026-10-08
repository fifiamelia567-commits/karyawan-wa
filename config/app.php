<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

define('BASE_URL', '/karyawan-wa');

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function require_login() {
    if (empty($_SESSION['login'])) {
        redirect(BASE_URL . '/auth/login.php');
    }
}

function require_admin() {
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        flash('error', 'Halaman ini hanya dapat diakses admin.');
        redirect(BASE_URL . '/absensi/index.php');
    }
}
