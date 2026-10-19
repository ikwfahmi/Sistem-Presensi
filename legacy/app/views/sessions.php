<header class="page-head">
<div>
<p class="eyebrow">PERKULIAHAN</p>
<h1>Sesi presensi</h1>
<p class="muted">Jadwalkan pertemuan dan tentukan jendela waktu presensi.</p>
</div>
</header>
<section class="panel">
<h2>Buat sesi</h2>
<form method="post" action="<?= url('sesi/tambah') ?>" class="form-grid">
<?=csrf_field()?>
<label>Kelas<select name="class_id" required>
<option value="">Pilih kelas</option>
<?php
foreach($data['classes'] as $c):
?>
<option value="<?=$c['id']?>">
<?=e($c['code'].' · '.$c['name'])?>
</option>
<?php
endforeach;
?>
</select>
</label>
<label>Judul pertemuan<input name="title" required placeholder="Pertemuan 1 — Pengantar">
</label>
<label>Mulai (WIB)<input type="datetime-local" name="starts_at" required value="<?=date('Y-m-d\TH:i')?>">
</label>
<label>Selesai (WIB)<input type="datetime-local" name="ends_at" required value="<?=date('Y-m-d\TH:i',time()+3600)?>">
</label>
<div>
<button>Buat sesi presensi</button>
</div>
</form>
</section>
<section class="panel">
<h2>Semua sesi</h2>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Kelas / pertemuan</th>
<th>Jadwal</th>
<th>Kehadiran</th>
<th>Status</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>
<?php
foreach($data['sessions'] as $s):
?>
<tr>
<td>
<strong>
<?=e($s['class_name'])?>
</strong>
<br>
<?=e($s['title'])?>
</td>
<td>
<?=fmt($s['starts_at'])?>
<br>s.d. <?=fmt($s['ends_at'])?>
</td>
<td>
<?=$s['present']?> / <?=$s['students']?>
</td>
<td>
<?=$s['closed']?'Ditutup':(strtotime($s['ends_at'])<time()?'Selesai':(strtotime($s['starts_at'])>time()?'Terjadwal':'Aktif'))?>
</td>
<td>
<?php
if(!$s['closed'] && strtotime($s['ends_at'])>=time()):
?>
<form method="post" action="<?= url('sesi/tutup') ?>" data-confirm="Tutup sesi presensi ini? Mahasiswa tidak dapat presensi setelah ditutup.">
<?=csrf_field()?>
<input type="hidden" name="action" value="close">
<input type="hidden" name="id" value="<?=$s['id']?>">
<button class="secondary">Tutup sesi</button>
</form>
<?php
endif;
?>
</td>
</tr>
<?php
endforeach;
?>
</tbody>
</table>
<?php
if(!$data['sessions']):
?>
<p class="empty">Belum ada sesi.</p>
<?php
endif;
?>
</div>
</section>
