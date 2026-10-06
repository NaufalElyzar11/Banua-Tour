<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class LoginThrottleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $register = str_contains($request->getUri()->getPath(), 'doRegister');
        $scope = $register ? 'register' : 'login';
        $throttler = service('throttler');
        $ipAllowed = $throttler->check($scope . '-ip-' . hash('sha256', $request->getIPAddress()), $register ? 5 : 30, 900);
        $identifier = $request->getPost('email');
        $accountAllowed = is_string($identifier) && $throttler->check(
            $scope . '-account-' . hash('sha256', strtolower(trim($identifier))), $register ? 3 : 10, 900
        );
        if (!$ipAllowed || !$accountAllowed) {
            $message = 'Terlalu banyak percobaan. Tunggu beberapa menit sebelum mencoba kembali.';
            if ($request->isAJAX()) {
                return service('response')->setStatusCode(429)->setJSON(['success' => false, 'message' => $message]);
            }
            return service('response')->setStatusCode(429)->setBody(view('auth/' . ($register ? 'register' : 'login'), ['error' => $message]));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
