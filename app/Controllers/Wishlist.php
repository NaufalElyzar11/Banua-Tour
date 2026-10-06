<?php
namespace App\Controllers;

use App\Models\WishlistModel;
use App\Models\WisataModel;

class Wishlist extends BaseController
{
    public function index()
    {
        return view('user/wishlist', ['title' => 'Destinasi tersimpan', 'wishlist' => (new WishlistModel())->getUserWishlist(session('user_id'))]);
    }
    public function add($id)
    {
        if (!(new WisataModel())->find($id)) return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Destinasi tidak ditemukan.']);
        $model = new WishlistModel();
        try {
            $ok = $model->isInWishlist(session('user_id'), $id) || $model->addToWishlist(session('user_id'), $id);
        } catch (\Throwable $e) {
            $ok = $model->isInWishlist(session('user_id'), $id);
        }
        return $this->response->setStatusCode($ok ? 200 : 500)->setJSON(['success' => $ok, 'message' => $ok ? 'Destinasi disimpan.' : 'Destinasi belum berhasil disimpan.']);
    }
    public function remove($id)
    {
        $ok = (new WishlistModel())->removeFromWishlist(session('user_id'), $id);
        return $this->response->setStatusCode($ok ? 200 : 500)->setJSON(['success' => $ok, 'message' => $ok ? 'Destinasi dihapus dari daftar tersimpan.' : 'Destinasi belum berhasil dihapus.']);
    }
}
