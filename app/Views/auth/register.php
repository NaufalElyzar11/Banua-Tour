<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<span class="eyebrow">MULAI RENCANA PERJALANAN</span>
<h1>Buat akun Anda</h1>
<p class="muted">Simpan destinasi favorit dan kelola pesanan di satu tempat. Preferensi wisata bisa diisi nanti.</p>
<form action="<?= base_url('auth/doRegister') ?>" method="post" class="auth-form">
    <?= csrf_field() ?>
    <div class="field"><label for="nama">Nama lengkap</label><input type="text" id="nama" name="nama" value="<?= esc(old('nama') ?? '', 'attr') ?>" required minlength="2" maxlength="100" autocomplete="name"></div>
    <div class="field"><label for="username">Nama pengguna</label><input type="text" id="username" name="username" value="<?= esc(old('username') ?? '', 'attr') ?>" required minlength="3" maxlength="50" pattern="[A-Za-z0-9_-]+" autocomplete="username" aria-describedby="username-help"><small id="username-help">Gunakan huruf, angka, garis bawah, atau tanda hubung.</small></div>
    <div class="field"><label for="email">Email</label><input type="email" id="email" name="email" value="<?= esc(old('email') ?? '', 'attr') ?>" required maxlength="254" autocomplete="email"></div>
    <div class="field"><label for="password">Kata sandi</label><div class="password-field"><input type="password" id="password" name="password" required minlength="12" maxlength="72" autocomplete="new-password" aria-describedby="password-help"><button type="button" data-password-toggle="password" aria-controls="password" aria-pressed="false">Tampilkan</button></div><small id="password-help">Minimal 12 karakter. Gunakan frasa yang panjang dan mudah Anda ingat.</small></div>
    <div class="field"><label for="confirm_password">Ulangi kata sandi</label><input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password"></div>
    <button class="primary-button" type="submit">Buat akun</button>
</form>
<p class="auth-switch">Sudah punya akun? <a href="<?= base_url('auth/login') ?>">Masuk</a></p>
<?= $this->endSection() ?>
