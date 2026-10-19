<?php

class Sesi extends Controller
{
    public function index()
    {
        $pengguna = require_roles(['admin', 'dosen']);
        $data['classes'] = $this->model('Kelas_model')->getKelasPengguna($pengguna);
        $data['sessions'] = $this->model('Sesi_model')->getSesiPengguna($pengguna);
        $this->halaman('sessions', $data);
    }

    public function tambah()
    {
        $this->hanyaPost();
        $pengguna = require_roles(['admin', 'dosen']);
        $kelas = $this->cekKelas((int) ($_POST['class_id'] ?? 0), $pengguna);
        $judul = trim($_POST['title'] ?? '');
        $mulai = strtotime($_POST['starts_at'] ?? '');
        $selesai = strtotime($_POST['ends_at'] ?? '');
        if (!$judul || !$mulai || !$selesai || $selesai <= $mulai || $selesai - $mulai > 86400) {
            throw new RuntimeException('Isi judul dan rentang sesi yang valid (maksimal 24 jam).');
        }

        $data = [
            'title' => $judul,
            'starts_at' => date('Y-m-d H:i:s', $mulai),
            'ends_at' => date('Y-m-d H:i:s', $selesai)
        ];
        $this->model('Sesi_model')->tambah($data, $kelas);
        flash('Sesi presensi dibuat.');
        redirect('sessions');
    }

    public function tutup()
    {
        $this->hanyaPost();
        $pengguna = require_roles(['admin', 'dosen']);
        $model = $this->model('Sesi_model');
        $sesi = $model->getById((int) ($_POST['id'] ?? 0));
        if (!$sesi) {
            throw new RuntimeException('Sesi tidak ditemukan.');
        }
        $this->cekKelas($sesi['class_id'], $pengguna);
        $model->tutup($sesi['id']);
        flash('Sesi ditutup.');
        redirect('sessions');
    }

    private function cekKelas($id, $pengguna)
    {
        $kelas = $this->model('Kelas_model')->getById($id);
        if (!$kelas || ($pengguna['role'] === 'dosen' && $kelas['lecturer_id'] != $pengguna['id'])) {
            throw new RuntimeException('Kelas tidak tersedia untuk akun Anda.');
        }
        return $kelas;
    }
}
