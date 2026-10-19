# GeoPresensi

Implementasi sistem presensi kampus berdasarkan empat laporan kelompok. Struktur `app/config`, `app/controller`, `app/core`, `app/models`, `app/views` dan `public/css`, `public/img`, `public/js` dipertahankan.

Susunan kode mengikuti pola **PHP MVC native WPU / Sandhika Galih**: `App`, `Controller`, `Database`, `Flasher`, controller per fitur, model melalui `$this->model()`, dan view melalui `$this->view()` dengan array `$data`. Rujukan: https://github.com/sandhikagalih/phpmvc. Ini adaptasi pola tutorial untuk aplikasi presensi, dengan tambahan validasi akses dan CSRF. Folder `controller` tetap tunggal mengikuti folder Anda. Database tetap SQLite; tutorial biasanya memakai MySQL, tetapi cara memakai PDO melalui `query()` dan `bind()` sama. Panduan membaca kode: [BELAJAR_MVC.md](BELAJAR_MVC.md).

## Menjalankan

PHP 8.2+ dengan PDO SQLite dan ZipArchive. Tidak membutuhkan Composer, npm, atau import database. Data tersimpan otomatis di `app/config/presensi.sqlite`; file sesi tersimpan di folder `app/config` yang terlindungi. Pastikan proses PHP memiliki izin tulis ke folder tersebut.

1. Jalankan Apache melalui Laragon, lalu buka `http://localhost/Sistem-Presensi/public/`.
2. Buat akun admin pada halaman pengaturan awal. Tidak ada kata sandi bawaan.
3. Pada menu Pengguna, tambahkan dosen, mahasiswa, dan BAAK.
4. Tambahkan kelas dengan koordinat **gedung sebenarnya**, dosen pengampu, dan radius awal 50 meter. Tombol lokasi membantu mengambil koordinat; pastikan akurasinya memadai.
5. Daftarkan mahasiswa ke kelas, kemudian buat sesi presensi dengan waktu WIB.
6. Mahasiswa masuk, periksa lokasi, lalu presensi. Dosen memantau dashboard dan rekap; BAAK mengunduh Excel atau mencetak laporan ke PDF.

Alternatif server pengembangan, jalankan dari folder proyek:

```powershell
php -S localhost:8080 -t public
```

Buka `http://localhost:8080`. Untuk mengakses GPS melalui HP, gunakan **HTTPS** dengan sertifikat yang dipercaya HP. URL HTTP menggunakan IP LAN tidak memenuhi syarat secure context browser. Gunakan document root `public` ketika membuat virtual host, supaya folder aplikasi/database tidak terpapar. Aturan `.htaccess` melindungi folder `app` pada Apache; Nginx memerlukan konfigurasi perlindungan tersendiri.

## Fitur

- Login berbasis peran, kata sandi hash, CSRF, session regeneration, dan pembatasan login gagal (5 kali per identitas dalam 15 menit).
- Admin mengelola akun dan kelas; dosen hanya mengelola kelas miliknya. BAAK memiliki akses baca/ekspor. Mahasiswa hanya melihat kelas dan riwayatnya.
- Pendaftaran peserta, pengaturan koordinat/radius, penjadwalan dan penutupan sesi. Koordinat dan radius disalin ke sesi agar perubahan kelas tidak mengubah validasi sesi sebelumnya.
- GPS browser diambil atas tindakan mahasiswa, bukan pelacakan latar belakang. Server memeriksa keanggotaan kelas, waktu sesi, rentang koordinat, akurasi, lokasi maksimal berusia 30 detik, challenge sekali pakai maksimal 60 detik, dan jarak Haversine.
- Timestamp server WIB, keunikan presensi per mahasiswa/sesi, log penolakan, dashboard polling 10 detik, dan laporan termasuk status belum presensi/tidak hadir.
- Ekspor `.xlsx` asli dan halaman cetak yang dapat disimpan sebagai PDF melalui dialog browser.
- Pengingat 15 menit sebelum sesi melalui dashboard/notifikasi browser, **hanya saat halaman terbuka** dan izin diberikan. Bukan push notification latar belakang.
- Google Maps opsional: set environment variable `GOOGLE_MAPS_KEY` sebelum menjalankan PHP untuk peta interaktif kelas. Batasi key ke HTTP referrer domain aplikasi. Tanpa key, validasi GPS tetap berjalan dan tautan titik Google Maps tersedia.

## Batas implementasi terhadap laporan

Ini aplikasi web responsif untuk desktop dan HP, **belum APK Android/build iOS**. Geolocation browser tidak menyediakan sinyal Mock Location yang dapat dipercaya server. Pemeriksaan koordinat, usia, akurasi, dan challenge mengurangi kesalahan/replay sederhana, tetapi **tidak menjamin pencegahan Fake GPS atau koordinat buatan**. Field `mock` ditolak jika terlapor, namun bukan bukti perangkat. Untuk memenuhi MUST anti-Fake-GPS secara kuat, diperlukan aplikasi native, pemeriksaan mock location native, attestation perangkat yang diverifikasi backend, dan pengujian keamanan terpisah. Jangan menganggap versi web ini lulus acceptance anti-Fake-GPS laporan.

Sinkronisasi presensi offline (COULD) tidak diterapkan karena bukti waktu/lokasi belum dapat diverifikasi secara aman. Radius kustom (COULD) tersedia. Honor dosen, nilai, RFID, dan sistem pembayaran dikecualikan sesuai scope. Target 1.000 pengguna, akurasi 95%, penetration testing, dan persetujuan stakeholder belum dibuktikan. SQLite cocok untuk pengembangan/uji coba; deployment berskala besar memerlukan evaluasi database dan load test.

Laporan memuat target waktu berbeda (2, 3, dan 4 bulan); implementasi mengikuti kebutuhan fitur yang berulang, bukan memperlakukan jadwal proyek sebagai instruksi eksekusi.

## Arsitektur & API

`public/index.php` → `app/init.php` → `App` → controller fitur → model → PDO/SQLite → view PHP. `Controller` menyediakan `model()` dan `view()`; `halaman()` menampilkan header, isi, dan footer. `Attendance` memuat perhitungan jarak dan kebijakan geofencing. Tidak memakai layanan eksternal untuk validasi lokasi. Semua SQL berada di model atau skema, bukan controller/view.

Contoh URL: `index.php?url=kelas/index`. Pada Apache dengan rewrite aktif, `public/kelas/index` juga dapat digunakan. Helper `url()` membuat URL controller/method secara otomatis. URL lama `?route=...` masih dikenali untuk kompatibilitas bookmark.

Relasi: `users` → `classes.lecturer_id`; `classes` ↔ `users` melalui `enrollments`; `classes` → `sessions`; `sessions` + `users` → `attendance` (unik pasangan); `attempts` mencatat penolakan. `login_attempts` membatasi login gagal.

Endpoint memakai cookie sesi:

| Endpoint | Akses | Fungsi |
|---|---|---|
| GET `index.php?url=presensi/challenge&session=ID` | Mahasiswa peserta | Token sekali pakai |
| POST `index.php?url=presensi/simpan` | Mahasiswa peserta | Form-urlencoded: csrf, session_id, token, latitude, longitude, accuracy, timestamp (milidetik Unix); JSON ok/message |
| GET `index.php?url=home/live` | Akun masuk | Rekap sesuai hak akses |
| GET `index.php?url=laporan/excel` | Admin/BAAK/dosen | Unduh XLSX; filter class_id/from/to |
| GET `index.php?url=laporan/cetak` | Admin/BAAK/dosen | Cetak/PDF; filter sama |

## Verifikasi

```powershell
python verify.py
```

Pengujian HTTP memakai database sementara dan server PHP terpisah, tidak mengubah data aplikasi Anda. Mencakup setup, login, CSRF, hak akses, kelas/peserta/sesi, lokasi di dalam/luar radius, akurasi buruk, koordinat kedaluwarsa, replay/duplikasi, penutupan sesi, isolasi dosen, dan struktur Excel. GPS perangkat, notifikasi, Google Maps dengan key, dan dialog cetak memerlukan UAT browser/HP.

Skenario UAT: uji izin GPS ditolak, posisi di dalam dan di luar 50 m, lokasi indoor tidak akurat, sesi habis/ditutup, dua mahasiswa berbeda, penolakan akses kelas lain, ekspor sesuai filter, serta simpan PDF. Jalankan di gedung kampus sebenarnya dengan koordinat terverifikasi. Cadangkan database sebelum digunakan dengan data nyata.
