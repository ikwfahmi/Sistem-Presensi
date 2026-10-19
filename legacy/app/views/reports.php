<header class="page-head">
<div>
<p class="eyebrow">KEHADIRAN</p>
<h1>
<?=$data['currentUser']['role']==='mahasiswa'?'Riwayat presensi':'Rekap kehadiran'?>
</h1>
<p class="muted">Termasuk mahasiswa yang belum presensi dan tidak hadir.</p>
</div>
</header>
<section class="panel">
<form method="get" class="filters">
<input type="hidden" name="url" value="laporan/index">
<label>Kelas<select name="class_id">
<option value="">Semua kelas</option>
<?php
foreach($data['classes'] as $c):
?>
<option value="<?=$c['id']?>" <?=($_GET['class_id']??0)==$c['id']?'selected':''?>>
<?=e($c['name'])?>
</option>
<?php
endforeach;
?>
</select>
</label>
<label>Dari tanggal<input type="date" name="from" value="<?=e($_GET['from']??'')?>">
</label>
<label>Sampai tanggal<input type="date" name="to" value="<?=e($_GET['to']??'')?>">
</label>
<button>Terapkan</button>
</form>
<?php
if($data['currentUser']['role']!=='mahasiswa'): $filter=array_intersect_key($_GET,array_flip(['class_id','from','to']));
?>
<div class="toolbar">
<a class="button secondary" href="<?=url('export',$filter)?>">↓ Unduh Excel</a>
<a class="button secondary" target="_blank" href="<?=url('print',$filter)?>">Cetak / Simpan PDF</a>
</div>
<?php
endif;
?>
<p class="muted tiny">
<?=count($data['rows'])?> data · <?=count(array_filter($data['rows'],fn($r)=>$r['status']==='Hadir'))?> hadir</p>
<?php
require __DIR__.'/records_table.php';
?>
</section>
