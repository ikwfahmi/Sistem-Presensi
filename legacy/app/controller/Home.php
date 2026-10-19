<?php

class Home extends Controller
{
    public function index()
    {
        $pengguna = require_roles(['admin', 'baak', 'dosen', 'mahasiswa']);
        $data['classes'] = $this->model('Kelas_model')->getKelasPengguna($pengguna);
        $data['sessions'] = $this->model('Sesi_model')->getSesiPengguna($pengguna);
        $data['rows'] = $this->model('Laporan_model')->getRekap($pengguna);

        // View tidak menjalankan query database.
        $data['sudahPresensi'] = [];
        foreach ($data['rows'] as $baris) {
            if ($baris['status'] === 'Hadir') {
                $data['sudahPresensi'][$baris['session_id']] = true;
            }
        }
        $this->halaman('dashboard', $data);
    }

    public function live()
    {
        $pengguna = require_roles(['admin', 'baak', 'dosen', 'mahasiswa']);
        $rows = $this->model('Laporan_model')->getRekap($pengguna);
        $jumlahHadir = 0;
        foreach ($rows as $row) {
            if ($row['status'] === 'Hadir') {
                $jumlahHadir++;
            }
        }
        json_response([
            'ok' => true,
            'count' => count($rows),
            'present' => $jumlahHadir,
            'records' => array_slice($rows, 0, 10),
            'serverTime' => date('c')
        ]);
    }
}
