<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= esc(csrf_hash(), 'attr') ?>">
    <meta name="description" content="Jelajahi destinasi wisata Kalimantan Selatan, simpan favorit, dan rencanakan kunjungan Anda bersama BanuaTour.">
    <title><?= esc($title ?? 'Jelajahi Kalimantan Selatan') ?> · BanuaTour</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <?= $this->renderSection('styles') ?>
    <script src="<?= base_url('js/app.js') ?>" defer></script>
</head>
<body class="site <?= esc($pageClass ?? '', 'attr') ?>">
    <a class="skip-link" href="#main-content">Lewati ke konten</a>
    <?php $path = trim(service('uri')->getPath(), '/'); ?>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="<?= base_url(session('isLoggedIn') ? 'user_home' : '/') ?>" aria-label="BanuaTour, beranda">
                <span class="brand-symbol" aria-hidden="true"><?= view('partials/brand_mark') ?></span><span>Banua<span class="brand-accent">Tour</span></span>
            </a>
            <button class="menu-toggle" data-menu-toggle aria-expanded="false" aria-controls="site-navigation" type="button"><i class="fas fa-bars" aria-hidden="true"></i> Menu</button>
            <nav id="site-navigation" class="site-nav" aria-label="Navigasi utama">
                <a href="<?= base_url(session('isLoggedIn') ? 'user_home' : '/') ?>" <?= $path === '' || str_ends_with($path, 'user_home') ? 'aria-current="page"' : '' ?>>Beranda</a>
                <a href="<?= base_url('destinasi') ?>" <?= str_contains($path, 'destinasi') ? 'aria-current="page"' : '' ?>>Destinasi</a>
                <?php if (session('isLoggedIn')): ?>
                    <a href="<?= base_url('wishlist') ?>" <?= str_ends_with($path, 'wishlist') ? 'aria-current="page"' : '' ?>>Favorit</a>
                    <a href="<?= base_url('riwayat') ?>" <?= str_ends_with($path, 'riwayat') ? 'aria-current="page"' : '' ?>>Pesanan</a>
                    <a href="<?= base_url('profile') ?>" <?= str_ends_with($path, 'profile') ? 'aria-current="page"' : '' ?>>Profil</a>
                    <?php if (session('role') === 'admin'): ?><a href="<?= base_url('admin') ?>">Panel admin</a><?php endif; ?>
                    <form action="<?= base_url('auth/logout') ?>" method="post" class="logout-form"><?= csrf_field() ?><button type="submit">Keluar</button></form>
                <?php else: ?>
                    <a href="<?= base_url('auth/login') ?>">Masuk</a>
                    <a class="nav-cta" href="<?= base_url('auth/register') ?>">Daftar</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main id="main-content" class="main-content" tabindex="-1"><?= $this->renderSection('content') ?></main>
    <footer class="site-footer">
        <div><a class="brand" href="<?= base_url('/') ?>">BanuaTour</a><p>Kenali tempatnya. Rencanakan perjalanannya.</p></div>
        <p>Wisata Kalimantan Selatan<br><small>&copy; <?= date('Y') ?> BanuaTour</small></p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
