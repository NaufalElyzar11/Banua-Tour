<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-wrap">
    <div class="section-heading"><div><span class="eyebrow">RENCANA PERJALANAN ANDA</span><h1><?= !empty($archived) ? 'Arsip pesanan' : 'Pesanan saya' ?></h1><p>Periksa pembayaran, tanggal kunjungan, dan tiket Anda.</p></div><a href="<?= base_url('riwayat') . (empty($archived) ? '?arsip=1' : '') ?>"><?= empty($archived) ? 'Lihat arsip' : 'Kembali ke pesanan' ?> &rarr;</a></div>
    <div id="order-feedback" aria-live="polite"></div>
    <?php foreach (['success', 'error'] as $type): if ($message = session()->getFlashdata($type)): ?><div class="notice notice-<?= $type === 'error' ? 'error' : 'success' ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>"><?= esc($message) ?></div><?php endif; endforeach; ?>
    <?php $groups = ['upcoming' => $upcomingBookings, 'completed' => $completedBookings, 'canceled' => $canceledBookings]; ?>
    <?php if (!array_filter($groups)): ?><div class="empty-state"><h2><?= empty($archived) ? 'Belum ada pesanan' : 'Arsip masih kosong' ?></h2><p><?= empty($archived) ? 'Temukan destinasi pilihan Anda dan mulai rencanakan kunjungan.' : 'Pesanan yang Anda arsipkan akan muncul di sini.' ?></p><a class="primary-button" href="<?= base_url('destinasi') ?>">Jelajahi destinasi</a></div><?php endif; ?>
    <div class="order-list">
    <?php foreach ($groups as $status => $bookings): foreach ($bookings as $booking): $paid = ($booking['status_pembayaran'] ?? '') === 'paid'; ?>
        <article class="order-card" data-order-id="<?= (int) $booking['booking_id'] ?>">
            <div class="order-card-header"><span class="status-pill <?= !$paid && $status === 'upcoming' ? 'pending' : '' ?>"><?= $status === 'canceled' ? 'Dibatalkan' : ($status === 'completed' ? 'Kunjungan selesai' : ($paid ? 'Siap berkunjung' : 'Menunggu pembayaran')) ?></span><span class="muted">Pesanan #<?= (int) $booking['booking_id'] ?></span></div>
            <div class="order-card-body"><img src="<?= esc(wisata_image($booking), 'attr') ?>" alt="" loading="lazy" width="100" height="90"><div><h2><a href="<?= base_url('destinasi/detail/' . (int) $booking['wisata_id']) ?>"><?= esc($booking['nama']) ?></a></h2><p><?= esc(visit_date($booking['tanggal_kunjungan'])) ?> · <?= (int) $booking['jumlah_orang'] ?> orang</p><p><?= $paid ? 'Pembayaran terverifikasi' : 'Pembayaran belum terverifikasi' ?></p></div></div>
            <?php if ($status === 'upcoming' && !$paid): ?><div class="notice">Hubungi pengelola untuk petunjuk pembayaran<?= !empty($booking['kontak_pengelola']) ? ': ' . esc($booking['kontak_pengelola']) : ' melalui informasi di detail destinasi' ?>. Tiket akan tersedia setelah pembayaran diperiksa.</div><?php endif; ?>
            <div class="order-card-footer"><strong>Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></strong><div class="order-actions">
                <?php if ($status !== 'canceled' && $paid): ?><button type="button" class="primary-button" data-ticket="<?= (int) $booking['booking_id'] ?>">Lihat tiket</button><?php endif; ?>
                <?php if ($status === 'completed' && $paid): ?><button type="button" class="secondary-button" data-review="<?= (int) $booking['wisata_id'] ?>">Tulis ulasan</button><?php endif; ?>
                <?php if ($status === 'upcoming' && !$paid): ?><form action="<?= base_url('riwayat/cancel/' . (int) $booking['booking_id']) ?>" method="post" data-confirm="Batalkan pesanan ini?"><?= csrf_field() ?><button type="submit" class="secondary-button">Batalkan</button></form><?php endif; ?>
                <?php if ($status === 'upcoming' && $paid): ?><a class="secondary-button" href="<?= base_url('destinasi/detail/' . (int) $booking['wisata_id']) ?>">Ketentuan pembatalan</a><?php endif; ?>
                <?php if ($status !== 'upcoming'): ?><button type="button" class="secondary-button" data-archive="<?= (int) $booking['booking_id'] ?>" data-action="<?= empty($archived) ? 'delete' : 'restore' ?>"><?= empty($archived) ? 'Arsipkan' : 'Pulihkan' ?></button><?php endif; ?>
            </div></div>
        </article>
    <?php endforeach; endforeach; ?>
    </div>
</div>
<dialog id="ticket-dialog" aria-labelledby="ticket-title">
    <div class="dialog-header"><h2 id="ticket-title">Tiket kunjungan</h2><button type="button" data-close-dialog aria-label="Tutup tiket">&times;</button></div>
    <div id="ticket-content"></div><div id="ticket-qr"></div>
</dialog>
<dialog id="review-dialog" aria-labelledby="review-title">
    <div class="dialog-header"><h2 id="review-title">Bagikan pengalaman Anda</h2><button type="button" data-close-dialog aria-label="Tutup form ulasan">&times;</button></div>
    <form id="review-form"><?= csrf_field() ?><input type="hidden" name="wisata_id" id="review-wisata-id">
        <div class="field"><label for="review-rating">Penilaian</label><select id="review-rating" name="rating" required><option value="5">5 — Sangat baik</option><option value="4">4 — Baik</option><option value="3">3 — Cukup</option><option value="2">2 — Kurang</option><option value="1">1 — Sangat kurang</option></select></div>
        <div class="field"><label for="review-comment">Ulasan</label><textarea id="review-comment" name="komentar" rows="4" minlength="10" maxlength="500" required aria-describedby="review-help"></textarea><small id="review-help">10–500 karakter. Ceritakan pengalaman kunjungan Anda.</small></div>
        <p id="review-feedback" role="alert"></p><button type="submit" class="primary-button">Kirim ulasan</button>
    </form>
</dialog>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ticketDialog = document.getElementById('ticket-dialog');
    const reviewDialog = document.getElementById('review-dialog');
    const feedback = document.getElementById('order-feedback');
    const showFeedback = (message, error = false) => {
        feedback.className = 'notice notice-' + (error ? 'error' : 'success');
        feedback.textContent = message;
        feedback.scrollIntoView({ block: 'nearest' });
    };
    document.querySelectorAll('[data-close-dialog]').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
    document.querySelectorAll('[data-ticket]').forEach(button => button.addEventListener('click', async () => {
        const content = document.getElementById('ticket-content');
        const qr = document.getElementById('ticket-qr');
        content.textContent = 'Memuat tiket…'; qr.replaceChildren(); ticketDialog.showModal();
        try {
            const response = await Banua.request(<?= json_encode(base_url('riwayat/tiket/')) ?> + button.dataset.ticket);
            const result = await response.json();
            if (!response.ok || result.status !== 'success') throw new Error(result.message || 'Tiket belum tersedia.');
            content.replaceChildren();
            const values = [result.data.nama_wisata, result.data.tanggal_kunjungan + ' · ' + result.data.jumlah_orang + ' orang', result.data.total_harga, result.data.sudah_digunakan ? 'Tiket sudah digunakan' : 'Tunjukkan tiket ini kepada pengelola saat berkunjung'];
            values.forEach(value => { const p = document.createElement('p'); p.textContent = value; content.appendChild(p); });
            const code = document.createElement('p'); code.className = 'ticket-code'; code.textContent = result.data.kode_tiket; content.appendChild(code);
            if (!result.data.sudah_digunakan && typeof QRCode !== 'undefined') new QRCode(qr, { text: result.data.kode_tiket, width: 180, height: 180 });
        } catch (error) { content.textContent = error.message; }
    }));
    document.querySelectorAll('[data-archive]').forEach(button => button.addEventListener('click', async () => {
        button.disabled = true;
        try {
            const response = await Banua.request(<?= json_encode(base_url('riwayat/')) ?> + button.dataset.action + '/' + button.dataset.archive, { method: 'POST' });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Tindakan belum berhasil.');
            button.closest('.order-card').remove(); showFeedback(result.message);
        } catch (error) { showFeedback(error.message, true); button.disabled = false; }
    }));
    document.querySelectorAll('[data-review]').forEach(button => button.addEventListener('click', () => {
        document.getElementById('review-form').reset();
        document.getElementById('review-wisata-id').value = button.dataset.review;
        document.getElementById('review-feedback').textContent = '';
        reviewDialog.showModal();
    }));
    document.getElementById('review-form').addEventListener('submit', async event => {
        event.preventDefault(); const form = event.currentTarget; const button = form.querySelector('[type="submit"]'); button.disabled = true;
        try {
            const response = await Banua.request(<?= json_encode(base_url('destinasi/addReview')) ?>, { method: 'POST', body: new FormData(form) });
            const result = await response.json();
            if (!response.ok || result.status !== 'success') throw new Error(result.message || 'Ulasan belum berhasil disimpan.');
            reviewDialog.close(); showFeedback(result.message);
        } catch (error) { document.getElementById('review-feedback').textContent = error.message; }
        finally { button.disabled = false; }
    });
});
</script>
<?= $this->endSection() ?>
