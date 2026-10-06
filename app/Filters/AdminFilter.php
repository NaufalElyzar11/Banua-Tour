<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authResponse = (new AuthFilter())->before($request);
        if ($authResponse) {
            return $authResponse;
        }
        if (session('role') !== 'admin') {
            return service('response')->setStatusCode(403)->setBody('Akses ditolak. Halaman ini hanya untuk admin.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $response->setHeader('Cache-Control', 'no-store');
    }
}
