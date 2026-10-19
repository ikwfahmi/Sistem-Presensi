# Membaca kode PHP MVC GeoPresensi

Kode mengikuti pola PHP MVC native pada seri Web Programming UNPAS milik Sandhika Galih: https://github.com/sandhikagalih/phpmvc. Polanya diterapkan pada fitur presensi. Nama folder Anda tetap sama, termasuk `app/controller` (tutorial memakai `controllers`).

## 1. Mulai dari pintu masuk

Buka `public/index.php`. File ini memanggil `app/init.php`, lalu membuat aplikasi:

```php
$app = new App;
```

`app/init.php` memuat konfigurasi, kelas inti, dan sesi login. `app/core/App.php` membaca URL, memilih controller, memilih method, kemudian menjalankan method tersebut.

Contoh `index.php?url=kelas/index` berarti: buka `app/controller/Kelas.php`, buat objek `Kelas`, jalankan `index()`.

## 2. Controller mengatur alur

Buka `app/controller/Kelas.php`. Semua controller fitur memakai `extends Controller`. Pada `index()`:

```php
$pengguna = require_roles(['admin', 'dosen']);
$data['classes'] = $this->model('Kelas_model')->getKelasPengguna($pengguna);
$this->halaman('classes', $data);
```

Baris pertama memeriksa akun. Baris kedua meminta data kepada model. Baris ketiga menampilkan halaman. Controller tidak menulis SQL.

`halaman()` adalah tambahan kecil di kelas induk `Controller` yang menjalankan:

```php
$this->view('layout_top', $data);
$this->view($view, $data);
$this->view('layout_bottom', $data);
```

Jika membutuhkan halaman tanpa header/footer, gunakan `$this->view()` langsung, seperti laporan cetak.

## 3. Model mengambil dan menyimpan data

Buka `app/models/Kelas_model.php`. Constructor membuat objek `Database`. Contoh mengambil satu kelas:

```php
$this->db->query('SELECT * FROM classes WHERE id=?');
$this->db->bind(1, $id);
return $this->db->single();
```

`query()` menyiapkan SQL. `bind(1, $id)` mengisi tanda `?` pertama; parameter tidak digabung langsung ke teks SQL. `single()` menjalankan query dan mengambil satu baris. Untuk banyak baris gunakan `resultSet()`. Untuk INSERT/UPDATE gunakan `execute()` lalu `rowCount()`.

Tutorial WPU memakai pola method yang sama. Di proyek ini koneksinya SQLite supaya data sebelumnya tetap dapat digunakan. Skema tabel dapat dibaca di `app/config/schema.sql`; konfigurasi berada di `app/config/config.php`.

## 4. View menampilkan array data

Buka `app/views/classes.php`. Array `$data` yang dikirim controller dapat dibaca langsung:

```php
<?php foreach ($data['classes'] as $kelas): ?>
    <h3><?= e($kelas['name']) ?></h3>
<?php endforeach; ?>
```

`e()` mengamankan teks sebelum ditampilkan sebagai HTML. View berisi tampilan/formulir, bukan query database. Untuk mengubah footer, edit `app/views/layout_bottom.php`; untuk CSS, edit `public/css/app.css`.

Formulir kelas dikirim ke `kelas/simpan`, kemudian controller memvalidasi input dan memanggil model `tambah()` atau `ubah()`. Setelah berhasil, controller mengalihkan pengguna ke halaman kelas. `Flasher` menyimpan pesan di sesi dan menampilkannya sekali.

## 5. Peta fitur

| Fitur | Controller | Model utama | View |
|---|---|---|---|
| Dashboard | Home | Kelas_model, Sesi_model, Laporan_model | dashboard |
| Login dan akun | Auth | User_model | login, setup, password |
| Pengguna | Pengguna | User_model | users |
| Kelas dan peserta | Kelas | Kelas_model | classes |
| Sesi pertemuan | Sesi | Sesi_model | sessions |
| Presensi GPS | Presensi | Presensi_model, Sesi_model, Attendance | dashboard, melalui JavaScript |
| Rekap dan ekspor | Laporan | Laporan_model | reports, print |
| Log penolakan | Keamanan | Presensi_model | security |

Urutan belajar yang disarankan: `public/index.php` → `app/init.php` → `app/core/App.php` → `app/core/Controller.php` → `app/controller/Kelas.php` → `app/models/Kelas_model.php` → `app/views/classes.php`.

Bagian keamanan tambahan tetap ada: CSRF memeriksa formulir, `require_roles()` membatasi peran, dan challenge sekali pakai memeriksa permintaan presensi. Pembantu ini berada di `app/core/Helpers.php`. Hitungan jarak GPS berada di `app/models/Attendance.php`, sedangkan pengambilan GPS dari HP berada di `public/js/app.js`.
