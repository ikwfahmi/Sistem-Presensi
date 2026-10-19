<?php

// Kelas induk semua controller, mengikuti pola PHP MVC WPU.
class Controller
{
    public function view($view, $data = [])
    {
        require __DIR__ . '/../views/' . $view . '.php';
    }

    public function model($model)
    {
        require_once __DIR__ . '/../models/' . $model . '.php';
        return new $model;
    }

    // Menampilkan header, isi halaman, lalu footer.
    protected function halaman($view, $data = [])
    {
        $data['currentUser'] = user();
        $this->view('layout_top', $data);
        $this->view($view, $data);
        $this->view('layout_bottom', $data);
    }

    protected function hanyaPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Gunakan formulir untuk melakukan tindakan ini.');
        }
    }
}
