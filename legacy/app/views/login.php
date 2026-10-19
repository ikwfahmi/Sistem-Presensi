<div class="auth-card">
<span class="logo">G</span>
<p class="eyebrow">SELAMAT DATANG DI GEOPRESENSI</p>
<h1>Kehadiran yang<br>lebih terpercaya.</h1>
<p class="muted">Masuk untuk mengelola perkuliahan dan melakukan presensi berbasis lokasi.</p>
<form method="post" action="<?= url('auth/masuk') ?>">
<?=csrf_field()?>
<label>NIM / NIP / Identitas akun<input name="identifier" required autocomplete="username" placeholder="Masukkan identitas Anda">
</label>
<label>Kata sandi<input name="password" type="password" required autocomplete="current-password" placeholder="Masukkan kata sandi">
</label>
<button>Masuk ke akun →</button>
</form>
<p class="muted tiny">Belum memiliki akun? Hubungi administrator akademik.</p>
</div>
