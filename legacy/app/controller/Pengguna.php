<?php

class Pengguna extends Controller
{
    public function index()
    {
        require_roles(['admin']);
        $data['users'] = $this->model('User_model')->getSemuaPengguna();
        $this->halaman('users', $data);
    }

    public function tambah()
    {
        $this->hanyaPost();
        require_roles(['admin']);
        if (!trim($_POST['name'] ?? '') || !trim($_POST['identifier'] ?? '') ||
            strlen($_POST['password'] ?? '') < 10) {
            throw new RuntimeException('Lengkapi nama, identitas, dan kata sandi minimal 10 karakter.');
        }
        $peran = $_POST['role'] ?? '';
        if (!in_array($peran, ['mahasiswa', 'dosen', 'baak', 'admin'], true)) {
            throw new RuntimeException('Peran tidak valid.');
        }
        $model = $this->model('User_model');
        if ($model->getByIdentitas(trim($_POST['identifier']))) {
            throw new RuntimeException('Identitas sudah digunakan.');
        }
        $model->tambah($_POST, $peran);
        flash('Pengguna ditambahkan.');
        redirect('users');
    }
}
