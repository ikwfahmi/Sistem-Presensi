<?php
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
// Menghubungkan nama menu lama dengan controller/method MVC.
function route_path($route, $post = false)
{
    $routes = [
        'dashboard' => 'home/index', 'login' => 'auth/login', 'setup' => 'auth/setup',
        'logout' => 'auth/keluar', 'password' => 'auth/password',
        'users' => 'pengguna/index', 'classes' => 'kelas/index',
        'sessions' => 'sesi/index', 'reports' => 'laporan/index',
        'history' => 'laporan/index', 'security' => 'keamanan/index',
        'live' => 'home/live', 'challenge' => 'presensi/challenge',
        'attend' => 'presensi/simpan', 'export' => 'laporan/excel', 'print' => 'laporan/cetak'
    ];
    if ($post) {
        $routes['login'] = 'auth/masuk';
        $routes['users'] = 'pengguna/tambah';
        $routes['classes'] = ($_POST['action'] ?? '') === 'enroll' ? 'kelas/daftar' : 'kelas/simpan';
        $routes['sessions'] = ($_POST['action'] ?? '') === 'close' ? 'sesi/tutup' : 'sesi/tambah';
    }
    return $routes[$route] ?? $route;
}
function url(string $route = 'dashboard', array $params = []): string
{
    return BASEURL . '/index.php?' . http_build_query(['url' => route_path($route)] + $params);
}
function current_route()
{
    if (isset($_GET['route'])) return $_GET['route'];
    $controller = strtolower(explode('/', $_GET['url'] ?? 'home/index')[0]);
    $menu = ['home' => 'dashboard', 'kelas' => 'classes', 'sesi' => 'sessions',
        'pengguna' => 'users', 'laporan' => user()['role'] === 'mahasiswa' ? 'history' : 'reports',
        'keamanan' => 'security', 'auth' => 'password'];
    return $menu[$controller] ?? '';
}
function redirect(string $route = 'dashboard'): never { header('Location: ' . url($route)); exit; }
function flash(string $message, string $type = 'success'): void { Flasher::setFlash($message, $type); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf()).'">'; }
function user(): ?array { return isset($_SESSION['uid']) ? (new User_model)->getById($_SESSION['uid']) : null; }
function require_roles(array $roles): array { $u=user(); if (!$u) redirect('login'); if (!in_array($u['role'],$roles,true)) { http_response_code(403); exit('Akses ditolak.'); } return $u; }
function json_response(array $data, int $status = 200): never { http_response_code($status); header('Content-Type: application/json'); echo json_encode($data); exit; }
function fmt(string $date): string { return date('d M Y, H:i', strtotime($date)); }
