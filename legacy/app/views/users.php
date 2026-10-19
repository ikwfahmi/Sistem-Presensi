<header class="page-head">
<div>
<p class="eyebrow">ADMINISTRASI</p>
<h1>Pengguna</h1>
<p class="muted">Kelola akses mahasiswa, dosen, dan biro akademik.</p>
</div>
</header>
<div class="columns">
<section class="panel">
<h2>Tambah pengguna</h2>
<form method="post" action="<?= url('pengguna/tambah') ?>">
<?=csrf_field()?>
<label>Nama lengkap<input name="name" required maxlength="120">
</label>
<label>NIM / NIP / Identitas<input name="identifier" required maxlength="80">
</label>
<label>Peran<select name="role">
<?php
foreach(['mahasiswa','dosen','baak','admin'] as $role):
?>
<option>
<?=e($role)?>
</option>
<?php
endforeach;
?>
</select>
</label>
<label>Kata sandi awal<input name="password" type="password" required minlength="10" autocomplete="new-password">
</label>
<button>Simpan pengguna</button>
</form>
</section>
<section class="panel">
<h2>Daftar akun</h2>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Nama</th>
<th>Identitas</th>
<th>Peran</th>
</tr>
</thead>
<tbody>
<?php
foreach($data['users'] as $u):
?>
<tr>
<td>
<?=e($u['name'])?>
</td>
<td>
<?=e($u['identifier'])?>
</td>
<td>
<span class="badge neutral">
<?=e($u['role'])?>
</span>
</td>
</tr>
<?php
endforeach;
?>
</tbody>
</table>
</div>
</section>
</div>
