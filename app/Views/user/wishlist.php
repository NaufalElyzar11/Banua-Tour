<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-wrap"><span class="eyebrow">PILIHAN ANDA</span><h1>Destinasi tersimpan</h1><p class="muted">Kumpulkan tempat yang ingin Anda kunjungi, lalu rencanakan perjalanannya.</p>
    <p id="wishlist-feedback" role="status" aria-live="polite"></p>
    <?php if (!$wishlist): ?><div class="empty-state"><h2>Belum ada destinasi tersimpan</h2><p>Gunakan tombol Simpan di halaman detail destinasi.</p><a class="primary-button" href="<?= base_url('destinasi') ?>">Jelajahi destinasi</a></div>
    <?php else: ?><div class="destination-grid"><?php foreach ($wishlist as $item): ?><div class="saved-destination"><?= view('partials/destination_card', ['item' => $item]) ?><button type="button" class="secondary-button w-100 mt-2" data-remove-url="<?= base_url('wishlist/remove/' . (int) $item['wisata_id']) ?>" aria-label="<?= esc('Hapus ' . $item['nama'] . ' dari tersimpan', 'attr') ?>">Hapus dari tersimpan</button></div><?php endforeach; ?></div><?php endif; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const feedback = document.getElementById('wishlist-feedback');
    document.querySelectorAll('[data-remove-url]').forEach(button => button.addEventListener('click', async () => {
        button.disabled = true;
        try {
            const response = await Banua.request(button.dataset.removeUrl, {method:'POST'}); const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Destinasi belum berhasil dihapus.');
            button.closest('.saved-destination').remove(); feedback.textContent = data.message;
            if (!document.querySelector('.saved-destination')) location.reload();
        } catch (error) { feedback.textContent = error.message; button.disabled = false; }
    }));
});
</script>
<?= $this->endSection() ?>