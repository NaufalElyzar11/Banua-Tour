<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-wrap">
    <a href="<?= base_url('destinasi/detail/' . (int) $wisata['wisata_id']) ?>">&larr; Kembali ke destinasi</a>
    <h1 class="mt-4 mb-4">Rencanakan kunjungan</h1>
    <?php if ($message = session()->getFlashdata('error')): ?><div class="notice notice-error" role="alert"><?= esc($message) ?></div><?php endif; ?>
    <div class="booking-layout">
        <section><img class="w-100 rounded-4" src="<?= esc(wisata_image($wisata), 'attr') ?>" alt="<?= esc($wisata['nama'], 'attr') ?>" width="800" height="500"><h2 class="mt-4"><?= esc($wisata['nama']) ?></h2><p class="muted"><?= esc($wisata['daerah']) ?> · <?= esc($wisata['nama_kategori'] ?? 'Wisata') ?></p><p><?= nl2br(esc($wisata['deskripsi'])) ?></p>
            <?= view('partials/visitor_info', ['wisata' => $wisata]) ?>
        </section>
        <section class="booking-panel" aria-labelledby="booking-title">
            <h2 id="booking-title">Pesan kunjungan</h2>
            <p class="muted">Pilih tanggal dan jumlah pengunjung. Tiket tersedia setelah pembayaran dikonfirmasi.</p>
            <form action="<?= base_url('booking/store') ?>" method="post" data-prevent-double-submit>
                <?= csrf_field() ?>
                <input type="hidden" name="wisata_id" value="<?= (int) $wisata['wisata_id'] ?>">
                <input type="hidden" name="booking_token" value="<?= esc($bookingToken, 'attr') ?>">
                <div class="field"><label for="tanggal_kunjungan">Tanggal kunjungan</label><input type="date" id="tanggal_kunjungan" name="tanggal_kunjungan" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+1 year')) ?>" required></div>
                <div class="field"><label for="jumlah_orang">Jumlah pengunjung</label><input type="number" id="jumlah_orang" name="jumlah_orang" value="1" min="1" max="100" step="1" required aria-describedby="quantity-help"><small id="quantity-help">Maksimal 100 orang per pesanan.</small></div>
                <div class="booking-summary"><span>Rp <?= number_format($wisata['harga'], 0, ',', '.') ?> / orang</span><strong id="total_harga" aria-live="polite">Rp <?= number_format($wisata['harga'], 0, ',', '.') ?></strong></div>
                <div class="notice"><strong>Pemesanan belum berarti lunas.</strong><br><?= esc($wisata['kontak_pengelola'] ? 'Hubungi pengelola: ' . $wisata['kontak_pengelola'] : 'Petunjuk pembayaran dapat dikonfirmasi kepada pengelola destinasi.') ?></div>
                <button class="primary-button w-100" type="submit" data-label="Simpan pesanan">Simpan pesanan</button>
            </form>
        </section>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const quantity = document.getElementById('jumlah_orang');
    const total = document.getElementById('total_harga');
    const price = <?= json_encode((float) $wisata['harga']) ?>;
    quantity.addEventListener('input', () => {
        const value = Number(quantity.value);
        total.textContent = Number.isInteger(value) && value >= 1 && value <= 100
            ? 'Rp ' + new Intl.NumberFormat('id-ID').format(price * value) : 'Periksa jumlah pengunjung';
    });
});
</script>
<?= $this->endSection() ?>
