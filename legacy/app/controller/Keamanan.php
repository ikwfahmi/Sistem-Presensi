<?php

class Keamanan extends Controller
{
    public function index()
    {
        require_roles(['admin']);
        $data['attempts'] = $this->model('Presensi_model')->getLogPenolakan();
        $this->halaman('security', $data);
    }
}
