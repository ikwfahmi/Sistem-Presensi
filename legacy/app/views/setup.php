<div class="auth-card">
<span class="logo">G</span>
<p class="eyebrow">PENGATURAN AWAL</p>
<h1>Siapkan GeoPresensi.</h1>
<p class="muted">Buat administrator pertama. Setelah itu, tambahkan dosen, mahasiswa, dan kelas dengan koordinat gedung yang benar.</p>
<form method="post">
<?=csrf_field()?>
<label>Nama administrator<input name="name" required maxlength="120">
</label>
<label>Identitas akun<input name="identifier" required maxlength="80" autocomplete="username">
</label>
<label>Kata sandi (minimal 10 karakter)<input name="password" type="password" required minlength="10" autocomplete="new-password">
</label>
<button>Buat akun administrator</button>
</form>
</div>
