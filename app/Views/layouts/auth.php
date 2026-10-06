<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= esc(csrf_hash(), 'attr') ?>">
    <title><?= esc($title ?? 'Akun') ?> · BanuaTour</title>
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <script src="<?= base_url('js/app.js') ?>" defer></script>
</head>
<body class="site auth-page">
    <a class="skip-link" href="#main-content">Lewati ke konten</a>
    <header class="auth-topbar"><a class="brand" href="<?= base_url('/') ?>"><span class="brand-symbol" aria-hidden="true"><?= view('partials/brand_mark') ?></span>BanuaTour</a><a href="<?= base_url('destinasi') ?>">Jelajahi destinasi &rarr;</a></header>
    <main class="auth-shell" id="main-content">
        <aside class="auth-story">
            <span class="eyebrow">KENALI BANUA LEBIH DEKAT</span>
            <h2>Perjalanan kecil.<br>Cerita yang berarti.</h2>
            <p>Dari tepian sungai hingga pegunungan, temukan sisi Kalimantan Selatan yang ingin Anda jelajahi.</p>
            <img src="<?= base_url('images/destination-placeholder.svg') ?>" alt="" width="800" height="600">
            <a href="<?= base_url('destinasi') ?>">Lihat destinasi tanpa membuat akun &rarr;</a>
        </aside>
        <section class="auth-card">
            <?php if ($message = ($error ?? session()->getFlashdata('error'))): ?><div class="notice notice-error" role="alert"><?= esc($message) ?></div><?php endif; ?>
            <?php if ($message = session()->getFlashdata('success')): ?><div class="notice notice-success" role="status"><?= esc($message) ?></div><?php endif; ?>
            <?php if ($errors = session()->getFlashdata('errors')): ?><div class="notice notice-error" role="alert"><p>Periksa kembali isian Anda:</p><ul><?php foreach ($errors as $message): ?><li><?= esc($message) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
            <?= $this->renderSection('content') ?>
        </section>
    </main>
</body>
</html>
