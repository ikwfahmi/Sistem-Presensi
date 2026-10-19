<?php

class Auth extends Controller
{
    public function setup()
    {
        $model = $this->model('User_model');
        if ($model->getPenggunaPertama()) {
            redirect('login');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validasiPengguna($_POST);
            $model->tambah($_POST, 'admin');
            flash('Akun administrator berhasil dibuat. Silakan masuk.');
            redirect('login');
        }
        $this->halaman('setup');
    }

    public function login()
    {
        if (user()) {
            redirect('dashboard');
        }
        $this->halaman('login');
    }

    public function masuk()
    {
        $this->hanyaPost();
        $model = $this->model('User_model');
        $identitas = trim($_POST['identifier'] ?? '');
        $model->bersihkanLoginGagal(time() - 900);
        $percobaan = $model->jumlahLoginGagal($identitas);
        if ($percobaan['total'] >= 5) {
            throw new RuntimeException('Terlalu banyak percobaan. Coba lagi dalam 15 menit.');
        }
        $pengguna = $model->getByIdentitas($identitas);
        if (!$pengguna || !password_verify($_POST['password'] ?? '', $pengguna['password'])) {
            $model->catatLoginGagal($identitas);
            throw new RuntimeException('Identitas atau kata sandi salah.');
        }
        $model->hapusLoginGagal($identitas);
        session_regenerate_id(true);
        $_SESSION['uid'] = $pengguna['id'];
        unset($_SESSION['csrf']);
        redirect('dashboard');
    }

    public function keluar()
    {
        $this->hanyaPost();
        require_roles(['admin', 'baak', 'dosen', 'mahasiswa']);
        $_SESSION = [];
        session_destroy();
        redirect('login');
    }

    public function password()
    {
        $pengguna = require_roles(['admin', 'baak', 'dosen', 'mahasiswa']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = $this->model('User_model');
            $record = $model->getPassword($pengguna['id']);
            if (!password_verify($_POST['old_password'] ?? '', $record['password'])) {
                throw new RuntimeException('Kata sandi lama salah.');
            }
            if (strlen($_POST['password'] ?? '') < 10) {
                throw new RuntimeException('Kata sandi minimal 10 karakter.');
            }
            $model->ubahPassword($pengguna['id'], $_POST['password']);
            session_regenerate_id(true);
            flash('Kata sandi diperbarui.');
            redirect('password');
        }
        $this->halaman('password');
    }

    private function validasiPengguna($data)
    {
        if (!trim($data['name'] ?? '') || !trim($data['identifier'] ?? '') ||
            strlen($data['password'] ?? '') < 10) {
            throw new RuntimeException('Lengkapi nama, identitas, dan kata sandi minimal 10 karakter.');
        }
    }
}
