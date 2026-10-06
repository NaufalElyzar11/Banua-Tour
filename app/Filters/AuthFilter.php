<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session('isLoggedIn') ? (new UserModel())->find(session('user_id')) : null;
        if (!$user) {
            session()->remove(['isLoggedIn', 'user_id', 'role']);
            if ($request->isAJAX()) {
                return service('response')->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sesi berakhir. Silakan masuk kembali.']);
            }
            $path = trim($request->getUri()->getPath(), '/');
            $path = preg_replace('~^index\\.php/~', '', $path);
            if (preg_match('~\\A(?:booking/pembelian/[0-9]+|profile|wishlist|riwayat)\\z~', $path)) {
                session()->set('return_to', $path);
            }
            return redirect()->to(base_url('auth/login'))->with('error', 'Masuk terlebih dahulu untuk melanjutkan.');
        }
        session()->set('role', $user['role']);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('Cache-Control', 'no-store');
    }
}
