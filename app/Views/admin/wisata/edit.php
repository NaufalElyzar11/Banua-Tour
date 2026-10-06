<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<h1 class="h3 mb-4"><?= isset($wisata) ? 'Edit' : 'Tambah' ?> Destinasi</h1>
<?php if ($errors = session()->getFlashdata('errors')): ?><div class="alert alert-danger" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div id="gallery-feedback" role="status" aria-live="polite"></div>
<div class="card shadow mb-4"><div class="card-body">
<form action="<?= base_url(isset($wisata) ? 'admin/wisata/update/' . (int) $wisata['wisata_id'] : 'admin/wisata/store') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-group mb-3"><label for="nama">Nama destinasi</label><input class="form-control" id="nama" name="nama" value="<?= esc(old('nama', $wisata['nama'] ?? ''), 'attr') ?>" minlength="3" maxlength="100" required></div>
    <div class="form-group mb-3"><label for="daerah">Daerah</label><select class="form-control" id="daerah" name="daerah" required><option value="">Pilih daerah</option><?php foreach (['Banjarbaru','Banjarmasin','Banjar','Barito Kuala','Tapin','Hulu Sungai Selatan','Hulu Sungai Tengah','Hulu Sungai Utara','Tanah Laut','Tanah Bumbu','Kotabaru','Tabalong','Balangan'] as $region): ?><option value="<?= esc($region, 'attr') ?>" <?= old('daerah', $wisata['daerah'] ?? '') === $region ? 'selected' : '' ?>><?= esc($region) ?></option><?php endforeach; ?></select></div>
    <div class="form-group mb-3"><label for="deskripsi">Deskripsi</label><textarea class="form-control" id="deskripsi" name="deskripsi" rows="6" minlength="10" maxlength="10000" required><?= esc(old('deskripsi', $wisata['deskripsi'] ?? '')) ?></textarea></div>
    <div class="form-group mb-3"><label for="harga">Harga per orang (Rp)</label><input type="number" class="form-control" id="harga" name="harga" value="<?= esc(old('harga', $wisata['harga'] ?? ''), 'attr') ?>" min="0" max="99999999.99" step="0.01" required></div>
    <div class="form-group mb-3"><label for="kategori_id">Kategori</label><select class="form-control" id="kategori_id" name="kategori_id" required><option value="">Pilih kategori</option><?php foreach ($kategoriList as $category): ?><option value="<?= (int) $category['kategori_id'] ?>" <?= old('kategori_id', $wisata['kategori_id'] ?? '') == $category['kategori_id'] ? 'selected' : '' ?>><?= esc($category['nama_kategori']) ?></option><?php endforeach; ?></select></div>
    <div class="row"><div class="form-group col-md-6 mb-3"><label for="latitude">Latitude (opsional)</label><input type="number" class="form-control" id="latitude" name="latitude" value="<?= esc(old('latitude', $wisata['latitude'] ?? ''), 'attr') ?>" min="-90" max="90" step="any"></div><div class="form-group col-md-6 mb-3"><label for="longitude">Longitude (opsional)</label><input type="number" class="form-control" id="longitude" name="longitude" value="<?= esc(old('longitude', $wisata['longitude'] ?? ''), 'attr') ?>" min="-180" max="180" step="any"></div></div>
    <div class="form-group mb-3"><label for="link_video">Video YouTube (opsional)</label><input type="url" class="form-control" id="link_video" name="link_video" value="<?= esc(old('link_video', $wisata['link_video'] ?? ''), 'attr') ?>" maxlength="500" placeholder="https://www.youtube.com/watch?v=..."></div>
    <?php if (isset($wisata)): ?><fieldset class="mb-3"><legend class="h6">Galeri saat ini</legend><div class="row"><?php foreach (wisata_gallery((int) $wisata['wisata_id']) as $file): ?><div class="col-md-3 mb-3 gallery-item"><img src="<?= esc(base_url('uploads/wisata/' . $file), 'attr') ?>" class="img-fluid rounded mb-2" alt="<?= esc($wisata['nama'], 'attr') ?>"><button type="button" class="btn btn-sm btn-outline-danger" data-delete-image="<?= esc(base_url('admin/wisata/delete-image/' . (int) $wisata['wisata_id'] . '/' . rawurlencode(basename($file))), 'attr') ?>">Hapus foto</button></div><?php endforeach; ?></div></fieldset><?php endif; ?>
    <div class="form-group mb-3"><label for="gambar">Tambah foto</label><input type="file" class="form-control" id="gambar" name="gambar[]" multiple accept="image/jpeg,image/png"><small>Galeri maksimal 7 foto. JPG/PNG, maksimal 2 MB dan 16 megapiksel per foto.</small></div>
    <?= view('admin/wisata/_visitor_fields', ['wisata' => $wisata ?? []]) ?>
    <button type="submit" class="btn btn-primary">Simpan destinasi</button> <a class="btn btn-secondary" href="<?= base_url('admin/wisata') ?>">Kembali</a>
</form></div></div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-delete-image]').forEach(button => button.addEventListener('click', async () => {
        if (!confirm('Hapus foto ini? Foto yang dihapus tidak dapat dikembalikan.')) return;
        button.disabled = true;
        try {
            const response = await Banua.request(button.dataset.deleteImage, {method:'POST'}); const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Foto belum berhasil dihapus.');
            button.closest('.gallery-item').remove(); document.getElementById('gallery-feedback').textContent = data.message;
        } catch (error) { button.disabled = false; document.getElementById('gallery-feedback').textContent = error.message; }
    }));
});
</script>
<?= $this->endSection() ?>