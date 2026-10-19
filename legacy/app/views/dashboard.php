<?php
$student=$data['currentUser']['role']==='mahasiswa';
 $active=array_filter($data['sessions'],fn($s)=>!$s['closed'] && strtotime($s['starts_at'])<=time() && strtotime($s['ends_at'])>=time());
 $present=count(array_filter($data['rows'],fn($r)=>$r['status']==='Hadir'));
?>
<header class="page-head">
<div>
<p class="eyebrow">OVERVIEW / <?=e(strtoupper($data['currentUser']['role']))?>
</p>
<h1>Selamat datang, <?=e(explode(' ',$data['currentUser']['name'])[0])?>.</h1>
<p class="muted">
<?=$student?'Pastikan Anda berada di lokasi kelas sebelum melakukan presensi.':'Pantau perkuliahan dan kehadiran dalam satu tempat.'?>
</p>
</div>
<span class="date">
<?=date('d M Y')?> · WIB</span>
</header>
<div class="stats">
<article>
<span>Kelas Anda</span>
<strong>
<?=count($data['classes'])?>
</strong>
<small>Kelas yang dapat diakses</small>
</article>
<article>
<span>Sesi aktif</span>
<strong>
<?=count($active)?>
</strong>
<small>Terbuka untuk presensi</small>
</article>
<article>
<span>Kehadiran tercatat</span>
<strong id="present-count">
<?=$present?>
</strong>
<small>
<?=$student?'Presensi berhasil Anda':'Dari seluruh sesi kelas'?>
</small>
</article>
<article>
<span>Total pertemuan</span>
<strong>
<?=count($data['sessions'])?>
</strong>
<small>Sesi yang telah dijadwalkan</small>
</article>
</div>
<section class="panel">
<div class="section-head">
<div>
<h2>
<?=$student?'Presensi perkuliahan':'Aktivitas perkuliahan'?>
</h2>
<p class="muted">Lokasi kelas dan waktu sesi mengikuti pengaturan dosen.</p>
</div>
<span class="badge">● Live</span>
</div>
<?php
if(!$data['sessions']):
?>
<div class="empty">Belum ada sesi perkuliahan. <?=$student?'Hubungi dosen atau admin untuk jadwal dan pendaftaran kelas.':'Tambahkan kelas dan jadwalkan sesi presensi.'?>
</div>
<?php
endif;
?>
<div class="session-grid">
<?php
foreach($data['sessions'] as $s): $now=time();
 $isActive=!$s['closed'] && $now>=strtotime($s['starts_at']) && $now<=strtotime($s['ends_at']);
 $upcoming=!$s['closed'] && $now<strtotime($s['starts_at']);
 $done=$student && isset($data['sudahPresensi'][$s['id']]);
?>
<article class="session-card" <?php
if($upcoming && $student):
?>data-reminder="<?=e($s['starts_at'])?>" data-title="<?=e($s['class_name'])?>"<?php
endif;
?>>
<div class="section-head">
<span class="course-code">
<?=e($s['code'])?>
</span>
<span class="badge <?=$isActive?'':'neutral'?>">
<?=$done?'Sudah hadir':($isActive?'Sesi aktif':($upcoming?'Terjadwal':'Selesai'))?>
</span>
</div>
<h3>
<?=e($s['class_name'])?>
</h3>
<p>
<?=e($s['title'])?>
</p>
<p class="muted tiny">
<?=e($s['lecturer'])?> · <?=e($s['room'])?>
<br>
<?=fmt($s['starts_at'])?> – <?=date('H:i',strtotime($s['ends_at']))?> WIB<br>Radius <?=e($s['radius'])?> m</p>
<?php
if($student && $isActive && !$done):
?>
<div class="gps-card" data-session="<?=$s['id']?>" data-lat="<?=e($s['latitude'])?>" data-lng="<?=e($s['longitude'])?>" data-radius="<?=$s['radius']?>">
<p class="gps-status muted tiny" role="status">Ambil lokasi untuk memeriksa jarak ke kelas.</p>
<button class="locate secondary" type="button">◎ Periksa lokasi saya</button>
<button class="attend" type="button" disabled>Presensi sekarang</button>
<a class="map-link tiny" href="https://www.google.com/maps?q=<?=e($s['latitude'])?>,<?=e($s['longitude'])?>" target="_blank" rel="noopener">Lihat titik kelas ↗</a>
</div>
<?php
else:
?>
<div class="card-footer">
<?=$student?($done?'✓ Kehadiran tersimpan':($upcoming?'Presensi dibuka sesuai jadwal':'Sesi telah berakhir')):$s['present'].' / '.$s['students'].' mahasiswa hadir'?>
</div>
<?php
endif;
?>
</article>
<?php
endforeach;
?>
</div>
</section>
<?php
if($student):
?>
<div class="notice">Lokasi hanya diminta saat Anda memeriksa lokasi dan melakukan presensi. GPS harus aktif dan koneksi internet diperlukan. <button type="button" class="subtle" id="notifications">Aktifkan pengingat</button>
<span id="notification-status" class="tiny">
</span>
</div>
<?php
else:
?>
<section class="panel">
<div class="section-head">
<h2>Monitor kehadiran</h2>
<span class="muted tiny">Diperbarui setiap 10 detik</span>
</div>
<div id="live-feed" class="muted">Memuat aktivitas…</div>
</section>
<?php
endif;
?>
