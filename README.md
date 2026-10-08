# Aplikasi Register, CRUD, Absensi & WhatsApp Fonnte

## 1. Instalasi
1. Jalankan Apache dan MySQL di XAMPP.
2. Buat folder `C:\xampp\htdocs\karyawan-wa`.
3. Salin semua file project ke folder tersebut.
4. Buka phpMyAdmin.
5. Import `database.sql`.
6. Edit `config/fonnte.php`:
   - `FONNTE_TOKEN`
   - `FONNTE_ADMIN_NUMBER`
7. Pastikan ekstensi cURL aktif di PHP.
8. Buat hash password admin:
   `php -r "echo password_hash('admin123', PASSWORD_DEFAULT), PHP_EOL;"`
9. Masukkan hash tersebut ke tabel `admins`, misalnya:
   `INSERT INTO admins (nama,email,password) VALUES ('Administrator','admin@karyawan.local','HASH');`
10. Buka `http://localhost/karyawan-wa/`.

## 2. Akun demo admin
Email: `admin@karyawan.local`
Password: `admin123`

Ganti password sebelum digunakan sungguhan.

## 3. Alur pengujian
1. Register karyawan.
2. Pastikan data masuk tabel `karyawan`.
3. Pastikan WA karyawan dan admin diproses.
4. Login admin, edit data, cek WA.
5. Hapus data, cek WA.
6. Register/login karyawan baru.
7. Absen masuk, cek database dan WA.
8. Coba absen masuk lagi: harus ditolak.
9. Absen pulang, cek database dan WA.
10. Coba pulang lagi atau pulang sebelum masuk: harus ditolak.
11. Login admin dan buka laporan.

## 4. Keamanan
- Password memakai `password_hash()` dan `password_verify()`.
- Query database memakai prepared statement.
- Output menggunakan `htmlspecialchars`.
- Session dipakai untuk autentikasi.
- Delete memakai POST + CSRF + konfirmasi.
- Token Fonnte tidak ditampilkan pada halaman.
- Jangan upload token asli ke GitHub.
