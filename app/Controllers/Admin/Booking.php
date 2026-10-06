<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\BookingService;
use App\Models\BookingModel;

class Booking extends BaseController
{
    public function index()
    {
        $archived = $this->request->getGet('arsip') === '1';
        return view('admin/booking/index', [
            'title' => 'Manajemen Pesanan',
            'archived' => $archived,
            'bookings' => (new BookingModel())->select('bookings.*, users.nama as nama_user, wisata.nama as nama_wisata')
                ->join('users', 'users.user_id = bookings.user_id')->join('wisata', 'wisata.wisata_id = bookings.wisata_id')
                ->where('archived_by_admin', (int) $archived)->orderBy('bookings.created_at', 'DESC')->findAll(),
        ]);
    }

    public function confirmPayment($id)
    {
        $reference = $this->request->getPost('payment_reference');
        if (!is_string($reference)) {
            return redirect()->to(base_url('admin/booking'))->with('error', 'Referensi pembayaran wajib diisi.');
        }
        try {
            (new BookingService())->confirmPayment((int) $id, (int) session('user_id'), $reference);
            return redirect()->to(base_url('admin/booking'))->with('success', 'Pembayaran dikonfirmasi dan tiket diterbitkan.');
        } catch (\DomainException $e) {
            return redirect()->to(base_url('admin/booking'))->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Konfirmasi pembayaran gagal: {exception}', ['exception' => $e]);
            return redirect()->to(base_url('admin/booking'))->with('error', 'Konfirmasi belum tersimpan.');
        }
    }

    public function complete($id)
    {
        try {
            (new BookingService())->complete((int) $id, (int) session('user_id'));
            return redirect()->to(base_url('admin/booking'))->with('success', 'Kunjungan dicatat selesai. Tiket tidak dapat digunakan kembali.');
        } catch (\DomainException $e) {
            return redirect()->to(base_url('admin/booking'))->with('error', 'Hanya pesanan lunas yang tanggal kunjungannya sudah tiba yang dapat diselesaikan.');
        } catch (\Throwable $e) {
            log_message('error', 'Pencatatan kunjungan gagal: {exception}', ['exception' => $e]);
            return redirect()->to(base_url('admin/booking'))->with('error', 'Kunjungan belum berhasil dicatat.');
        }
    }

    public function delete($id)
    {
        $model = new BookingModel();
        $booking = $model->find($id);
        if (!$booking || !in_array($booking['status'], ['completed', 'canceled'], true)) {
            return redirect()->to(base_url('admin/booking'))->with('error', 'Pesanan aktif tidak dapat diarsipkan.');
        }
        $model->update($id, ['archived_by_admin' => 1]);
        return redirect()->to(base_url('admin/booking'))->with('success', 'Pesanan diarsipkan. Catatan transaksi tetap tersimpan.');
    }

    public function restore($id)
    {
        $model = new BookingModel();
        if (!$model->find($id)) return redirect()->to(base_url('admin/booking'))->with('error', 'Pesanan tidak ditemukan.');
        $model->update($id, ['archived_by_admin' => 0]);
        return redirect()->to(base_url('admin/booking'))->with('success', 'Pesanan dipulihkan.');
    }
}
