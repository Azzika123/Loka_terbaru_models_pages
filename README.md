# Loka Boardgame Cafe: PHP + MySQL (phpMyAdmin)

Data disimpan di database MySQL (`loka_cafe`), bisa dilihat dan diubah lewat phpMyAdmin.

## Struktur folder
```
loka-web/
  index.html  style.css  script.js
  database.sql   <- diimpor ke phpMyAdmin
  setup.php      <- buat akun admin (hapus setelah dipakai)
  migrate.php    <- update database lama (jalankan sekali, lalu hapus)
  img/           <- taruh foto menu di sini (opsional)
  api/
    config.php  bootstrap.php  auth.php  user.php  admin.php  catalog.php
```

## Langkah menjalankan (XAMPP)
1. Nyalakan **Apache** dan **MySQL** di XAMPP Control Panel.
2. Salin folder `loka-web` ke `C:\xampp\htdocs\`.
3. Buka http://localhost/phpmyadmin, tab **Import**, pilih `database.sql`, klik **Go**.
   Kalau database sebelumnya sudah diimpor, impor ulang saja: aman, data lama tidak terhapus, dan tabel `games` (katalog boardgame) ditambahkan.
   Database lama juga perlu membuka http://localhost/loka-web/migrate.php **sekali** (menambah kategori, deskripsi, dan foto pada menu, serta melengkapi menu awal seperti Bakso). Lalu **hapus migrate.php**.
4. Jika MySQL kamu punya kata sandi, ubah `api/config.php`.
5. Buka http://localhost/loka-web/setup.php sekali untuk membuat admin, lalu **hapus setup.php**.
6. Buka http://localhost/loka-web/

Akun admin awal: `admin@loka.id` / `admin123` (ganti kata sandinya di `setup.php` sebelum dijalankan).

Catatan: jangan buka dengan klik dua kali `index.html` atau Live Server, karena PHP tidak jalan di sana.

## Fitur
- Katalog publik di beranda: boardgame (cari, filter genre, filter jumlah pemain, urutkan) dan menu. Bisa dilihat tanpa login.
- Pelanggan: daftar, masuk, katalog menu bergaya toko (kategori, pencarian, keranjang) langsung tampil setelah login, pesan beberapa menu sekaligus, booking meja, batalkan, riwayat, edit profil.
- Admin: statistik, ubah status pesanan dan booking, kelola menu (kategori, deskripsi, foto), kelola boardgame (judul, genre, pemain, durasi, kesulitan, tersedia/dipakai), hapus pelanggan, riwayat login.


## Foto menu
Taruh foto di folder `img/` (misal `img/bakso.jpg`), lalu isi kolom **Foto** di Dashboard admin > Menu dengan `img/bakso.jpg`. Alamat `https://...` juga bisa. Tanpa foto, kartu memakai ilustrasi ikon sesuai kategori.
