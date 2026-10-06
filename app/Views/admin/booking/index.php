<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<h1 class="h3 mb-4"><?= !empty($archived) ? 'Arsip Pesanan' : 'Manajemen Pesanan' ?></h1>
<p><a href="<?= base_url('admin/booking') . (empty($archived) ? '?arsip=1' : '') ?>"><?= empty($archived) ? 'Lihat arsip pesanan' : 'Kembali ke pesanan aktif' ?></a></p>
<?php foreach (['success','error'] as $type): if ($message = session()->getFlashdata($type)): ?><div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?>" role="alert"><?= esc($message) ?></div><?php endif; endforeach; ?>
<div class="alert alert-info">Konfirmasikan pembayaran hanya setelah memeriksa pembayaran yang diterima pengelola. Referensi dan admin yang mengonfirmasi dicatat. Penyelesaian kunjungan menandai tiket sebagai sudah digunakan.</div>
<div class="card shadow mb-4"><div class="card-body"><div class="table-responsive">
<table class="table table-bordered"><thead><tr><th>Pesanan</th><th>Pengunjung &amp; destinasi</th><th>Kunjungan</th><th>Total</th><th>Status</th><th>Tindakan</th></tr></thead><tbody>
<?php foreach ($bookings as $booking): $paid = $booking['status_pembayaran'] === 'paid'; ?>
<tr><td>#<?= (int) $booking['booking_id'] ?></td><td><?= esc($booking['nama_user']) ?><br><strong><?= esc($booking['nama_wisata']) ?></strong></td><td><?= esc(visit_date($booking['tanggal_kunjungan'])) ?><br><?= (int) $booking['jumlah_orang'] ?> orang</td><td>Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></td><td><?= ['upcoming' => 'Akan datang', 'completed' => 'Kunjungan selesai', 'canceled' => 'Dibatalkan'][$booking['status']] ?? esc($booking['status']) ?><br><span class="badge badge-<?= $paid ? 'success' : 'warning' ?>"><?= $paid ? 'Lunas terverifikasi' : 'Belum terverifikasi' ?></span><?php if ($paid): ?><br><small>Ref: <?= esc($booking['payment_reference'] ?? 'Gratis') ?><br><?= esc($booking['paid_at'] ?? '') ?></small><?php endif; ?></td>
<td>
<?php if ($booking['status'] === 'upcoming' && !$paid && $booking['tanggal_kunjungan'] >= date('Y-m-d')): ?><form action="<?= base_url('admin/booking/confirm-payment/' . (int) $booking['booking_id']) ?>" method="post" data-confirm="Pembayaran sudah diperiksa dan diterima pengelola?"><?= csrf_field() ?><label class="small" for="reference-<?= (int) $booking['booking_id'] ?>">Referensi pembayaran</label><input class="form-control form-control-sm mb-2" id="reference-<?= (int) $booking['booking_id'] ?>" name="payment_reference" required minlength="3" maxlength="100"><button class="btn btn-sm btn-success" type="submit">Konfirmasi pembayaran</button></form><?php endif; ?>
<?php if ($booking['status'] === 'upcoming' && $paid && $booking['tanggal_kunjungan'] <= date('Y-m-d')): ?><form action="<?= base_url('admin/booking/complete/' . (int) $booking['booking_id']) ?>" method="post" data-confirm="Pengunjung sudah datang dan tiket sudah diperiksa?"><?= csrf_field() ?><p class="small text-break"><?= esc($booking['kode_tiket']) ?></p><button type="submit" class="btn btn-sm btn-primary">Catat kunjungan selesai</button></form><?php endif; ?>
<?php if (in_array($booking['status'], ['completed','canceled'], true)): ?><form action="<?= base_url('admin/booking/' . (empty($archived) ? 'delete/' : 'restore/') . (int) $booking['booking_id']) ?>" method="post" class="mt-2"><?= csrf_field() ?><button class="btn btn-sm btn-outline-secondary" type="submit"><?= empty($archived) ? 'Arsipkan' : 'Pulihkan' ?></button></form><?php endif; ?>
</td></tr>
<?php endforeach; ?>
<?php if (!$bookings): ?><tr><td colspan="6" class="text-center text-muted">Belum ada pesanan dalam daftar ini.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?= $this->endSection() ?>
