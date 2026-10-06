<?php

namespace App\Libraries;

use App\Models\BookingModel;
use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;

class BookingService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= db_connect();
    }

    public function reserve(int $userId, array $wisata, string $date, string $quantity, string $token): int
    {
        $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Makassar'));
        $visit = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone('Asia/Makassar'));
        if (!$visit || $visit->format('Y-m-d') !== $date || $visit < $today || $visit > $today->modify('+1 year')) {
            throw new DomainException('Pilih tanggal mulai hari ini hingga satu tahun ke depan.');
        }
        if (!preg_match('/\A[1-9][0-9]*\z/', $quantity) || (int) $quantity > 100) {
            throw new DomainException('Jumlah pengunjung harus berupa bilangan bulat antara 1 dan 100.');
        }
        if (!preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            throw new DomainException('Form pemesanan tidak valid. Buka kembali halaman pemesanan.');
        }
        $model = new BookingModel($this->db);
        $existing = $model->where('user_id', $userId)->where('request_token', $token)->first();
        if ($existing) {
            if ((int) $existing['wisata_id'] !== (int) $wisata['wisata_id'] || $existing['tanggal_kunjungan'] !== $date || (int) $existing['jumlah_orang'] !== (int) $quantity) {
                throw new DomainException('Form ini sudah digunakan untuk pesanan lain. Buka form pemesanan baru.');
            }
            return (int) $existing['booking_id'];
        }
        $unitCents = (int) round((float) $wisata['harga'] * 100);
        $totalCents = $unitCents * (int) $quantity;
        if ($unitCents < 0 || $totalCents > 9999999999) {
            throw new DomainException('Total harga di luar batas pemesanan. Hubungi pengelola.');
        }
        $id = $model->insert([
            'user_id' => $userId, 'wisata_id' => $wisata['wisata_id'], 'tanggal_kunjungan' => $date,
            'jumlah_orang' => (int) $quantity, 'total_harga' => number_format($totalCents / 100, 2, '.', ''),
            'status' => 'upcoming', 'status_pembayaran' => 'unpaid', 'request_token' => $token,
        ], true);
        if (!$id) {
            throw new \RuntimeException('Pemesanan belum tersimpan.');
        }
        return (int) $id;
    }

    public function confirmPayment(int $id, int $adminId, string $reference): void
    {
        $reference = trim($reference);
        if (mb_strlen($reference) < 3 || mb_strlen($reference) > 100) {
            throw new DomainException('Isi referensi pembayaran yang sudah diperiksa (3–100 karakter).');
        }
        $this->transition($id, $adminId, 'payment_confirmed', [
            'status' => 'upcoming', 'status_pembayaran' => 'unpaid',
            'tanggal_kunjungan >=' => date('Y-m-d'),
        ], [
            'status_pembayaran' => 'paid', 'payment_reference' => $reference,
            'paid_at' => date('Y-m-d H:i:s'), 'confirmed_by' => $adminId,
            'kode_tiket' => 'BT-' . strtoupper(bin2hex(random_bytes(16))),
        ], $reference);
    }

    public function complete(int $id, int $adminId): void
    {
        $this->transition($id, $adminId, 'visit_completed', [
            'status' => 'upcoming', 'status_pembayaran' => 'paid',
            'tanggal_kunjungan <=' => date('Y-m-d'),
        ], ['status' => 'completed']);
    }

    public function cancel(int $id, int $userId): void
    {
        $this->transition($id, $userId, 'canceled', [
            'user_id' => $userId, 'status' => 'upcoming', 'status_pembayaran' => 'unpaid',
        ], ['status' => 'canceled']);
    }

    private function transition(int $id, int $actor, string $event, array $conditions, array $changes, ?string $reference = null): void
    {
        $this->db->transBegin();
        try {
            $updated = $this->db->table('bookings')->where('booking_id', $id)->where($conditions)->update($changes);
            if (!$updated || $this->db->affectedRows() !== 1) {
                throw new DomainException('Status pesanan tidak memungkinkan tindakan ini, atau pesanan sudah diperbarui.');
            }
            if (!$this->db->table('booking_events')->insert([
                'booking_id' => $id, 'actor_id' => $actor, 'event' => $event,
                'reference' => $reference, 'created_at' => date('Y-m-d H:i:s'),
            ]) || !$this->db->transStatus()) {
                throw new \RuntimeException('Pencatatan transaksi gagal.');
            }
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
