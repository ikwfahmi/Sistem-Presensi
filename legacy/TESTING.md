# Hasil verifikasi — 13 Oktober 2026

Status setelah perubahan ke pola PHP MVC WPU: **43 pemeriksaan integrasi HTTP lulus**, exit code 0, tanpa warning/fatal error PHP. Semua 36 berkas PHP lolos `php -l`; JavaScript lolos `node --check`.

Lingkungan uji: Windows, PHP CLI 8.2.12, Python 3.13, SQLite, server development PHP dengan port sementara. PHP Laragon 8.3.30 juga terdeteksi memiliki PDO SQLite dan ZipArchive; server Apache Laragon belum diuji langsung.

Pengujian dijalankan melalui `python verify.py` dengan database dan sesi sementara. Tidak membuat akun produksi atau mengisi database aplikasi.

| Area | Hasil |
|---|---|
| Instalasi administrator pertama dan penguncian ulang setup | Lulus |
| Login admin, mahasiswa, dosen, dan BAAK | Lulus |
| Pembuatan pengguna, kelas, peserta, dan sesi | Lulus, diperiksa melalui alur presensi |
| Penolakan CSRF salah dan pembatasan hak akses | Lulus |
| Routing controller/method, action formulir, dan kompatibilitas bookmark lama | Lulus |
| Method private/induk dan path traversal tidak dapat diakses via URL | Lulus |
| Tindakan perubahan data menolak GET | Lulus |
| Lokasi luar radius, akurasi buruk, usia lokasi, koordinat tidak valid, flag mock terlapor | Lulus |
| Presensi valid, penolakan duplikasi dan replay | Lulus |
| Riwayat mahasiswa dan isolasi kelas dosen | Lulus |
| Penutupan sesi dan penolakan presensi sesudah ditutup | Lulus |
| Hak baca BAAK dan filter kelas | Lulus |
| XLSX: integritas ZIP, seluruh XML valid, hadir dan tidak hadir | Lulus |
| Halaman cetak/PDF tersedia | Lulus melalui HTTP |
| Database: satu presensi tersimpan dan penolakan dicatat | Lulus |

Belum diverifikasi: tampilan visual desktop/HP (browser pemeriksaan tidak tersedia), GPS perangkat nyata, deny/timeout izin GPS, Google Maps dengan API key, notifikasi browser, hasil cetak PDF secara visual, batas tepat 50 meter di lapangan, beban 1.000 pengguna, dan penetration testing. Pemeriksaan flag mock tidak membuktikan deteksi Fake GPS nyata. Build Android/iOS, attestation, dan push notification latar belakang belum diimplementasikan; lihat README.

UAT stakeholder tetap diperlukan. Dokumen ini bukan pernyataan penerimaan atau persetujuan stakeholder.
