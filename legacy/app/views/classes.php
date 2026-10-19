<header class="page-head">
<div>
<p class="eyebrow">PERKULIAHAN</p>
<h1>Kelas & lokasi</h1>
<p class="muted">Gunakan koordinat gedung yang telah diverifikasi. Radius awal 50 meter.</p>
</div>
</header>
<?php
$edit=null;
 foreach($data['classes'] as $c) if($c['id']==($_GET['edit']??0)) $edit=$c;
?>
<section class="panel">
<h2>
<?=$edit?'Ubah kelas':'Tambah kelas'?>
</h2>
<form method="post" action="<?= url('kelas/simpan') ?>" class="form-grid">
<?=csrf_field()?>
<input type="hidden" name="id" value="<?=$edit['id']??0?>">
<label>Kode kelas<input name="code" required value="<?=e($edit['code']??'')?>">
</label>
<label>Mata kuliah<input name="name" required value="<?=e($edit['name']??'')?>">
</label>
<label>Ruangan / gedung<input name="room" required value="<?=e($edit['room']??'')?>">
</label>
<?php
if($data['currentUser']['role']==='admin'):
?>
<label>Dosen<select name="lecturer_id" required>
<option value="">Pilih dosen</option>
<?php
foreach($data['lecturers'] as $l):
?>
<option value="<?=$l['id']?>" <?=($edit['lecturer_id']??0)==$l['id']?'selected':''?>>
<?=e($l['name'])?>
</option>
<?php
endforeach;
?>
</select>
</label>
<?php
endif;
?>
<label>Latitude<input id="class-lat" name="latitude" type="number" step="any" min="-90" max="90" required value="<?=e($edit['latitude']??'')?>" placeholder="Contoh: -7.95">
</label>
<label>Longitude<input id="class-lng" name="longitude" type="number" step="any" min="-180" max="180" required value="<?=e($edit['longitude']??'')?>" placeholder="Contoh: 112.60">
</label>
<label>Radius (10–500 meter)<input name="radius" type="number" min="10" max="500" required value="<?=e($edit['radius']??50)?>">
</label>
<div class="form-actions">
<button>Simpan kelas</button>
<button type="button" class="secondary" id="class-location">Gunakan lokasi saya</button>
</div>
<p id="class-location-status" class="muted tiny">
</p>
</form>
<?php
if(GOOGLE_MAPS_KEY):
?>
<div id="google-map" style="height:280px;margin-top:20px">
</div>
<script src="https://maps.googleapis.com/maps/api/js?key=<?=e(GOOGLE_MAPS_KEY)?>&amp;callback=initClassMap" defer>
</script>
<?php
endif;
?>
</section>
<section class="panel">
<h2>Daftar kelas</h2>
<?php
if(!$data['classes']):
?>
<p class="empty">Belum ada kelas. Buat akun dosen terlebih dahulu melalui menu Pengguna.</p>
<?php
endif;
?>
<div class="session-grid">
<?php
foreach($data['classes'] as $c):
?>
<article class="session-card">
<span class="course-code">
<?=e($c['code'])?>
</span>
<h3>
<?=e($c['name'])?>
</h3>
<p class="muted tiny">
<?=e($c['lecturer'])?> · <?=e($c['room'])?>
<br>
<?=e($c['latitude'])?>, <?=e($c['longitude'])?> · <?=$c['radius']?> m<br>
<?=$c['students']?> mahasiswa terdaftar</p>
<a href="<?=url('classes',['edit'=>$c['id']])?>">Ubah kelas</a> · <a href="https://www.google.com/maps?q=<?=e($c['latitude'])?>,<?=e($c['longitude'])?>" target="_blank" rel="noopener">Peta ↗</a>
<form method="post" action="<?= url('kelas/daftar') ?>">
<?=csrf_field()?>
<input type="hidden" name="action" value="enroll">
<input type="hidden" name="id" value="<?=$c['id']?>">
<label>Daftarkan mahasiswa<select name="student_id" required>
<option value="">Pilih mahasiswa</option>
<?php
foreach($data['students'] as $st):
?>
<option value="<?=$st['id']?>">
<?=e($st['identifier'].' · '.$st['name'])?>
</option>
<?php
endforeach;
?>
</select>
</label>
<button class="secondary">Daftarkan</button>
</form>
<details>
<summary>Peserta kelas</summary>
<?php
foreach($data['students'] as $st): if (!array_filter($data['enrollments'], fn($en)=>$en['class_id']==$c['id'] && $en['student_id']==$st['id'])) continue;
?>
<p class="tiny">
<?=e($st['identifier'].' · '.$st['name'])?>
</p>
<?php
endforeach;
?>
</details>
</article>
<?php
endforeach;
?>
</div>
</section>
