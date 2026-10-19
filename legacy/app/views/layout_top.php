<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?= e(csrf()) ?>">
<meta name="app-url" content="<?= e(BASEURL) ?>">
<title>GeoPresensi • Kampus</title>
<link rel="stylesheet" href="<?= BASEURL ?>/css/app.css">
<script src="<?= BASEURL ?>/js/app.js" defer>
</script>
</head>
<body>
<?php
if ($data['currentUser']):
?>
<aside class="sidebar">
<a class="brand" href="<?=url()?>">
<span class="logo">G</span> GeoPresensi</a>
<small>KAMPUS • SISTEM KEHADIRAN</small>
<nav>
<?php
$links=['dashboard'=>'Dashboard'];
 if(in_array($data['currentUser']['role'],['admin','dosen'])) $links+=['classes'=>'Kelas & lokasi','sessions'=>'Sesi presensi'];
 if($data['currentUser']['role']==='admin') $links+=['users'=>'Pengguna'];
 $links+=[$data['currentUser']['role']==='mahasiswa'?'history':'reports'=>'Rekap kehadiran'];
 if($data['currentUser']['role']==='admin') $links+=['security'=>'Log validasi'];
 $links+=['password'=>'Kata sandi'];
 foreach($links as $r=>$label):
?>
<a class="<?=current_route()===$r?'selected':''?>" href="<?=url($r)?>">
<?=e($label)?>
</a>
<?php
endforeach;
?>
</nav>
<div class="profile">
<strong>
<?=e($data['currentUser']['name'])?>
</strong>
<small>
<?=e(ucfirst($data['currentUser']['role']))?>
</small>
<form method="post" action="<?=url('logout')?>">
<?=csrf_field()?>
<button class="subtle">Keluar dari akun</button>
</form>
</div>
</aside>
<?php
endif;
?>
<main class="<?=$data['currentUser']?'main':'auth'?>">
<?php
Flasher::flash();
?>
