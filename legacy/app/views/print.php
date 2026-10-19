<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Rekap Kehadiran</title>
<link rel="stylesheet" href="<?= BASEURL ?>/css/app.css">
<style>body{background:white;padding:30px}table{font-size:11px}.panel{border:0;padding:0}@media print{.toolbar{display:none}body{padding:0}thead{display:table-header-group}tr{break-inside:avoid}@page{size:A4 landscape;margin:15mm}}</style>
</head>
<body>
<div class="toolbar">
<button onclick="window.print()">Cetak / Simpan sebagai PDF</button>
</div>
<h1>Rekap Kehadiran Perkuliahan</h1>
<p>GeoPresensi · Dicetak oleh <?=e($data['u']['name'])?> · <?=date('d M Y H:i')?> WIB</p>
<p>
<?=count($data['rows'])?> data kehadiran. Filter kelas: <?=e($_GET['class_id']??'Semua')?> · Periode: <?=e($_GET['from']??'Semua')?> – <?=e($_GET['to']??'Semua')?>
</p>
<?php
require __DIR__.'/records_table.php';
?>
</body>
</html>
