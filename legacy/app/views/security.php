<header class="page-head">
<div>
<p class="eyebrow">VALIDASI</p>
<h1>Log percobaan presensi</h1>
<p class="muted">200 percobaan ditolak terakhir. Log ini bukan bukti pasti penggunaan Fake GPS.</p>
</div>
</header>
<section class="panel">
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Waktu</th>
<th>Mahasiswa</th>
<th>Sesi</th>
<th>Alasan penolakan</th>
</tr>
</thead>
<tbody>
<?php
foreach($data['attempts'] as $a):
?>
<tr>
<td>
<?=fmt($a['occurred_at'])?>
</td>
<td>
<?=e($a['name'])?>
<br>
<?=e($a['identifier'])?>
</td>
<td>
<?=e($a['session_id'])?>
</td>
<td>
<?=e($a['reason'])?>
</td>
</tr>
<?php
endforeach;
?>
</tbody>
</table>
<?php
if(!$data['attempts']):
?>
<p class="empty">Belum ada percobaan yang ditolak.</p>
<?php
endif;
?>
</div>
</section>
