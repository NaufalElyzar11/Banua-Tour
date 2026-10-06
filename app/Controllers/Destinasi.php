<?php

namespace App\Controllers;

use App\Models\WisataModel;
use App\Models\KategoriModel;
use App\Models\ReviewModel;
use App\Models\BookingModel;

class Destinasi extends BaseController
{
    public function index()
    {
        return $this->catalog();
    }

    public function search()
    {
        return $this->catalog();
    }

    private function catalog()
    {
        $query = $this->request->getGet();
        $filters = [
            'keyword' => mb_substr(is_string($query['keyword'] ?? null) ? trim($query['keyword']) : '', 0, 100),
            'kategori' => is_scalar($query['kategori'] ?? null) && ctype_digit((string) $query['kategori']) ? (string) $query['kategori'] : '',
            'daerah' => mb_substr(is_string($query['daerah'] ?? null) ? trim($query['daerah']) : '', 0, 100),
            'sort' => in_array($query['sort'] ?? '', ['name-asc', 'name-desc', 'price-asc', 'price-desc'], true) ? $query['sort'] : 'name-asc',
        ];
        $model = new WisataModel();
        $model->select('wisata.*, kategori.nama_kategori')->join('kategori', 'kategori.kategori_id = wisata.kategori_id', 'left');
        if ($filters['keyword'] !== '') {
            $model->groupStart()->like('wisata.nama', $filters['keyword'])->orLike('wisata.daerah', $filters['keyword'])
                ->orLike('kategori.nama_kategori', $filters['keyword'])->groupEnd();
        }
        if ($filters['kategori'] !== '') $model->where('wisata.kategori_id', $filters['kategori']);
        if ($filters['daerah'] !== '') $model->where('wisata.daerah', $filters['daerah']);
        [$field, $direction] = match ($filters['sort']) {
            'name-desc' => ['wisata.nama', 'DESC'], 'price-asc' => ['wisata.harga', 'ASC'],
            'price-desc' => ['wisata.harga', 'DESC'], default => ['wisata.nama', 'ASC'],
        };
        $wisata = $model->orderBy($field, $direction)->orderBy('wisata.wisata_id', 'ASC')->paginate(12);
        return view('destinasi/index', [
            'title' => 'Jelajahi Destinasi', 'wisata' => $wisata, 'filters' => $filters, 'pager' => $model->pager,
            'kategoriList' => (new KategoriModel())->findAll(),
            'daerahList' => array_column((new WisataModel())->select('daerah')->distinct()->orderBy('daerah')->findAll(), 'daerah'),
        ]);
    }

    public function detail($id)
    {
        $wisata = (new WisataModel())->select('wisata.*, kategori.nama_kategori')
            ->join('kategori', 'kategori.kategori_id = wisata.kategori_id', 'left')->find($id);
        if (!$wisata) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Destinasi tidak ditemukan.');
        $reviews = new ReviewModel();
        return view('destinasi/detail', [
            'title' => $wisata['nama'], 'wisata' => $wisata, 'galeri' => wisata_gallery((int) $id),
            'isInWishlist' => session('isLoggedIn') && (new \App\Models\WishlistModel())->isInWishlist(session('user_id'), $id),
            'reviews' => $reviews->getReviewsByWisataId($id),
            'averageRating' => (float) $reviews->getAverageRating($id),
            'trendingScore' => (new BookingModel())->getTotalPengunjung($id),
        ]);
    }

    public function addReview()
    {
        if (!$this->validate([
            'wisata_id' => 'required|is_natural_no_zero|is_not_unique[wisata.wisata_id]',
            'rating' => 'required|integer|greater_than[0]|less_than[6]',
            'komentar' => 'required|min_length[10]|max_length[500]',
        ])) {
            return $this->response->setStatusCode(422)->setJSON(['status' => 'error', 'message' => 'Pilih rating 1–5 dan tulis ulasan 10–500 karakter.']);
        }
        $wisataId = (int) $this->request->getPost('wisata_id');
        $userId = (int) session('user_id');
        $visited = (new BookingModel())->where('user_id', $userId)->where('wisata_id', $wisataId)
            ->where('status', 'completed')->where('status_pembayaran', 'paid')->where('tanggal_kunjungan <=', date('Y-m-d'))->countAllResults();
        if (!$visited) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Ulasan tersedia setelah kunjungan selesai dan pembayaran terverifikasi.']);
        }
        $model = new ReviewModel();
        if ($model->where('user_id', $userId)->where('wisata_id', $wisataId)->countAllResults()) {
            return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'Anda sudah memberikan ulasan untuk destinasi ini.']);
        }
        if (!service('throttler')->check('review-' . $userId, 3, 300)) {
            return $this->response->setStatusCode(429)->setJSON(['status' => 'error', 'message' => 'Tunggu beberapa menit sebelum mengirim ulasan lagi.']);
        }
        try {
        $ok = $model->insert(['user_id' => $userId, 'wisata_id' => $wisataId,
            'rating' => (int) $this->request->getPost('rating'), 'komentar' => $this->request->getPost('komentar')]);
        } catch (\Throwable $e) {
            if ($model->where('user_id', $userId)->where('wisata_id', $wisataId)->countAllResults()) return $this->response->setStatusCode(409)->setJSON(['status' => 'error', 'message' => 'Anda sudah memberikan ulasan untuk destinasi ini.']);
            $ok = false;
        }
        return $this->response->setStatusCode($ok ? 200 : 500)->setJSON([
            'status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Ulasan berhasil disimpan.' : 'Ulasan belum berhasil disimpan.',
        ]);
    }

    public function deleteReview($id)
    {
        $model = new ReviewModel();
        $review = $model->where('user_id', session('user_id'))->find($id);
        if (!$review) return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Ulasan tidak ditemukan.']);
        $ok = $model->delete($id);
        return $this->response->setStatusCode($ok ? 200 : 500)->setJSON([
            'status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Ulasan dihapus.' : 'Ulasan belum berhasil dihapus.',
        ]);
    }
}
