<div class="table-wrap">
<table>
<thead>
<tr>
<th>Kelas / pertemuan</th>
<th>Mahasiswa</th>
<th>Status</th>
<th>Waktu presensi</th>
<th>Jarak / akurasi</th>
</tr>
</thead>
<tbody>
<?php
foreach($data['rows'] as $r):
?>
<tr>
<td>
<strong>
<?=e($r['class_name'])?>
</strong>
<br>
<?=e($r['title'])?>
<br>
<span class="muted tiny">
<?=fmt($r['starts_at'])?>
</span>
</td>
<td>
<?=e($r['name'])?>
<br>
<span class="muted tiny">
<?=e($r['identifier'])?>
</span>
</td>
<td>
<span class="badge <?=$r['status']==='Hadir'?'':'neutral'?>">
<?=e($r['status'])?>
</span>
</td>
<td>
<?=$r['recorded_at']?fmt($r['recorded_at']).' WIB':'—'?>
</td>
<td>
<?=$r['distance']!==null?round($r['distance'],1).' m / '.round($r['accuracy'],1).' m':'—'?>
</td>
</tr>
<?php
endforeach;
?>
</tbody>
</table>
<?php
if(!$data['rows']):
?>
<p class="empty">Belum ada data kehadiran untuk pilihan ini.</p>
<?php
endif;
?>
</div>
