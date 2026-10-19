<?php

class Presensi extends Controller
{
    public function challenge()
    {
        $pengguna = require_roles(['mahasiswa']);
        $idSesi = (int) ($_GET['session'] ?? 0);
        $sesi = $this->model('Sesi_model')->getSesiMahasiswa($idSesi, $pengguna['id']);
        if (!$sesi) {
            json_response(['message' => 'Sesi tidak tersedia.'], 403);
        }
        $token = bin2hex(random_bytes(24));
        $_SESSION['challenge'] = ['token' => $token, 'session' => $idSesi, 'at' => time()];
        json_response(['token' => $token]);
    }

    public function simpan()
    {
        $this->hanyaPost();
        $pengguna = require_roles(['mahasiswa']);
        $idSesi = (int) ($_POST['session_id'] ?? 0);
        $model = $this->model('Presensi_model');

        try {
            // Token sekali pakai mencegah pengiriman ulang permintaan yang sama.
            $challenge = $_SESSION['challenge'] ?? [];
            unset($_SESSION['challenge']);
            if (!isset($challenge['token']) ||
                !hash_equals($challenge['token'], (string) ($_POST['token'] ?? '')) ||
                $challenge['session'] !== $idSesi || time() - $challenge['at'] > 60) {
                throw new RuntimeException('Verifikasi kedaluwarsa. Silakan ambil lokasi ulang.');
            }

            $sesi = $this->model('Sesi_model')->getSesiMahasiswa($idSesi, $pengguna['id']);
            if (!$sesi) {
                throw new RuntimeException('Anda tidak terdaftar pada kelas ini.');
            }
            $jarak = $this->model('Attendance')->validate($sesi, $_POST, time());
            if ($model->getPresensi($idSesi, $pengguna['id'])) {
                throw new RuntimeException('Anda sudah presensi pada sesi ini.');
            }
            $model->simpan($idSesi, $pengguna['id'], $_POST, $jarak);
            json_response([
                'ok' => true,
                'message' => 'Presensi berhasil dicatat pukul ' . date('H:i:s') . ' WIB.'
            ]);
        } catch (RuntimeException $error) {
            $model->catatPenolakan($pengguna['id'], $idSesi, $error->getMessage());
            throw $error;
        }
    }
}
