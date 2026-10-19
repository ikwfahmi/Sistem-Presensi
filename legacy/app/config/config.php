<?php
// BASEURL otomatis menyesuaikan localhost Laragon atau server development.
define('BASEURL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/'));
// SQLite dipertahankan supaya data aplikasi sebelumnya tidak perlu dipindahkan.
define('DB_PATH', getenv('PRESENSI_DB_PATH') ?: __DIR__ . '/presensi.sqlite');
define('GOOGLE_MAPS_KEY', getenv('GOOGLE_MAPS_KEY') ?: '');
