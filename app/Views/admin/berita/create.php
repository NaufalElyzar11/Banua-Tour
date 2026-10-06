<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<h1 class="h3 mb-4"><?= isset($berita) ? 'Edit' : 'Tambah' ?> Berita</h1>
<?php if ($message = session()->getFlashdata('error')): ?><div class="alert alert-danger" role="alert"><?= esc($message) ?></div><?php endif; ?>
<?php if ($errors = session()->getFlashdata('errors')): ?><div class="alert alert-danger" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="card shadow mb-4"><div class="card-body">
<form action="<?= base_url(isset($berita) ? 'admin/berita/update/' . (int) $berita['berita_id'] : 'admin/berita/store') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-group mb-3"><label for="judul">Judul berita</label><input class="form-control" id="judul" name="judul" value="<?= esc(old('judul', $berita['judul'] ?? ''), 'attr') ?>" minlength="5" maxlength="255" required></div>
    <div class="form-group mb-3"><label for="konten">Konten</label><textarea class="form-control" id="konten" name="konten" rows="8" minlength="10" maxlength="50000" required><?= esc(old('konten', $berita['konten'] ?? '')) ?></textarea></div>
    <div class="form-group mb-3"><label for="wisata_id">Destinasi terkait (opsional)</label><select class="form-control" id="wisata_id" name="wisata_id"><option value="">Tanpa destinasi terkait</option><?php foreach ($wisataList as $wisata): ?><option value="<?= (int) $wisata['wisata_id'] ?>" <?= old('wisata_id', $berita['wisata_id'] ?? '') == $wisata['wisata_id'] ? 'selected' : '' ?>><?= esc($wisata['nama']) ?></option><?php endforeach; ?></select></div>
    <div class="form-group mb-3"><label for="link_berita">URL sumber berita (opsional)</label><input type="url" class="form-control" id="link_berita" name="link_berita" value="<?= esc(old('link_berita', $berita['link_berita'] ?? ''), 'attr') ?>" maxlength="255" placeholder="https://..."></div>
    <?php if (!empty($berita['gambar'])): ?><p>Gambar saat ini</p><img src="<?= esc(news_image($berita['gambar']), 'attr') ?>" alt="<?= esc($berita['judul'], 'attr') ?>" width="200" class="mb-3"><?php endif; ?>
    <div class="form-group mb-3"><label for="gambar">Unggah foto (opsional)</label><input type="file" class="form-control" id="gambar" name="gambar" accept="image/jpeg,image/png"><small>JPG atau PNG, maksimal 2 MB dan 16 megapiksel.</small></div>
    <div class="form-group mb-3"><label for="gambar_url">Atau URL gambar (opsional)</label><input type="url" class="form-control" id="gambar_url" name="gambar_url" maxlength="255" value="<?= esc(old('gambar_url') ?? '', 'attr') ?>" placeholder="https://..."><small>Unggahan dipakai jika keduanya diisi. Kosongkan untuk mempertahankan gambar saat ini.</small></div>
    <div class="form-group mb-3"><label for="status">Status</label><select class="form-control" id="status" name="status"><option value="draft" <?= old('status', $berita['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draf</option><option value="published" <?= old('status', $berita['status'] ?? 'draft') === 'published' ? 'selected' : '' ?>>Terbit</option></select></div>
    <button type="submit" class="btn btn-primary">Simpan berita</button> <a href="<?= base_url('admin/berita') ?>" class="btn btn-secondary">Kembali</a>
</form></div></div>
<?= $this->endSection() ?>