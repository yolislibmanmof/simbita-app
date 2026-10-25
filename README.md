# SIMBITA

Sistem Informasi Bimbingan Skripsi, Tesis, dan Disertasi berbasis PHP 8, MySQL/MariaDB, Bootstrap 5, dan PDF.js.

## Struktur

simbita-app/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DocumentController.php
│   │   └── ReviewController.php
│   ├── Core/
│   │   ├── Auth.php
│   │   ├── Csrf.php
│   │   └── Helpers.php
│   ├── Models/
│   │   ├── AcademicWork.php
│   │   └── User.php
│   └── Views/
│       ├── admin/dashboard.php
│       ├── auth/login.php
│       ├── lecturer/dashboard.php
│       ├── student/dashboard.php
│       └── partials/navbar.php
├── config/
│   ├── config.php
│   └── database.php
├── public/
│   ├── assets/
│   │   ├── css/app.css
│   │   └── js/pdf-review.js
│   └── index.php
├── storage/
│   └── documents/
└── README.md

## Instalasi

1. Salin folder `simbita-app` ke `htdocs` atau folder web server.
2. Import `simbita_final.sql` ke MySQL/MariaDB.
3. Sesuaikan `config/config.php`, terutama `DB_USER`, `DB_PASS`, dan `BASE_URL`.
4. Pastikan folder `storage/documents` dapat ditulis oleh web server.
5. Akses `http://localhost/simbita-app/public`.

## Keamanan

- Password menggunakan `password_hash()` dan `password_verify()`.
- Query memakai PDO prepared statements.
- Perlindungan CSRF tersedia melalui `Csrf`.
- File PDF disimpan di luar folder publik dan hanya dapat diakses setelah otorisasi.
- Hanya PDF yang diperbolehkan pada versi awal.

