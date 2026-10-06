<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $embed = youtube_embed($wisata['link_video'] ?? ''); $imageUrls = array_map(static fn ($file) => base_url('uploads/wisata/' . $file), $galeri); if (!$imageUrls) $imageUrls = [wisata_image($wisata)]; ?>
<div class="page-wrap">
    <nav aria-label="Jejak navigasi" class="mb-4"><a href="<?= base_url('destinasi') ?>">Destinasi</a> <span aria-hidden="true">/</span> <?= esc($wisata['nama']) ?></nav>
    <div id="detail-feedback" aria-live="polite"></div>
    <div class="detail-layout">
        <section aria-label="Foto destinasi">
            <img class="gallery-main" src="<?= esc($imageUrls[0], 'attr') ?>" alt="<?= esc($wisata['nama'], 'attr') ?>" width="800" height="600">
            <?php if (count($imageUrls) > 1): ?><div class="gallery-thumbnails"><?php foreach ($imageUrls as $index => $url): ?><button type="button" data-gallery="<?= $index ?>" aria-label="Lihat foto <?= $index + 1 ?> <?= esc($wisata['nama'], 'attr') ?>"><img src="<?= esc($url, 'attr') ?>" alt="" loading="lazy" width="96" height="72"></button><?php endforeach; ?></div><?php endif; ?>
        </section>
        <section class="detail-copy">
            <div class="detail-intro"><span class="eyebrow"><?= esc($wisata['nama_kategori'] ?? 'WISATA BANUA') ?></span>
            <h1><?= esc($wisata['nama']) ?></h1><p class="muted"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> <?= esc($wisata['daerah']) ?></p>
            <p class="detail-rating"><?= $reviews ? number_format($averageRating, 1, ',', '.') . ' / 5 · ' . count($reviews) . ' ulasan' : 'Belum ada ulasan' ?></p>
            <div class="detail-price"><strong>Rp <?= number_format($wisata['harga'], 0, ',', '.') ?></strong><span class="muted"> / orang</span></div>
            <div class="detail-actions"><a class="primary-button" href="<?= base_url('booking/pembelian/' . (int) $wisata['wisata_id']) ?>">Pesan kunjungan</a>
                <?php if (session('isLoggedIn')): ?><button type="button" class="secondary-button" id="wishlist-button" aria-pressed="<?= $isInWishlist ? 'true' : 'false' ?>"><?= $isInWishlist ? 'Tersimpan di favorit' : 'Simpan ke favorit' ?></button><?php else: ?><a class="secondary-button" href="<?= base_url('wishlist') ?>">Simpan ke favorit</a><?php endif; ?>
            </div>
            <p class="muted small">Pemesanan belum berarti lunas. Pembayaran diperiksa oleh pengelola sebelum tiket diterbitkan.</p>
            </div><div class="detail-description"><h2 class="mt-4">Tentang destinasi</h2><p><?= nl2br(esc($wisata['deskripsi'])) ?></p>
            <?= view('partials/visitor_info', ['wisata' => $wisata]) ?></div>
        </section>
    </div>
    <div class="destination-media mt-4">
            <?php if ($embed): ?><iframe class="destination-frame video-frame" src="<?= esc($embed, 'attr') ?>" title="Video <?= esc($wisata['nama'], 'attr') ?>" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe><?php endif; ?>
            <?php $lat = $wisata['latitude'] ?? null; $lng = $wisata['longitude'] ?? null; if (is_numeric($lat) && is_numeric($lng) && abs((float) $lat) <= 90 && abs((float) $lng) <= 180): ?>
                <section class="mt-4"><h2>Lokasi destinasi</h2><iframe class="destination-frame" src="https://www.google.com/maps?q=<?= (float) $lat ?>,<?= (float) $lng ?>&amp;output=embed" title="Peta lokasi <?= esc($wisata['nama'], 'attr') ?>" loading="lazy" referrerpolicy="no-referrer" allowfullscreen></iframe><a href="https://www.google.com/maps/search/?api=1&amp;query=<?= (float) $lat ?>,<?= (float) $lng ?>" target="_blank" rel="noopener noreferrer">Buka petunjuk arah <span class="visually-hidden">(tab baru)</span>&rarr;</a></section>
            <?php endif; ?>
    </div>
    <section class="mt-5" aria-labelledby="reviews-title"><div class="section-heading"><div><span class="eyebrow">PENGALAMAN PENGUNJUNG</span><h2 id="reviews-title">Ulasan destinasi</h2><p>Ulasan baru dapat diberikan melalui pesanan setelah kunjungan selesai.</p></div></div>
        <?php if (!$reviews): ?><div class="empty-state">Belum ada ulasan. Pengalaman Anda bisa membantu pengunjung berikutnya.</div><?php endif; ?>
        <div class="review-list">
        <?php foreach ($reviews as $review): ?><article class="review-card"><div class="review-card-header"><div><strong><?= esc($review['nama_user']) ?></strong><p class="muted small"><?= esc(visit_date($review['tanggal_review'])) ?></p></div><span aria-label="<?= (int) $review['rating'] ?> dari 5 bintang"><?= (int) $review['rating'] ?> / 5</span><?php if ((int) session('user_id') === (int) $review['user_id']): ?><button type="button" class="secondary-button" data-delete-review="<?= (int) $review['review_id'] ?>">Hapus ulasan</button><?php endif; ?></div><p><?= nl2br(esc($review['komentar'])) ?></p></article><?php endforeach; ?>
        </div>
    </section>
</div>
<dialog id="gallery-dialog" aria-labelledby="gallery-title"><div class="dialog-header"><h2 id="gallery-title">Foto <?= esc($wisata['nama']) ?></h2><button type="button" data-close-dialog aria-label="Tutup galeri">&times;</button></div><img id="gallery-image" alt="<?= esc($wisata['nama'], 'attr') ?>"><div class="gallery-controls"><button type="button" class="secondary-button" id="gallery-prev" aria-label="Foto sebelumnya">&larr;</button><span id="gallery-count" aria-live="polite"></span><button type="button" class="secondary-button" id="gallery-next" aria-label="Foto berikutnya">&rarr;</button></div></dialog>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const feedback = document.getElementById('detail-feedback');
    const message = (text, error = false) => { feedback.className = 'notice notice-' + (error ? 'error' : 'success'); feedback.textContent = text; };
    const wishlistButton = document.getElementById('wishlist-button');
    wishlistButton?.addEventListener('click', async () => {
        const added = wishlistButton.getAttribute('aria-pressed') === 'true'; wishlistButton.disabled = true;
        try {
            const response = await Banua.request(<?= json_encode(base_url('wishlist/')) ?> + (added ? 'remove/' : 'add/') + <?= (int) $wisata['wisata_id'] ?>, { method: 'POST' });
            const result = await response.json(); if (!response.ok || !result.success) throw new Error(result.message || 'Favorit belum berhasil diperbarui.');
            wishlistButton.setAttribute('aria-pressed', String(!added)); wishlistButton.textContent = added ? 'Simpan ke favorit' : 'Tersimpan di favorit'; message(result.message);
        } catch (error) { message(error.message, true); } finally { wishlistButton.disabled = false; }
    });
    document.querySelectorAll('[data-delete-review]').forEach(button => button.addEventListener('click', async () => {
        if (!confirm('Hapus ulasan Anda?')) return;
        button.disabled = true;
        try {
            const response = await Banua.request(<?= json_encode(base_url('destinasi/review/delete/')) ?> + button.dataset.deleteReview, { method: 'POST' });
            const result = await response.json(); if (!response.ok || result.status !== 'success') throw new Error(result.message || 'Ulasan belum berhasil dihapus.');
            location.reload();
        } catch (error) { message(error.message, true); button.disabled = false; }
    }));
    const images = <?= json_encode($imageUrls, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const dialog = document.getElementById('gallery-dialog'); let index = 0;
    const showImage = () => { document.getElementById('gallery-image').src = images[index]; document.getElementById('gallery-count').textContent = (index + 1) + ' / ' + images.length; };
    const step = direction => { index = (index + direction + images.length) % images.length; showImage(); };
    document.querySelectorAll('[data-gallery]').forEach(button => button.addEventListener('click', () => { index = Number(button.dataset.gallery); showImage(); dialog.showModal(); }));
    document.getElementById('gallery-prev').addEventListener('click', () => step(-1));
    document.getElementById('gallery-next').addEventListener('click', () => step(1));
    dialog.addEventListener('keydown', event => { if (event.key === 'ArrowLeft') step(-1); if (event.key === 'ArrowRight') step(1); });
});
</script>
<?= $this->endSection() ?>
