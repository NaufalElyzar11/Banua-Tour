<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-wrap">
    <span class="eyebrow">PILIH TUJUAN ANDA</span>
    <h1>Jelajahi destinasi</h1>
    <p class="muted">Temukan tempat yang sesuai dengan rencana, lokasi, dan anggaran Anda.</p>
    <form action="<?= base_url('destinasi') ?>" method="get" class="filter-container catalog-filters">
        <div class="field search-field"><label for="keyword">Nama tempat atau daerah</label><input id="keyword" name="keyword" type="search" value="<?= esc($filters['keyword'], 'attr') ?>" placeholder="Contoh: Loksado" maxlength="100"></div>
        <div class="field"><label for="kategori">Kategori</label><select id="kategori" name="kategori"><option value="">Semua kategori</option><?php foreach ($kategoriList as $item): ?><option value="<?= (int) $item['kategori_id'] ?>" <?= $filters['kategori'] === (string) $item['kategori_id'] ? 'selected' : '' ?>><?= esc($item['nama_kategori']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label for="daerah">Daerah</label><select id="daerah" name="daerah"><option value="">Semua daerah</option><?php foreach ($daerahList as $daerah): ?><option value="<?= esc($daerah, 'attr') ?>" <?= $filters['daerah'] === $daerah ? 'selected' : '' ?>><?= esc($daerah) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label for="sort">Urutkan</label><select id="sort" name="sort"><?php foreach (['name-asc' => 'Nama A–Z', 'name-desc' => 'Nama Z–A', 'price-asc' => 'Harga terendah', 'price-desc' => 'Harga tertinggi'] as $value => $label): ?><option value="<?= $value ?>" <?= $filters['sort'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
        <button class="primary-button" type="submit">Cari</button>
    </form>
    <div class="catalog-results" aria-live="polite"><span><?= number_format($pager->getTotal()) ?> destinasi ditemukan<?= $filters['keyword'] !== '' ? ' untuk “' . esc($filters['keyword']) . '”' : '' ?></span><a href="<?= base_url('destinasi') ?>">Reset filter</a></div>
    <?php if ($message = session()->getFlashdata('error')): ?><div class="notice notice-error" role="alert"><?= esc($message) ?></div><?php endif; ?>
    <?php if ($wisata): ?>
        <div class="destination-grid"><?php foreach ($wisata as $item): ?><?= view('partials/destination_card', ['item' => $item]) ?><?php endforeach; ?></div>
        <?= $pager->only(['keyword', 'kategori', 'daerah', 'sort'])->links('default', 'catalog') ?>
    <?php else: ?>
        <div class="empty-state"><h2>Belum ada hasil yang sesuai</h2><p>Coba nama daerah lain atau kurangi filter pencarian.</p><a class="secondary-button" href="<?= base_url('destinasi') ?>">Lihat semua destinasi</a></div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
