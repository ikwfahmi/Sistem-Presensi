<header class="page-head">
<div>
<p class="eyebrow">AKUN</p>
<h1>Ubah kata sandi</h1>
</div>
</header>
<section class="panel narrow">
<form method="post">
<?=csrf_field()?>
<label>Kata sandi lama<input name="old_password" type="password" required autocomplete="current-password">
</label>
<label>Kata sandi baru<input name="password" type="password" required minlength="10" autocomplete="new-password">
</label>
<button>Simpan kata sandi</button>
</form>
</section>
