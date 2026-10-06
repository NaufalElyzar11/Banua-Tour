<?php

namespace App\Controllers;

use App\Libraries\BookingService;
use App\Models\BookingModel;
use App\Models\WisataModel;

class Booking extends BaseController
{
    public function index()
    {
        return redirect()->to(base_url('riwayat'));
    }

    public function create($wisataId)
    {
        $wisata = (new WisataModel())->select('wisata.*, kategori.nama_kategori')
            ->join('kategori', 'kategori.kategori_id = wisata.kategori_id', 'left')->find($wisataId);
        if (!$wisata) {
            return redirect()->to(base_url('destinasi'))->with('error', 'Destinasi tidak ditemukan.');
        }
        $token = bin2hex(random_bytes(32));
        $tokens = array_filter(session()->get('booking_tokens') ?? [], static fn ($item) => $item['expires'] > time());
        $tokens = array_slice($tokens, -9, null, true);
        $tokens[$token] = ['wisata_id' => (int) $wisataId, 'expires' => time() + 7200];
        session()->set('booking_tokens', $tokens);
        return view('booking/create', [
            'title' => 'Pesan Kunjungan', 'wisata' => $wisata, 'bookingToken' => $token,
        ]);
    }

    public function store()
    {
        if (!$this->validate([
            'wisata_id' => 'required|is_natural_no_zero',
            'tanggal_kunjungan' => 'required|valid_date[Y-m-d]',
            'jumlah_orang' => 'required|is_natural_no_zero|less_than_equal_to[100]',
            'booking_token' => 'required|exact_length[64]|alpha_numeric',
        ])) {
            return redirect()->back()->with('error', 'Periksa tanggal dan jumlah pengunjung (1–100 orang).');
        }
        $token = $this->request->getPost('booking_token');
        $wisataId = (int) $this->request->getPost('wisata_id');
        $tokens = session()->get('booking_tokens') ?? [];
        if (!isset($tokens[$token]) || $tokens[$token]['expires'] < time() || $tokens[$token]['wisata_id'] !== $wisataId) {
            return redirect()->to(base_url('booking/pembelian/' . $wisataId))->with('error', 'Form sudah kedaluwarsa. Silakan isi kembali.');
        }
        $wisata = (new WisataModel())->find($wisataId);
        if (!$wisata) {
            return redirect()->to(base_url('destinasi'))->with('error', 'Destinasi tidak ditemukan.');
        }
        try {
            (new BookingService())->reserve(
                (int) session('user_id'), $wisata, $this->request->getPost('tanggal_kunjungan'),
                (string) $this->request->getPost('jumlah_orang'), $token
            );
            return redirect()->to(base_url('riwayat'))->with('success', 'Pesanan tersimpan. Pembayaran belum dikonfirmasi; lihat petunjuk pengelola di detail destinasi.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Pemesanan gagal: {exception}', ['exception' => $e]);
            return redirect()->back()->with('error', 'Pesanan belum berhasil disimpan. Silakan coba lagi.');
        }
    }
}
