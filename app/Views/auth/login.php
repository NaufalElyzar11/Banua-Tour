<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>
<span class="eyebrow">SELAMAT DATANG KEMBALI</span>
<h1>Masuk ke BanuaTour</h1>
<p class="muted">Lanjutkan rencana perjalanan dan lihat destinasi favorit Anda.</p>
<form action="<?= base_url('auth/doLogin') ?>" method="post" class="auth-form">
    <?= csrf_field() ?>
    <div class="field"><label for="email">Email atau nama pengguna</label><input type="text" id="email" name="email" value="<?= esc(old('email') ?? '', 'attr') ?>" required maxlength="254" autocomplete="username" autofocus></div>
    <div class="field"><label for="password">Kata sandi</label><div class="password-field"><input type="password" id="password" name="password" required autocomplete="current-password"><button type="button" data-password-toggle="password" aria-controls="password" aria-pressed="false">Tampilkan</button></div></div>
    <button class="primary-button" type="submit">Masuk</button>
</form>
<p class="auth-switch">Belum punya akun? <a href="<?= base_url('auth/register') ?>">Daftar</a></p>
<?= $this->endSection() ?>
