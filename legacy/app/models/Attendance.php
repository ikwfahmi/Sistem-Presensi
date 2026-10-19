<?php

// Model perhitungan lokasi. Tidak membutuhkan query database.
class Attendance
{
    public function distance($latMahasiswa, $lngMahasiswa, $latKelas, $lngKelas)
    {
        // Rumus Haversine: jarak dua koordinat pada permukaan bumi, dalam meter.
        $selisihLat = deg2rad($latKelas - $latMahasiswa);
        $selisihLng = deg2rad($lngKelas - $lngMahasiswa);
        $nilai = sin($selisihLat / 2) ** 2
               + cos(deg2rad($latMahasiswa)) * cos(deg2rad($latKelas))
               * sin($selisihLng / 2) ** 2;
        return 6371000 * 2 * atan2(sqrt($nilai), sqrt(max(0, 1 - $nilai)));
    }

    public function validate($sesi, $gps, $waktuSekarang)
    {
        // 1. Sesi harus sedang berlangsung.
        if ($sesi['closed'] || $waktuSekarang < strtotime($sesi['starts_at']) ||
            $waktuSekarang > strtotime($sesi['ends_at'])) {
            throw new RuntimeException('Sesi presensi belum dibuka atau sudah berakhir.');
        }

        // 2. Koordinat dan akurasi harus berupa angka yang valid.
        foreach (['latitude', 'longitude', 'accuracy', 'timestamp'] as $nama) {
            if (!isset($gps[$nama]) || !is_numeric($gps[$nama]) || !is_finite((float) $gps[$nama])) {
                throw new RuntimeException('Data lokasi tidak valid.');
            }
        }
        $latitude = (float) $gps['latitude'];
        $longitude = (float) $gps['longitude'];
        $akurasi = (float) $gps['accuracy'];
        if (abs($latitude) > 90 || abs($longitude) > 180 || $akurasi <= 0) {
            throw new RuntimeException('Koordinat atau akurasi lokasi tidak valid.');
        }

        // Browser tidak dapat membuktikan mock location; ini hanya flag terlapor.
        if (!empty($gps['mock'])) {
            throw new RuntimeException('Lokasi tiruan terdeteksi.');
        }

        // 3. GPS browser memakai milidetik; time() server memakai detik.
        if (abs($waktuSekarang - (float) $gps['timestamp'] / 1000) > 30) {
            throw new RuntimeException('Lokasi sudah kedaluwarsa. Ambil lokasi baru.');
        }
        if ($akurasi > min(50, (int) $sesi['radius'])) {
            throw new RuntimeException('Sinyal GPS kurang akurat. Pindah ke area terbuka dan coba kembali.');
        }

        // 4. Periksa jarak mahasiswa terhadap pusat lokasi kelas.
        $jarak = $this->distance($latitude, $longitude, (float) $sesi['latitude'], (float) $sesi['longitude']);
        if ($jarak > (int) $sesi['radius']) {
            throw new RuntimeException('Anda berada di luar radius presensi (' . round($jarak) . ' meter).');
        }
        return $jarak;
    }
}
