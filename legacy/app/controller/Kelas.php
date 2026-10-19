<?php

class Kelas extends Controller
{
    public function index()
    {
        $pengguna = require_roles(['admin', 'dosen']);
        $data['classes'] = $this->model('Kelas_model')->getKelasPengguna($pengguna);
        $data['lecturers'] = $this->model('User_model')->getByPeran('dosen');
        $data['students'] = $this->model('User_model')->getByPeran('mahasiswa');
        $data['enrollments'] = $this->model('Kelas_model')->getPeserta();
        $this->halaman('classes', $data);
    }

    public function simpan()
    {
        $this->hanyaPost();
        $pengguna = require_roles(['admin', 'dosen']);
        $model = $this->model('Kelas_model');
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $this->cekPemilik($id, $pengguna);
        }
        $data = [
            'id' => $id,
            'name' => trim($_POST['name'] ?? ''),
            'code' => trim($_POST['code'] ?? ''),
            'room' => trim($_POST['room'] ?? ''),
            'latitude' => $this->angka('latitude', -90, 90),
            'longitude' => $this->angka('longitude', -180, 180),
            'radius' => $this->angka('radius', 10, 500),
            'lecturer_id' => $pengguna['role'] === 'dosen' ? $pengguna['id'] : (int) ($_POST['lecturer_id'] ?? 0)
        ];
        if (!$data['name'] || !$data['code'] || !$data['room']) {
            throw new RuntimeException('Lengkapi nama, kode, dan ruangan kelas.');
        }
        $dosen = $this->model('User_model')->getById($data['lecturer_id']);
        if (!$dosen || $dosen['role'] !== 'dosen') {
            throw new RuntimeException('Pilih dosen pengampu.');
        }
        if ($model->getByKode($data['code'], $id)) {
            throw new RuntimeException('Kode kelas sudah digunakan.');
        }
        if ($id) {
            $model->ubah($data);
        } else {
            $model->tambah($data);
        }
        flash('Data kelas disimpan. Perubahan lokasi berlaku untuk sesi baru.');
        redirect('classes');
    }

    public function daftar()
    {
        $this->hanyaPost();
        $pengguna = require_roles(['admin', 'dosen']);
        $idKelas = (int) ($_POST['id'] ?? 0);
        $idMahasiswa = (int) ($_POST['student_id'] ?? 0);
        $this->cekPemilik($idKelas, $pengguna);
        $mahasiswa = $this->model('User_model')->getById($idMahasiswa);
        if (!$mahasiswa || $mahasiswa['role'] !== 'mahasiswa') {
            throw new RuntimeException('Mahasiswa tidak ditemukan.');
        }
        $this->model('Kelas_model')->daftarkanMahasiswa($idKelas, $idMahasiswa);
        flash('Mahasiswa terdaftar di kelas.');
        redirect('classes');
    }

    private function cekPemilik($id, $pengguna)
    {
        $kelas = $this->model('Kelas_model')->getById($id);
        if (!$kelas || ($pengguna['role'] === 'dosen' && $kelas['lecturer_id'] != $pengguna['id'])) {
            throw new RuntimeException('Kelas tidak tersedia untuk akun Anda.');
        }
        return $kelas;
    }

    private function angka($nama, $min, $max)
    {
        $nilai = $_POST[$nama] ?? '';
        if (!is_numeric($nilai) || !is_finite((float) $nilai) || $nilai < $min || $nilai > $max) {
            throw new RuntimeException('Nilai ' . $nama . ' tidak valid.');
        }
        return (float) $nilai;
    }
}
