<?php

namespace App\Controllers;

use App\Libraries\BookingService;
use App\Models\BookingModel;

class Riwayat extends BaseController
{
    public function index()
    {
        $model = new BookingModel();
        $userId = session('user_id');
        $archived = $this->request->getGet('arsip') === '1';
        return view('user/riwayat', [
            'title' => 'Pesanan Saya',
            'archived' => $archived,
            'upcomingBookings' => $model->getUserBookings($userId, 'upcoming', $archived),
            'completedBookings' => $model->getUserBookings($userId, 'completed', $archived),
            'canceledBookings' => $model->getUserBookings($userId, 'canceled', $archived),
        ]);
    }

    public function cancel($bookingId)
    {
        try {
            (new BookingService())->cancel((int) $bookingId, (int) session('user_id'));
            return redirect()->to(base_url('riwayat'))->with('success', 'Pesanan dibatalkan.');
        } catch (\DomainException $e) {
            return redirect()->to(base_url('riwayat'))->with('error', 'Pesanan tidak dapat dibatalkan. Untuk pesanan yang sudah dibayar, hubungi pengelola.');
        } catch (\Throwable $e) {
            log_message('error', 'Pembatalan gagal: {exception}', ['exception' => $e]);
            return redirect()->to(base_url('riwayat'))->with('error', 'Pembatalan belum berhasil. Coba lagi.');
        }
    }

    public function delete($bookingId)
    {
        $model = new BookingModel();
        $booking = $model->where('user_id', session('user_id'))->find($bookingId);
        if (!$booking) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Pesanan tidak ditemukan.']);
        }
        if (!in_array($booking['status'], ['completed', 'canceled'], true)) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'message' => 'Pesanan aktif tidak dapat diarsipkan.']);
        }
        $ok = $model->update($bookingId, ['hidden_by_user' => 1]);
        return $this->response->setStatusCode($ok ? 200 : 500)->setJSON([
            'success' => $ok, 'message' => $ok ? 'Pesanan diarsipkan dari daftar Anda.' : 'Pesanan belum berhasil diarsipkan.',
        ]);
    }

    public function showTicket($bookingId)
    {
        $booking = (new BookingModel())->select('bookings.*, wisata.nama as nama_wisata')
            ->join('wisata', 'wisata.wisata_id = bookings.wisata_id')
            ->where('bookings.user_id', session('user_id'))->find($bookingId);
        if (!$booking) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Pesanan tidak ditemukan.']);
        }
        if (($booking['status_pembayaran'] ?? '') !== 'paid' || !in_array($booking['status'], ['upcoming', 'completed'], true) || empty($booking['kode_tiket'])) {
            return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'Tiket tersedia setelah pembayaran dikonfirmasi pengelola.']);
        }
        return $this->response->setHeader('Cache-Control', 'no-store')->setJSON(['status' => 'success', 'data' => [
            'nama_wisata' => $booking['nama_wisata'], 'jumlah_orang' => $booking['jumlah_orang'],
            'total_harga' => 'Rp ' . number_format($booking['total_harga'], 0, ',', '.'),
            'kode_tiket' => $booking['kode_tiket'], 'tanggal_kunjungan' => $booking['tanggal_kunjungan'],
            'sudah_digunakan' => $booking['status'] === 'completed',
        ]]);
    }

    public function restore($bookingId)
    {
        $model = new BookingModel();
        $booking = $model->where('user_id', session('user_id'))->find($bookingId);
        if (!$booking) return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Pesanan tidak ditemukan.']);
        $ok = $model->update($bookingId, ['hidden_by_user' => 0]);
        return $this->response->setStatusCode($ok ? 200 : 500)->setJSON(['success' => $ok, 'message' => $ok ? 'Pesanan dipulihkan.' : 'Pesanan belum berhasil dipulihkan.']);
    }
}
