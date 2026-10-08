CREATE DATABASE IF NOT EXISTS db_karyawan
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_karyawan;

CREATE TABLE IF NOT EXISTS karyawan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nik VARCHAR(30) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    jabatan VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_karyawan INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME NULL,
    jam_pulang TIME NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Hadir',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unik_absensi_harian (id_karyawan, tanggal),
    CONSTRAINT fk_absensi_karyawan
        FOREIGN KEY (id_karyawan) REFERENCES karyawan(id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS log_whatsapp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    target VARCHAR(30) NOT NULL,
    jenis VARCHAR(50) NOT NULL,
    response LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Setelah database dibuat, buat hash dengan password_hash() atau jalankan:
-- php -r "echo password_hash('admin123', PASSWORD_DEFAULT), PHP_EOL;"
-- Lalu masukkan hasil hash ke query berikut:
-- INSERT INTO admins (nama,email,password) VALUES ('Administrator','admin@karyawan.local','HASH_DI_SINI');
