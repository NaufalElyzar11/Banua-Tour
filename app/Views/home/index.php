<?= $this->extend('layouts/main') ?>
<?= $this->section('styles') ?><link rel="stylesheet" href="<?= base_url('css/home.css') ?>"><?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php $heroDestination ??= $wisataTrending[0] ?? null; ?>
<div class="home-page">
    <section class="banua-hero" aria-labelledby="hero-title">
        <img class="banua-panorama" src="<?= esc($heroDestination ? wisata_image($heroDestination) : base_url('images/destination-placeholder.svg'), 'attr') ?>" alt="<?= esc($heroDestination['nama'] ?? 'Ilustrasi bentang alam Banua', 'attr') ?>" width="1600" height="1000" fetchpriority="high">
        <div class="banua-hero-shade" aria-hidden="true"></div>
        <div class="banua-hero-inner">
            <div class="hero-story">
                <span class="hero-kicker"><span aria-hidden="true"></span> KALIMANTAN SELATAN, INDONESIA</span>
                <h1 id="hero-title">Ada cerita<br>di setiap sudut<br><em>Banua.</em></h1>
                <p>Ikuti aliran sungainya. Temui hangat budayanya.<br>Temukan tempat yang membuat Anda ingin kembali.</p>
                <a href="#<?= $journeys ? 'pilih-pengalaman' : 'discovery-title' ?>" class="hero-explore">Mulai menjelajah <span aria-hidden="true">↗</span></a>
            </div>
            <div class="banua-seal" aria-hidden="true"><span>KENALI LEBIH DEKAT</span><svg viewBox="0 0 64 64" fill="none"><path d="m32 8 7 17 17 7-17 7-7 17-7-17-17-7 17-7Z" stroke="currentColor" stroke-width="1.3"/><path d="m32 18 4 10 10 4-10 4-4 10-4-10-10-4 10-4Z" fill="currentColor"/></svg><span>JELAJAHI BANUA</span></div>
            <?php if ($heroDestination): ?><a class="panorama-location" href="<?= base_url('destinasi/detail/' . (int) $heroDestination['wisata_id']) ?>"><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span><small>SEBUAH SUDUT BANUA</small><?= esc($heroDestination['nama']) ?> <span class="location-region">· <?= esc($heroDestination['daerah']) ?></span></span><span aria-hidden="true">↗</span></a><?php endif; ?>
        </div>
    </section>

    <div class="home-container search-dock-wrap">
        <section class="search-dock" aria-labelledby="discovery-title">
            <div class="dock-heading"><div><span class="eyebrow">PERJALANAN ANDA DIMULAI DI SINI</span><h2 id="discovery-title">Ke mana cerita berikutnya?</h2></div><span class="dock-coordinate" aria-hidden="true">Sungai · Bukit · Pantai</span></div>
            <form action="<?= base_url('destinasi/search') ?>" method="get" class="home-search" role="search"><label for="home-keyword" class="visually-hidden">Nama destinasi atau daerah</label><i class="fas fa-search" aria-hidden="true"></i><input id="home-keyword" type="search" name="keyword" placeholder="Cari destinasi atau daerah, misalnya Loksado" maxlength="100"><button type="submit">Temukan destinasi <span aria-hidden="true">→</span></button></form>
            <div class="home-categories"><span>Ingin suasana apa?</span><?php foreach ($kategoriList as $category): ?><a href="<?= base_url('destinasi') . '?kategori=' . (int) $category['kategori_id'] ?>"><?= esc($category['nama_kategori']) ?></a><?php endforeach; ?></div>
        </section>
    </div>
    <div class="home-container">
        <?php if ($message = session()->getFlashdata('error')): ?><div class="notice notice-error" role="alert"><?= esc($message) ?></div><?php endif; ?>
        <?php if ($journeys): ?>
        <section id="pilih-pengalaman" class="experience-section" aria-labelledby="experience-title">
            <div class="home-section-heading"><div><span class="eyebrow">SATU BANUA, BANYAK CARA MENIKMATINYA</span><h2 id="experience-title">Ikuti rasa ingin <em>tahu Anda.</em></h2></div><p>Untuk pencari tenang, pencinta alam,<br>dan siapa pun yang ingin keluar sebentar.</p></div>
            <div class="experience-grid">
                <?php foreach ($journeys as $index => $journey): $destination = $journey['destination']; ?>
                <a class="experience-tile experience-<?= $index ?>" href="<?= base_url('destinasi/detail/' . (int) $destination['wisata_id']) ?>"><img src="<?= esc($journey['image'], 'attr') ?>" alt="<?= esc($destination['nama'], 'attr') ?>" loading="lazy" decoding="async" width="640" height="800"><div class="experience-shade" aria-hidden="true"></div><span class="experience-number" aria-hidden="true">0<?= $index + 1 ?></span><div class="experience-copy"><span><?= esc($journey['label']) ?></span><h3><?= esc($journey['heading']) ?></h3><p><?= esc($journey['description']) ?></p><div class="experience-destination"><?= esc($destination['nama']) ?> <span aria-hidden="true">↗</span></div></div></a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
        <?php if ($wisataRekomendasi): ?><section class="home-destinations"><div class="home-section-heading"><div><span class="eyebrow">SESUAI MINAT ANDA</span><h2>Perjalanan yang <em>lebih personal.</em></h2></div><a class="home-text-link" href="<?= base_url('profile') ?>">Atur minat wisata <span aria-hidden="true">↗</span></a></div><div class="destination-grid"><?php foreach ($wisataRekomendasi as $item): ?><?= view('partials/destination_card', ['item' => $item]) ?><?php endforeach; ?></div></section><?php endif; ?>

        <section class="home-destinations"><div class="home-section-heading"><div><span class="eyebrow">SIMPAN UNTUK PERJALANAN BERIKUTNYA</span><h2>Tempat baru.<br><em>Cerita yang berbeda.</em></h2></div><div class="section-aside"><p>Pilihan berdasarkan kunjungan terverifikasi<br>dalam 30 hari terakhir.</p><a class="home-text-link" href="<?= base_url('destinasi') ?>">Lihat semua destinasi <span aria-hidden="true">↗</span></a></div></div>
            <?php if ($wisataTrending): ?><div class="destination-grid"><?php foreach ($wisataTrending as $item): ?><?= view('partials/destination_card', ['item' => $item]) ?><?php endforeach; ?></div><?php else: ?><div class="empty-state">Destinasi akan tampil setelah ditambahkan pengelola.</div><?php endif; ?>
        </section>
    </div>

    <section class="banua-culture" aria-labelledby="culture-title">
        <div class="culture-pattern" aria-hidden="true"></div><div class="home-container culture-layout">
            <div class="culture-art"><img src="<?= base_url('images/banua-river.svg') ?>" alt="Ilustrasi jukung di antara aliran sungai dan pegunungan" width="640" height="400" loading="lazy"><span class="art-caption">SUNGAI MENGALIR. CERITA TINGGAL.</span></div>
            <div class="culture-copy"><span class="eyebrow">LEBIH DARI SEBUAH TUJUAN</span><h2>Yang dicari tempatnya.<br>Yang diingat <em>ceritanya.</em></h2><p>Jukung yang menyusuri sungai, pasar terapung yang hidup sejak pagi, dan warna Sasirangan. Di Banua, perjalanan juga tentang mengenal kehidupan yang tumbuh di sekitarnya.</p><a href="https://www.indonesia.travel/id/id/destination/kalimantan/south-kalimantan" target="_blank" rel="noopener noreferrer">Kenali cerita Kalimantan Selatan <span aria-hidden="true">↗</span><span class="visually-hidden">(Indonesia Travel, tab baru)</span></a></div>
        </div>
    </section>

    <div class="home-container">
        <?php if ($regions): ?><section class="region-discovery"><span class="eyebrow">MULAI DARI DAERAH PILIHAN</span><div class="region-heading"><h2>Banua, <em>selangkah lebih dekat.</em></h2><a class="home-text-link" href="<?= base_url('destinasi') ?>">Jelajahi semua daerah <span aria-hidden="true">↗</span></a></div><div class="region-links"><?php foreach ($regions as $region): ?><a href="<?= base_url('destinasi') . '?daerah=' . rawurlencode($region) ?>"><span aria-hidden="true">↗</span><?= esc($region) ?></a><?php endforeach; ?></div></section><?php endif; ?>
        <?php if ($wisataTerdekat): ?><section class="home-destinations"><div class="home-section-heading"><div><span class="eyebrow">DI DAERAH PILIHAN ANDA</span><h2>Jelajahi <em><?= esc($userRegion) ?>.</em></h2></div><a class="home-text-link" href="<?= base_url('destinasi') . '?daerah=' . rawurlencode($userRegion) ?>">Lihat destinasi di sini <span aria-hidden="true">↗</span></a></div><div class="destination-grid"><?php foreach ($wisataTerdekat as $item): ?><?= view('partials/destination_card', ['item' => $item]) ?><?php endforeach; ?></div></section><?php endif; ?>
        <?php if ($berita): ?><section class="home-journal"><div class="home-section-heading"><div><span class="eyebrow">CATATAN DARI BANUA</span><h2>Sedikit inspirasi<br>sebelum <em>berangkat.</em></h2></div><p>Cerita dan kabar untuk menemani<br>rencana perjalanan Anda.</p></div><div class="journal-grid">
            <?php foreach ($berita as $index => $item): $newsLink = safe_url($item['link_berita'] ?? ''); ?><article class="journal-item"><img src="<?= esc(news_image($item['gambar'] ?? null), 'attr') ?>" alt="<?= esc($item['judul'], 'attr') ?>" loading="lazy" decoding="async" width="640" height="400"><div><span class="journal-label">CATATAN <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= esc($item['judul']) ?></h3><p><?= esc(mb_strimwidth(strip_tags($item['konten'] ?? ''), 0, 150, '…')) ?></p><?php if ($newsLink): ?><a href="<?= esc($newsLink, 'attr') ?>" target="_blank" rel="noopener noreferrer">Baca cerita <span aria-hidden="true">↗</span><span class="visually-hidden">(sumber berita, tab baru)</span></a><?php endif; ?></div></article><?php endforeach; ?>
        </div></section><?php endif; ?>
        <aside class="home-trip-note"><span class="trip-note-number" aria-hidden="true">↗</span><div><span class="eyebrow">JANGAN TERBURU-BURU BERANGKAT</span><h2>Kenali tempatnya. Nikmati perjalanannya.</h2><p>Periksa jam buka, akses, fasilitas, dan ketentuan tiket di detail destinasi. Pembayaran pesanan diverifikasi pengelola.</p></div><a href="<?= base_url('destinasi') ?>">Rencanakan kunjungan <span aria-hidden="true">→</span></a></aside>
    </div>
</div>
<?= $this->endSection() ?>
