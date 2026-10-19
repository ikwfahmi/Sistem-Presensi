<?php
require __DIR__.'/../app/init.php';
try { $app = new App; }
catch (Throwable $error) { error_log((string)$error); http_response_code(500); echo 'Terjadi kesalahan sistem. Periksa log PHP dan konfigurasi database.'; }
