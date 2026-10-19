<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Jakarta');
session_save_path(getenv('PRESENSI_SESSION_PATH') ?: __DIR__ . '/config');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
session_start();
require __DIR__ . '/config/config.php';
require __DIR__ . '/core/Database.php';
require __DIR__ . '/core/Controller.php';
require __DIR__ . '/core/App.php';
require __DIR__ . '/core/Flasher.php';
require __DIR__ . '/core/Helpers.php';
require __DIR__ . '/models/User_model.php';
