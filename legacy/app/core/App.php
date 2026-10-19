<?php

// URL: index.php?url=kelas/index atau /kelas/index (Apache rewrite).
class App
{
    protected $controller = 'Home';
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        $url = $this->parseURL();
        $namaController = ucfirst($url[0] ?? 'home');
        $namaMethod = $url[1] ?? 'index';

        // Batasi nama file supaya URL tidak dapat membuka file sembarang.
        if (!preg_match('/^[A-Za-z]+$/', $namaController) ||
            !preg_match('/^[A-Za-z]+$/', $namaMethod)) {
            $this->tidakDitemukan();
            return;
        }

        $file = __DIR__ . '/../controller/' . $namaController . '.php';
        if (!file_exists($file)) {
            $this->tidakDitemukan();
            return;
        }

        require_once $file;
        $this->controller = new $namaController;

        // Hanya method public milik controller fitur yang boleh diakses.
        if (!method_exists($this->controller, $namaMethod)) {
            $this->tidakDitemukan();
            return;
        }
        $method = new ReflectionMethod($this->controller, $namaMethod);
        if (!$method->isPublic() || $method->getDeclaringClass()->getName() === 'Controller') {
            $this->tidakDitemukan();
            return;
        }

        $this->method = $namaMethod;
        $this->params = array_slice($url, 2);
        if (count($this->params) > $method->getNumberOfParameters()) {
            $this->tidakDitemukan();
            return;
        }

        $pengguna = (new User_model)->getPenggunaPertama();
        if (!$pengguna && !($namaController === 'Auth' && $namaMethod === 'setup')) {
            redirect('setup');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
            !hash_equals(csrf(), (string) ($_POST['csrf'] ?? ''))) {
            http_response_code(419);
            exit('Sesi formulir kedaluwarsa. Muat ulang halaman.');
        }

        try {
            call_user_func_array([$this->controller, $this->method], $this->params);
        } catch (PDOException $error) {
            // Kesalahan SQL dicatat oleh index.php, tidak ditampilkan sebagai flash.
            throw $error;
        } catch (RuntimeException $error) {
            if ($namaController === 'Presensi' && $namaMethod === 'simpan') {
                json_response(['ok' => false, 'message' => $error->getMessage()], 422);
            }
            flash($error->getMessage(), 'error');
            $tujuan = [
                'Auth' => $namaMethod === 'setup' ? 'setup' : ($namaMethod === 'password' ? 'password' : 'login'),
                'Kelas' => 'classes', 'Sesi' => 'sessions', 'Pengguna' => 'users',
                'Laporan' => 'reports', 'Keamanan' => 'security', 'Home' => 'dashboard'
            ];
            redirect($tujuan[$namaController] ?? 'dashboard');
        }
    }

    public function parseURL()
    {
        // URL lama tetap dikenali agar bookmark dan tautan lama masih bekerja.
        $path = $_GET['url'] ?? route_path($_GET['route'] ?? 'dashboard', $_SERVER['REQUEST_METHOD'] === 'POST');
        return explode('/', trim($path, '/'));
    }

    protected function tidakDitemukan()
    {
        http_response_code(404);
        $controller = new Controller;
        $data = ['currentUser' => user()];
        $controller->view('layout_top', $data);
        $controller->view('notfound', $data);
        $controller->view('layout_bottom', $data);
    }
}
