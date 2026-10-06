<article class="destination-card">
    <a href="<?= base_url('destinasi/detail/' . (int) $item['wisata_id']) ?>">
        <img src="<?= esc(wisata_image($item), 'attr') ?>" alt="<?= esc($item['nama'], 'attr') ?>" loading="lazy" decoding="async" width="640" height="480">
        <div class="destination-card-content"><span class="card-category"><?= esc($item['nama_kategori'] ?? 'Wisata') ?></span><h3><?= esc($item['nama']) ?></h3><p class="card-region"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> <?= esc($item['daerah']) ?></p><p class="card-price">Rp <?= number_format($item['harga'] ?? 0, 0, ',', '.') ?> <small>/ orang</small></p></div>
    </a>
</article>
